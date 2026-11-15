<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** "Marcar pagado": registra un reembolso entre dos integrantes. */
class PagoController extends Controller
{
    public function store(Request $request, Grupo $grupo)
    {
        $this->miembroActual($grupo);
        $ids = $grupo->miembros->pluck('id')->all();

        $datos = $request->validate([
            'de_miembro_id' => ['required', Rule::in($ids)],
            'para_miembro_id' => ['required', Rule::in($ids), 'different:de_miembro_id'],
            'monto' => ['required', 'integer', 'min:1'],
        ]);

        $grupo->pagos()->create($datos);

        return redirect()->route('grupos.saldos', $grupo)->with('ok', 'Pago registrado ✓');
    }

    public function destroy(Grupo $grupo, Pago $pago)
    {
        $this->miembroActual($grupo);
        $pago->delete();

        return redirect()->route('grupos.saldos', $grupo)->with('ok', 'Pago deshecho.');
    }
}
