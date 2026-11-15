<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GastoController extends Controller
{
    public function create(Grupo $grupo)
    {
        $yo = $this->miembroActual($grupo);

        return view('gastos.create', [
            'grupo' => $grupo,
            'yo' => $yo,
            // Un ítem vacío para partir, dividido entre todos.
            // GroqController puede llenar esto mismo con lo que lea de una boleta.
            'items' => [['nombre' => '', 'cantidad' => 1, 'precio_unitario' => '', 'miembros' => $grupo->miembros->pluck('id')->all()]],
        ]);
    }

    public function store(Request $request, Grupo $grupo)
    {
        $this->miembroActual($grupo);
        $ids = $grupo->miembros->pluck('id')->all();

        $datos = $request->validate([
            'comercio' => ['required', 'string', 'max:80'],
            'pagado_por_miembro_id' => ['required', Rule::in($ids)],
            'propina' => ['nullable', 'integer', 'min:0'],
            'items' => ['required', 'array', 'min:1', 'max:60'],
            'items.*.nombre' => ['required', 'string', 'max:120'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.precio_unitario' => ['required', 'integer', 'min:1'],
            'items.*.miembros' => ['required', 'array', 'min:1'],
            'items.*.miembros.*' => [Rule::in($ids)],
        ], [
            'items.*.miembros.required' => 'Cada ítem tiene que ser de alguien: marca al menos una persona.',
        ]);

        Gasto::registrar(
            $grupo,
            $grupo->miembros->firstWhere('id', $datos['pagado_por_miembro_id']),
            $datos['comercio'],
            array_values($datos['items']),
            (int) ($datos['propina'] ?? 0),
        );

        return redirect()->route('grupos.show', $grupo)->with('ok', 'Gasto guardado ✓');
    }

    public function destroy(Grupo $grupo, Gasto $gasto)
    {
        $this->miembroActual($grupo);
        $gasto->delete();

        return redirect()->route('grupos.show', $grupo)->with('ok', 'Gasto eliminado.');
    }
}
