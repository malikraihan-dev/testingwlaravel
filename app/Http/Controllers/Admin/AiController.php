<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'history' => ['required', 'array', 'min:1'],
            'history.*.role' => ['required', 'in:user,assistant'],
            'history.*.content' => ['required', 'string'],
        ]);

        $apiKey = env('ANTHROPIC_API_KEY');

        if (! $apiKey) {
            return response()->json([
                'error' => 'ANTHROPIC_API_KEY belum diatur di file .env server.',
            ], 500);
        }

        // Build real context from the database so the AI answers based on actual data.
        $today = now()->toDateString();
        $totalUsers = User::where('role', 'user')->count();
        $todayCounts = Attendance::whereDate('date', $today)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $last7 = collect(range(6, 0))->map(function ($i) {
            $date = now()->subDays($i)->toDateString();
            $counts = Attendance::whereDate('date', $date)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            return sprintf(
                '%s: hadir=%d, izin=%d, sakit=%d, alpa=%d',
                $date,
                $counts['hadir'] ?? 0,
                $counts['izin'] ?? 0,
                $counts['sakit'] ?? 0,
                $counts['alpa'] ?? 0
            );
        })->implode("\n");

        $systemPrompt = <<<PROMPT
        Kamu adalah "Workforce AI Assistant", asisten yang membantu admin menganalisis data kehadiran karyawan.
        Jawab singkat, jelas, dan dalam Bahasa Indonesia kecuali diminta bahasa lain.
        Gunakan HANYA data berikut sebagai konteks, jangan mengarang angka lain:

        Total user (non-admin): {$totalUsers}
        Ringkasan hari ini ({$today}): hadir={$todayCounts['hadir']} izin={$todayCounts['izin']} sakit={$todayCounts['sakit']} alpa={$todayCounts['alpa']}

        Ringkasan 7 hari terakhir:
        {$last7}
        PROMPT;

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-5',
                'max_tokens' => 500,
                'system' => $systemPrompt,
                'messages' => $request->input('history'),
            ]);

            if ($response->failed()) {
                Log::error('Anthropic API error', ['body' => $response->body()]);

                return response()->json([
                    'error' => 'Gagal menghubungi AI (cek API key dan kuota).',
                ], 500);
            }

            $data = $response->json();
            $textParts = collect($data['content'] ?? [])
                ->where('type', 'text')
                ->pluck('text');

            return response()->json([
                'reply' => $textParts->implode("\n") ?: 'Maaf, tidak ada respons dari AI.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Anthropic API exception', ['message' => $e->getMessage()]);

            return response()->json([
                'error' => 'Terjadi kesalahan saat menghubungi AI.',
            ], 500);
        }
    }
}
