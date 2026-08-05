<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FaceEnrollController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('admin.face.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('admin.face.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'face_descriptor' => ['required', 'array', 'size:128'],
            'face_descriptor.*' => ['numeric'],
            'photo' => ['required', 'string'],
        ]);

        $newPath = FaceEnrollController::storeBase64Photo($request->input('photo'), 'face-photos');

        if (! $newPath) {
            return response()->json(['success' => false, 'message' => 'Foto tidak valid.'], 422);
        }

        if ($user->face_photo_path) {
            Storage::disk('public')->delete($user->face_photo_path);
        }

        $user->update([
            'face_descriptor' => $request->input('face_descriptor'),
            'face_photo_path' => $newPath,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(User $user)
    {
        if ($user->face_photo_path) {
            Storage::disk('public')->delete($user->face_photo_path);
        }

        $user->update(['face_descriptor' => null, 'face_photo_path' => null]);

        return back()->with('success', "Verifikasi wajah {$user->name} berhasil dihapus.");
    }
}
