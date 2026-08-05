<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::with(['owner', 'latestVersion', 'collaborators'])
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('admin.documents.index', compact('documents'));
    }

    public function destroy(Document $document)
    {
        foreach ($document->versions as $version) {
            Storage::disk('public')->delete($version->file_path);
        }

        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus oleh admin.');
    }
}
