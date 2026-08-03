<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        ]);

        $request->user()->update([
            'face_descriptor' => $request->input('face_descriptor'),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $request->user()->update(['face_descriptor' => null]);

        return back()->with('success', 'Verifikasi wajah dinonaktifkan.');
    }
}
