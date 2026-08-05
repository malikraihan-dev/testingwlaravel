<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    private const ALLOWED_MIMES = 'doc,docx,pdf,xls,xlsx';
    private const MAX_KB = 10240; // 10MB

    public function index()
    {
        $userId = Auth::id();

        $documents = Document::where('owner_id', $userId)
            ->orWhereHas('collaborators', fn ($q) => $q->where('user_id', $userId))
            ->with(['owner', 'latestVersion', 'collaborators'])
            ->orderByDesc('updated_at')
            ->paginate(10);

        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['required', 'file', 'mimes:'.self::ALLOWED_MIMES, 'max:'.self::MAX_KB],
        ]);

        $document = Document::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'owner_id' => Auth::id(),
        ]);

        $this->storeNewVersion($document, $request->file('file'));

        return redirect()->route('documents.show', $document)->with('success', 'Dokumen berhasil diupload.');
    }

    public function show(Document $document)
    {
        abort_unless($document->isAccessibleBy(Auth::user()), 403, 'Kamu tidak punya akses ke dokumen ini.');

        $document->load(['versions.uploader', 'collaborators', 'owner']);

        $availableUsers = User::where('id', '!=', $document->owner_id)
            ->whereNotIn('id', $document->collaborators->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('documents.show', compact('document', 'availableUsers'));
    }

    public function update(Request $request, Document $document)
    {
        abort_unless($document->isOwnedBy(Auth::user()), 403, 'Hanya pemilik dokumen yang bisa mengedit info ini.');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $document->update($data);

        return back()->with('success', 'Info dokumen berhasil diperbarui.');
    }

    public function destroy(Document $document)
    {
        abort_unless($document->isOwnedBy(Auth::user()), 403, 'Hanya pemilik dokumen yang bisa menghapus.');

        foreach ($document->versions as $version) {
            Storage::disk('public')->delete($version->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil dihapus.');
    }

    public function uploadVersion(Request $request, Document $document)
    {
        abort_unless($document->isAccessibleBy(Auth::user()), 403, 'Kamu tidak punya akses ke dokumen ini.');

        $request->validate([
            'file' => ['required', 'file', 'mimes:'.self::ALLOWED_MIMES, 'max:'.self::MAX_KB],
        ]);

        $this->storeNewVersion($document, $request->file('file'));
        $document->touch();

        return back()->with('success', 'Versi baru berhasil diupload.');
    }

    public function downloadVersion(DocumentVersion $version)
    {
        abort_unless($version->document->isAccessibleBy(Auth::user()), 403);

        return Storage::disk('public')->download($version->file_path, $version->original_name);
    }

    public function addCollaborator(Request $request, Document $document)
    {
        abort_unless($document->isOwnedBy(Auth::user()), 403, 'Hanya pemilik dokumen yang bisa menambah kolaborator.');

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        if ((int) $data['user_id'] === $document->owner_id) {
            return back()->with('error', 'Pemilik dokumen tidak perlu ditambahkan sebagai kolaborator.');
        }

        $document->collaborators()->syncWithoutDetaching([$data['user_id']]);

        return back()->with('success', 'Kolaborator berhasil ditambahkan.');
    }

    public function removeCollaborator(Document $document, User $user)
    {
        abort_unless($document->isOwnedBy(Auth::user()), 403, 'Hanya pemilik dokumen yang bisa menghapus kolaborator.');

        $document->collaborators()->detach($user->id);

        return back()->with('success', 'Kolaborator berhasil dihapus.');
    }

    private function storeNewVersion(Document $document, $file): void
    {
        $nextVersion = ($document->versions()->max('version_number') ?? 0) + 1;
        $path = $file->store('documents', 'public');

        DocumentVersion::create([
            'document_id' => $document->id,
            'uploaded_by' => Auth::id(),
            'version_number' => $nextVersion,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'extension' => strtolower($file->getClientOriginalExtension()),
        ]);
    }
}
