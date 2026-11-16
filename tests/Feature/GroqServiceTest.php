<?php

namespace Tests\Feature;

use App\Services\GroqService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GroqServiceTest extends TestCase
{
    public function test_analizar_texto_devuelve_el_json_de_la_respuesta(): void
    {
        config(['services.groq.key' => 'clave-de-prueba', 'services.groq.text_model' => 'modelo-de-prueba']);
        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [['message' => ['content' => '{"comercio":"La Pica","total":12000}']]],
            ]),
        ]);

        $resultado = (new GroqService)->analizarTexto('pagué 12 lucas en La Pica');

        $this->assertSame(['comercio' => 'La Pica', 'total' => 12000], $resultado);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer clave-de-prueba')
            && $request['model'] === 'modelo-de-prueba');
    }

    public function test_analizar_texto_devuelve_arreglo_vacio_si_la_respuesta_no_es_json(): void
    {
        config(['services.groq.key' => 'clave-de-prueba', 'services.groq.text_model' => 'modelo-de-prueba']);
        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [['message' => ['content' => 'Esto no es un JSON válido']]],
            ]),
        ]);

        $resultado = (new GroqService)->analizarTexto('hola');

        $this->assertSame([], $resultado);
    }

    public function test_analizar_texto_devuelve_arreglo_vacio_si_la_api_falla(): void
    {
        config(['services.groq.key' => 'clave-de-prueba', 'services.groq.text_model' => 'modelo-de-prueba']);
        Http::fake([
            'api.groq.com/*' => Http::response('Error interno', 500),
        ]);

        $resultado = (new GroqService)->analizarTexto('hola');

        $this->assertSame([], $resultado);
    }
}
