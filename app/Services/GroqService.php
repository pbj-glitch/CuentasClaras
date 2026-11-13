<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GroqService
{
    public function analizarTexto(string $promptUsuario): array
    {
        $response = Http::withToken(config('services.groq.key'))
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.text_model'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => 'Responde estrictamente en JSON válido.'],
                    ['role' => 'user', 'content' => $promptUsuario],
                ],
            ]);

        return json_decode($response->json('choices.0.message.content', '{}'), true);
    }
}