<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\FaceMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::where('user_id', Auth::id())
            ->orderByDesc('date')
            ->paginate(15);

        $todayRecord = Attendance::where('user_id', Auth::id())
            ->whereDate('date', now()->toDateString())
            ->first();

        return view('attendance.index', compact('attendances', 'todayRecord'));
    }

    public function checkIn(Request $request, FaceMatcher $faceMatcher)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            return $this->respondError($request, 'Kamu sudah melakukan check-in hari ini.');
        }

        $photoPath = null;

        // If the user has enrolled a face, verification is mandatory for check-in.
        if ($user->hasFaceEnrolled()) {
            $request->validate([
                'face_descriptor' => ['required', 'array', 'size:128'],
                'face_descriptor.*' => ['numeric'],
                'photo' => ['nullable', 'string'],
            ], [
                'face_descriptor.required' => 'Verifikasi wajah diperlukan untuk check-in.',
            ]);

            if (! $faceMatcher->isMatch($user->face_descriptor, $request->input('face_descriptor'))) {
                return $this->respondError($request, 'Verifikasi wajah gagal. Wajah tidak cocok dengan data terdaftar.');
            }

            if ($request->filled('photo')) {
                $photoPath = $this->storeBase64Photo($request->input('photo'));
            }
        }

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'check_in' => now()->format('H:i:s'),
            'photo_path' => $photoPath,
            'status' => 'hadir',
        ]);

        $message = 'Check-in berhasil dicatat.'.($user->hasFaceEnrolled() ? ' (terverifikasi wajah)' : '');

        return $this->respondSuccess($request, $message);
    }

    public function checkOut(Request $request)
    {
        $record = Attendance::where('user_id', Auth::id())
            ->whereDate('date', now()->toDateString())
            ->first();

        if (! $record) {
            return $this->respondError($request, 'Kamu belum check-in hari ini.');
        }

        if ($record->check_out) {
            return $this->respondError($request, 'Kamu sudah melakukan check-out hari ini.');
        }

        $record->update([
            'check_out' => now()->format('H:i:s'),
        ]);

        return $this->respondSuccess($request, 'Check-out berhasil dicatat.');
    }

    /**
     * Decode a base64 data URL image (from canvas.toDataURL) and store it on the public disk.
     * Returns the stored relative path, or null if the input wasn't a valid image data URL.
     */
    private function storeBase64Photo(string $dataUrl): ?string
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

        $path = 'attendance-photos/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, $decoded);

        return $path;
    }

    /**
     * Face check-in is submitted via fetch/JSON, plain check-in/out via normal form POST.
     * Support both so existing non-face users are unaffected.
     */
    private function respondError(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->isJson()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        return back()->with('error', $message);
    }

    private function respondSuccess(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->isJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
