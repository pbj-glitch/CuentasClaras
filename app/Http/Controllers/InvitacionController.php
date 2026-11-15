<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\Request;

class InvitacionController extends Controller
{
    /** Pantalla "Invitar": código del grupo, solicitudes pendientes e integrantes sin cuenta. */
    public function show(Grupo $grupo)
    {
        $this->miembroActual($grupo);

        return view('invitar.show', [
            'grupo' => $grupo,
            'solicitudes' => $grupo->solicitudes()->with('user', 'miembroReclamado')->where('estado', 'pendiente')->latest()->get(),
            'sinCuenta' => $grupo->miembros->whereNull('user_id'),
        ]);
    }

    public function unirse()
    {
        return view('unirse.index');
    }

    /** Formulario de código: lleva a la página del grupo (la misma a la que llevará el QR). */
    public function buscar(Request $request)
    {
        $request->validate(['codigo' => ['required', 'string', 'max:12']]);

        return redirect()->route('unirse.show', self::normalizar($request->input('codigo')));
    }

    public function grupo(Request $request, string $codigo)
    {
        $grupo = Grupo::with('miembros', 'gastos.partes', 'pagos')
            ->where('codigo_invitacion', self::normalizar($codigo))
            ->first();

        if (! $grupo) {
            return redirect()->route('unirse.index')->withErrors([
                'codigo' => 'Ese código no existe. Pídele el código a alguien del grupo.',
            ]);
        }

        if ($grupo->miembroDe($request->user())) {
            return redirect()->route('grupos.show', $grupo);
        }

        return view('unirse.show', [
            'grupo' => $grupo,
            'sinCuenta' => $grupo->miembros->whereNull('user_id'),
            'saldos' => $grupo->saldos(),
            'pendiente' => $grupo->solicitudes()
                ->where('user_id', $request->user()->id)
                ->where('estado', 'pendiente')
                ->exists(),
        ]);
    }

    /** "7k4m2q", "7K4 M2Q" o "7k4-m2q" → "7K4-M2Q" */
    public static function normalizar(string $codigo): string
    {
        $limpio = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $codigo));

        return strlen($limpio) === 6 ? substr($limpio, 0, 3).'-'.substr($limpio, 3) : $limpio;
    }
}
