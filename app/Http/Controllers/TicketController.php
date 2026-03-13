<?php

namespace App\Http\Controllers;

use App\Mail\TicketRepliedCustomer;
use App\Models\Setting;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with('customer')
            ->orderByRaw("FIELD(status, 'open', 'in_progress', 'resolved', 'closed')")
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy('last_reply_at', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('ticket_number', 'like', "%{$s}%")
                  ->orWhere('subject', 'like', "%{$s}%")
                  ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
            });
        }

        $tickets = $query->paginate(20)->withQueryString();

        $stats = [
            'open'        => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved'    => SupportTicket::where('status', 'resolved')->count(),
            'total'       => SupportTicket::count(),
        ];

        return Inertia::render('Tickets/Index', [
            'tickets'  => $tickets,
            'stats'    => $stats,
            'filters'  => $request->only(['status', 'priority', 'category', 'search']),
        ]);
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['customer', 'replies', 'assignedTo']);

        // Mark as in_progress when admin first views it
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message'     => 'required|string|max:5000',
            'is_internal' => 'boolean',
            'status'      => 'nullable|in:open,in_progress,resolved,closed',
        ]);

        $user = Auth::user();

        $reply = SupportTicketReply::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'admin',
            'sender_name' => $user->name,
            'sender_email' => $user->email,
            'message'     => $request->message,
            'is_internal' => $request->boolean('is_internal'),
        ]);

        $newStatus = $request->status ?? $ticket->status;
        $ticket->update([
            'status'        => $newStatus,
            'last_reply_at' => now(),
        ]);

        // Only email customer for public replies
        if (!$reply->is_internal) {
            try {
                Mail::to($ticket->customer->email, $ticket->customer->name)
                    ->send(new TicketRepliedCustomer($ticket->fresh(), $reply));
            } catch (\Exception $e) {
                // Log but don't fail the request
                \Log::warning('Failed to send ticket reply email: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Reply sent successfully.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'status'   => 'required|in:open,in_progress,resolved,closed',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $ticket->update(array_filter([
            'status'   => $request->status,
            'priority' => $request->priority,
        ]));

        return back()->with('success', 'Ticket updated.');
    }

    public function destroy(SupportTicket $ticket)
    {
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket deleted.');
    }
}
