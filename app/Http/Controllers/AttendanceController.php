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

    public function checkIn(Request $request)
    {
        $today = now()->toDateString();
        $user = Auth::user();

        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            return back()->with('error', 'Kamu sudah melakukan check-in hari ini.');
        }

        // Verifikasi wajah sekarang WAJIB. Kalau belum daftar wajah, tolak di sini juga
        // (bukan cuma disembunyikan di tampilan) supaya tidak bisa dilewati.
        if (! $user->face_descriptor) {
            return redirect()
                ->route('attendance.face-enroll')
                ->with('error', 'Kamu perlu mendaftarkan wajah terlebih dahulu sebelum bisa check-in.');
        }

        $request->validate([
            'descriptor' => ['required', 'array', 'size:128'],
            'descriptor.*' => ['numeric'],
            'photo' => ['nullable', 'string', 'max:2000000'],
        ]);

        if (! FaceMatcher::isMatch($user->face_descriptor, $request->input('descriptor'))) {
            return back()->with('error', 'Verifikasi wajah gagal, wajah tidak cocok dengan data terdaftar. Check-in ditolak.');
        }

        $photoPath = $request->filled('photo')
            ? $this->storeCheckInPhoto($request->input('photo'), $user->id)
            : null;

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'check_in' => now()->format('H:i:s'),
            'status' => 'hadir',
            'photo_path' => $photoPath,
        ]);

        return back()->with('success', 'Check-in berhasil dicatat (wajah terverifikasi).');
    }

    public function checkOut(Request $request)
    {
        $record = Attendance::where('user_id', Auth::id())
            ->whereDate('date', now()->toDateString())
            ->first();

        if (! $record) {
            return back()->with('error', 'Kamu belum check-in hari ini.');
        }

        if ($record->check_out) {
            return back()->with('error', 'Kamu sudah melakukan check-out hari ini.');
        }

        $record->update([
            'check_out' => now()->format('H:i:s'),
        ]);

        return back()->with('success', 'Check-out berhasil dicatat.');
    }

    /**
     * Simpan snapshot foto (data URL base64 dari kamera) ke storage/app/public
     * dan kembalikan path relatifnya untuk disimpan di kolom photo_path.
     */
    private function storeCheckInPhoto(string $base64Image, int $userId): ?string
    {
        if (! preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
            return null;
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $data = substr($base64Image, strpos($base64Image, ',') + 1);
        $decoded = base64_decode($data, true);

        if ($decoded === false) {
            return null;
        }

        $filename = 'attendance-photos/'.$userId.'-'.now()->format('Ymd-His').'-'.Str::random(6).'.'.$extension;
        Storage::disk('public')->put($filename, $decoded);

        return $filename;
    }
}
