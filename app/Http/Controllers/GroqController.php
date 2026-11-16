<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;
use App\Services\GroqService;

class GroqController extends Controller
{
    public function analizar(Request $request, Grupo $grupo, GroqService $groq)
    {
        $yo = $this->miembroActual($grupo);

        $request->validate([
            'texto' => ['nullable', 'string', 'max:1000', 'required_without:boleta'],
            'boleta' => ['nullable', 'image', 'max:8192', 'required_without:texto'],
        ], [
            'texto.required_without' => 'Escribe una descripción o sube una foto de la boleta.',
            'boleta.required_without' => 'Sube una foto de la boleta o escribe el gasto.',
        ]);

        $miembrosLista = $grupo->miembros
            ->map(fn ($m) => "ID {$m->id}: {$m->nombre_alias}".($m->id === $yo->id ? ' (usuario actual / "yo")' : ''))
            ->implode(', ');

        $instrucciones = <<<PROMPT
Extrae los datos del gasto o boleta para dividir la cuenta en pesos chilenos (CLP, enteros sin decimales).
Integrantes del grupo: [{$miembrosLista}].
Si el texto dice "yo" o "pagué", corresponde al ID {$yo->id}. Si no se especifica quién consumió un ítem, asígnalo a todos los IDs del grupo.
Responde ESTRICTAMENTE con este formato JSON:
{
  "comercio": "Nombre del local o descripción breve",
  "pagado_por_miembro_id": {$yo->id},
  "propina": 0,
  "items": [
    {"nombre": "Producto", "cantidad": 1, "precio_unitario": 1000, "miembros": [1, 2]}
  ]
}
Importante: "precio_unitario" es el precio de 1 unidad (si dice "2 cervezas por 6000", cantidad=2 y precio_unitario=3000; si dice "15 lucas", son 15000).
PROMPT;

        if($request->hasFile('boleta')) {
            $archivo = $request->file('boleta');
            $base64 = base64_encode(file_get_contents($archivo->getRealPath()));
            $extra = $request->filled('texto') ? "\nInstrucción adicional del usuario:".$request->input('texto') : '';
            $respuesta = $groq->analizarImagen($base64, $archivo->getMimeType(), $instrucciones.$extra);
        } else {
            $respuesta = $groq->analizarTexto($instrucciones. "\n\nTexto del usuario:".request->input('texto'));
        }

        if (empty($respuesta) || empty($respuesta['items']) || ! is_array($respuesta['items'])) {
            return back()
                ->withInput()
                ->withErrors(['ia' => 'No pudimos interpretar el gasto con IA. Intenta de nuevo o ingrésalo manualmente.']);
        }

        $idsValidos = $grupo->miembros->pluck('id')->all();
        $items = [];

        foreach ($respuesta['items'] as $item) {
            $miembrosItem = array_values(array_intersect(
                array_map('intval', (array) ($item['miembros'] ?? [])),
                $idsValidos
            ));

            $items[] = [
                'nombre' => (string) ($item['nombre'] ?? 'Ítem'),
                'cantidad' => max(1, (int) ($item['cantidad'] ?? 1)),
                'precio_unitario' => max(1, (int) ($item['precio_unitario'] ?? 0)),
                'miembros' => $miembrosItem ?: $idsValidos,
            ];
        }

        $pagadorId = (int) ($respuesta['pagado_por_miembro_id'] ?? $yo->id);
        if (! in_array($pagadorId, $idsValidos, true)) {
            $pagadorId = $yo->id;
        }

        return redirect()
        ->route('gastos.create', $grupo)
        ->withInput([
            'comercio' => (string) ($respuesta['comercio'] ?? 'Gasto'),
            'pagado_por_miembro_id' => $pagadorId,
            'propina' => max(0, (int) ($respuesta['propina'] ?? 0)),
            'items' => $items
        ])
        ->with('ok', 'Boleta analizada con IA. Revisa los datos y guarda el gasto.');
    }
}