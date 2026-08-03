<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    public function ask(string $systemPrompt, string $userMessage): string
    {
        $apiKey = config('services.anthropic.key');

        if (! $apiKey) {
            return 'Asisten AI belum dikonfigurasi. Tambahkan ANTHROPIC_API_KEY di file .env.';
        }

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-sonnet-4-6',
            'max_tokens' => 500,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        if ($response->failed()) {
            Log::warning('Anthropic API error', ['body' => $response->body()]);

            return 'Maaf, asisten AI sedang tidak bisa diakses. Coba lagi nanti.';
        }

        $blocks = $response->json('content', []);

        $text = collect($blocks)
            ->where('type', 'text')
            ->pluck('text')
            ->implode("\n");

        return $text !== '' ? $text : 'Maaf, tidak ada jawaban dari asisten AI.';
    }
}
