<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsAppSupportController extends Controller
{
    public function __construct(protected WhatsAppService $whatsapp) {}

    /*
    |--------------------------------------------------------------------------
    | Index – list all conversations
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = WhatsAppConversation::with(['customer', 'assignedTo'])
            ->orderByRaw("FIELD(status,'open','in_progress','closed')")
            ->orderByDesc('last_message_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('customer_name', 'like', "%$s%")
                  ->orWhere('customer_phone', 'like', "%$s%");
            });
        }

        $conversations = $query->paginate(25)->withQueryString();

        $stats = [
            'open'        => WhatsAppConversation::where('status', 'open')->count(),
            'in_progress' => WhatsAppConversation::where('status', 'in_progress')->count(),
            'closed'      => WhatsAppConversation::where('status', 'closed')->count(),
            'unread'      => WhatsAppConversation::where('unread_count', '>', 0)->sum('unread_count'),
        ];

        return view('admin.whatsapp.index', compact('conversations', 'stats'));
    }

    /*
    |--------------------------------------------------------------------------
    | Show – single conversation chat view
    |--------------------------------------------------------------------------
    */
    public function show(WhatsAppConversation $conversation)
    {
        $conversation->markAllRead();

        $messages = $conversation->messages()
            ->with('sentBy')
            ->orderBy('created_at')
            ->get();

        $staffList = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('admin.whatsapp.show', compact('conversation', 'messages', 'staffList'));
    }

    /*
    |--------------------------------------------------------------------------
    | Send a reply
    |--------------------------------------------------------------------------
    */
    public function reply(Request $request, WhatsAppConversation $conversation)
    {
        $request->validate([
            'message' => 'required|string|max:1600',
        ]);

        $sent = $this->whatsapp->sendSupport($conversation->customer_phone, $request->message);

        $sid = $sent['sid'] ?? null;

        $msg = $conversation->addOutboundMessage($request->message, Auth::id(), $sid);

        if ($sent === false) {
            return back()->with('warning', 'Message saved but could not be delivered (check WhatsApp configuration).');
        }

        if ($conversation->status === 'open') {
            $conversation->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Message sent!');
    }

    /*
    |--------------------------------------------------------------------------
    | Update status / assignment / notes
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, WhatsAppConversation $conversation)
    {
        $data = $request->validate([
            'status'      => 'sometimes|in:open,in_progress,closed',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'notes'       => 'sometimes|nullable|string|max:2000',
        ]);

        $conversation->update($data);

        return back()->with('success', 'Conversation updated.');
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy conversation
    |--------------------------------------------------------------------------
    */
    public function destroy(WhatsAppConversation $conversation)
    {
        $conversation->delete();
        return redirect()->route('whatsapp.support.index')->with('success', 'Conversation deleted.');
    }

    /*
    |--------------------------------------------------------------------------
    | Quick-send message to any phone (new conversation)
    |--------------------------------------------------------------------------
    */
    public function compose(Request $request)
    {
        $request->validate([
            'phone'   => 'required|string|max:30',
            'message' => 'required|string|max:1600',
            'name'    => 'nullable|string|max:150',
        ]);

        $phone = preg_replace('/[^+0-9]/', '', $request->phone);

        $conversation = WhatsAppConversation::firstOrCreate(
            ['customer_phone' => $phone],
            ['customer_name' => $request->name ?? $phone, 'status' => 'open']
        );

        $sent = $this->whatsapp->sendSupport($phone, $request->message);
        $sid  = $sent['sid'] ?? null;

        $conversation->addOutboundMessage($request->message, Auth::id(), $sid);

        if ($conversation->status === 'open') {
            $conversation->update(['status' => 'in_progress']);
        }

        return redirect()->route('whatsapp.support.show', $conversation)
            ->with('success', 'Message sent to ' . $phone);
    }
}
