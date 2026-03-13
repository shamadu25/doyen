<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\JobCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with(['documentable', 'uploader']);

        if ($request->filled('type')) {
            $query->where('documentable_type', $request->type);
        }

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        $documents = $query->latest()->paginate(20);

        return view('admin.documents.index', compact('documents'));
    }

    public function create(Request $request)
    {
        $documentableType = $request->input('type');
        $documentableId = $request->input('id');

        return view('admin.documents.create', compact('documentableType', 'documentableId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'documentable_type' => 'required|string',
            'documentable_id' => 'required|integer',
            'document_type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        $path = $file->storeAs(
            "documents/{$validated['documentable_type']}/{$validated['documentable_id']}",
            $fileName,
            'public'
        );

        $document = Document::create([
            'documentable_type' => $validated['documentable_type'],
            'documentable_id' => $validated['documentable_id'],
            'document_type' => $validated['document_type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'file_name' => $originalName,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'document' => $document,
            ]);
        }

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function show(Document $document)
    {
        $document->load(['documentable', 'uploader']);

        return view('admin.documents.show', compact('document'));
    }

    public function download(Document $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function destroy(Document $document)
    {
        $document->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Document deleted successfully.');
    }

    /**
     * Get documents for a specific model (AJAX)
     */
    public function getForModel(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'id' => 'required|integer',
        ]);

        $documents = Document::where('documentable_type', $validated['type'])
            ->where('documentable_id', $validated['id'])
            ->with('uploader')
            ->latest()
            ->get();

        return response()->json($documents);
    }

    /**
     * Upload multiple files (AJAX)
     */
    public function uploadMultiple(Request $request)
    {
        $validated = $request->validate([
            'documentable_type' => 'required|string',
            'documentable_id' => 'required|integer',
            'document_type' => 'required|string',
            'files.*' => 'required|file|max:10240',
        ]);

        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '_' . Str::random(4) . '.' . $file->getClientOriginalExtension();
            
            $path = $file->storeAs(
                "documents/{$validated['documentable_type']}/{$validated['documentable_id']}",
                $fileName,
                'public'
            );

            $document = Document::create([
                'documentable_type' => $validated['documentable_type'],
                'documentable_id' => $validated['documentable_id'],
                'document_type' => $validated['document_type'],
                'title' => pathinfo($originalName, PATHINFO_FILENAME),
                'file_name' => $originalName,
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);

            $uploaded[] = $document;
        }

        return response()->json([
            'success' => true,
            'uploaded' => count($uploaded),
            'documents' => $uploaded,
        ]);
    }

    /**
     * Upload a diagnostic/diagnostic report document for a Job Card (Inertia).
     * Accepts: title, document_type, visible_to_customer, file.
     */
    public function uploadForJobCard(Request $request, JobCard $jobCard)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'document_type'       => 'required|string|max:100',
            'description'         => 'nullable|string|max:500',
            'visible_to_customer' => 'boolean',
            'file'                => 'required|file|max:20480|mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,csv,txt',
        ]);

        $file         = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileName     = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs(
            'documents/job-cards/' . $jobCard->id,
            $fileName,
            'public'
        );

        $jobCard->documents()->create([
            'document_type'       => $validated['document_type'],
            'title'               => $validated['title'],
            'description'         => $validated['description'] ?? null,
            'file_name'           => $originalName,
            'file_path'           => $path,
            'mime_type'           => $file->getMimeType(),
            'file_size'           => $file->getSize(),
            'uploaded_by'         => auth()->id(),
            'visible_to_customer' => $request->boolean('visible_to_customer'),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Toggle visible_to_customer on an existing document.
     */
    public function toggleVisibility(Document $document)
    {
        $document->update(['visible_to_customer' => !$document->visible_to_customer]);
        return back()->with('success', 'Visibility updated.');
    }

    /**
     * Customer portal: download a document that is marked visible_to_customer
     * and belongs to this customer's job card.
     */
    public function customerDownload(Document $document)
    {
        $customerId = session('customer_id');
        if (!$customerId) {
            abort(403, 'Not authenticated.');
        }

        // Verify document is visible and belongs to this customer
        if (!$document->visible_to_customer) {
            abort(403, 'This document is not available for download.');
        }

        // Only documents attached to JobCards are currently supported for customer download
        if ($document->documentable_type !== JobCard::class) {
            abort(403);
        }

        $jobCard = JobCard::findOrFail($document->documentable_id);
        if ($jobCard->customer_id !== $customerId) {
            abort(403, 'Access denied.');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
}
