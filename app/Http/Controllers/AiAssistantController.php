<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\AiAssistantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAssistantController extends Controller
{
    public function __construct(private AiAssistantService $ai)
    {
    }

    /**
     * Chatbot untuk user: tanya-jawab seputar absensi mereka sendiri.
     * Hanya diberi akses ke data 30 hari terakhir milik user yang login.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $user = Auth::user();

        $history = Attendance::where('user_id', $user->id)
            ->orderByDesc('date')
            ->take(30)
            ->get(['date', 'check_in', 'check_out', 'status', 'notes'])
            ->map(function ($a) {
                return sprintf(
                    '%s: status=%s, masuk=%s, pulang=%s%s',
                    $a->date->format('Y-m-d'),
                    $a->status,
                    $a->check_in ?? '-',
                    $a->check_out ?? '-',
                    $a->notes ? ", catatan={$a->notes}" : ''
                );
            })
            ->implode("\n");

        if ($history === '') {
            $history = '(belum ada data absensi)';
        }

        $system = "Kamu adalah asisten absensi ramah untuk aplikasi kantor/kampus. "
            . "Jawab HANYA berdasarkan data absensi user berikut (30 hari terakhir), dalam Bahasa Indonesia, "
            . "singkat, dan sopan. Jangan mengarang data yang tidak ada di daftar. Kalau ditanya di luar "
            . "topik absensi, arahkan kembali dengan sopan.\n\n"
            . "Data absensi {$user->name}:\n{$history}";

        $reply = $this->ai->ask($system, $request->input('message'));

        return response()->json(['reply' => $reply]);
    }

    /**
     * AI Insight untuk admin: ringkasan pola kehadiran tim 30 hari terakhir + saran tindakan.
     */
    public function insight()
    {
        $summary = Attendance::selectRaw('status, count(*) as total')
            ->whereBetween('date', [now()->subDays(30)->toDateString(), now()->toDateString()])
            ->groupBy('status')
            ->pluck('total', 'status');

        $problemUsers = Attendance::with('user')
            ->whereBetween('date', [now()->subDays(30)->toDateString(), now()->toDateString()])
            ->whereIn('status', ['alpa', 'izin', 'sakit'])
            ->get()
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->first()->user->name.': '.$rows->count().'x')
            ->values()
            ->implode(', ');

        if ($problemUsers === '') {
            $problemUsers = '(tidak ada)';
        }

        $system = 'Kamu adalah analis HR. Berdasarkan ringkasan data absensi tim 30 hari terakhir, '
            . 'berikan 3-4 poin insight singkat dan saran tindakan konkret untuk admin, dalam Bahasa '
            . 'Indonesia, format bullet point. Jangan mengarang angka yang tidak diberikan.';

        $userMessage = 'Ringkasan jumlah per status: '.json_encode($summary)."\n"
            ."User dengan izin/sakit/alpa terbanyak (30 hari terakhir): {$problemUsers}";

        $reply = $this->ai->ask($system, $userMessage);

        return response()->json(['reply' => $reply]);
    }
}
