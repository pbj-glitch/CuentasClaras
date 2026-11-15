<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Pedir unirse a un grupo, y que alguien del grupo acepte o rechace. */
class SolicitudController extends Controller
{
    public function store(Request $request, Grupo $grupo)
    {
        $user = $request->user();
        if ($grupo->miembroDe($user)) {
            return redirect()->route('grupos.show', $grupo);
        }

        // "nuevo" o el id de un integrante sin cuenta (si dice ser esa persona)
        $datos = $request->validate([
            'miembro' => ['required', Rule::in(
                $grupo->miembros->whereNull('user_id')->pluck('id')->map(fn ($id) => (string) $id)->push('nuevo')->all()
            )],
        ]);

        Solicitud::updateOrCreate(
            ['grupo_id' => $grupo->id, 'user_id' => $user->id, 'estado' => 'pendiente'],
            ['miembro_reclamado_id' => $datos['miembro'] === 'nuevo' ? null : (int) $datos['miembro']],
        );

        return redirect()->route('grupos.index')
            ->with('ok', "Solicitud enviada a {$grupo->nombre}. Apenas te acepten, lo vas a ver aquí.");
    }

    public function aceptar(Grupo $grupo, Solicitud $solicitud)
    {
        $this->miembroActual($grupo);
        abort_unless($solicitud->estado === 'pendiente', 410);

        $miembro = $grupo->incorporar($solicitud->user, $solicitud->miembroReclamado);
        $solicitud->update(['estado' => 'aprobada']);

        return back()->with('ok', "{$miembro->nombre_alias} ya es parte del grupo.");
    }

    public function rechazar(Grupo $grupo, Solicitud $solicitud)
    {
        $this->miembroActual($grupo);
        $solicitud->update(['estado' => 'rechazada']);

        return back()->with('ok', 'Solicitud rechazada.');
    }
}
