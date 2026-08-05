<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FaceEnrollController extends Controller
{
    public function showEnrollForm()
    {
        return view('attendance.face-enroll');
    }

    public function store(Request $request)
    {
        $request->validate([
            'face_descriptor' => ['required', 'array', 'size:128'],
            'face_descriptor.*' => ['numeric'],
            'photo' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $photoPath = $user->face_photo_path;

        if ($request->filled('photo')) {
            $newPath = static::storeBase64Photo($request->input('photo'), 'face-photos');
            if ($newPath) {
                if ($photoPath) {
                    Storage::disk('public')->delete($photoPath);
                }
                $photoPath = $newPath;
            }
        }

        $user->update([
            'face_descriptor' => $request->input('face_descriptor'),
            'face_photo_path' => $photoPath,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $user = $request->user();

        if ($user->face_photo_path) {
            Storage::disk('public')->delete($user->face_photo_path);
        }

        $user->update(['face_descriptor' => null, 'face_photo_path' => null]);

        return back()->with('success', 'Verifikasi wajah dinonaktifkan.');
    }

    /**
     * Shared helper: decode a base64 data URL image and store it on the public disk.
     * Used by both self-service enrollment and admin-managed enrollment.
     */
    public static function storeBase64Photo(string $dataUrl, string $folder): ?string
    {
        if (! preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $matches)) {
            return null;
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $data = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $decoded = base64_decode($data);

        if ($decoded === false) {
            return null;
        }

        $path = $folder.'/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, $decoded);

        return $path;
    }
}
