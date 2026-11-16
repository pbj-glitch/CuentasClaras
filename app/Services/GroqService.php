<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class GroqService
{
    public function analizarTexto(string $promptUsuario): array
    {
        try {
            $response = Http::withToken(config('services.groq.key'))
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.groq.text_model'),
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => 'Responde estrictamente en JSON válido.'],
                        ['role' => 'user', 'content' => $promptUsuario],
                    ],
                ]);

            if (! $response->successful()) {
                return [];
            }

            $datos = json_decode($response->json('choices.0.message.content', '{}'), true);

            return is_array($datos) ? $datos : [];
        } catch (Throwable) {
            return [];
        }
    }

    public function analizarImagen(string $base64, string $mimeType, string $promptUsuario): array
    {
        try {
            $response = Http::withToken(config('services.groq.key'))
            ->timeout(45)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.vision_model'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $promptUsuario],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$base64}",
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

            if (! $response->successful()) {
                return [];
            }

            $datos = json_decode($response->json('choices.0.message.content', '{}'), true);

            return is_array($datos) ? $datos : [];
        } catch (Throwable) {
            return [];
        }
    }
}