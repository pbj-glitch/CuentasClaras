<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $grupos = $user->grupos()
            ->with('miembros', 'gastos.partes', 'pagos')
            ->get()
            ->map(function (Grupo $grupo) use ($user) {
                $grupo->mi_saldo = $grupo->saldos()[$grupo->miembroDe($user)->id] ?? 0;

                return $grupo;
            });

        $pendientes = Solicitud::with('grupo')
            ->where('user_id', $user->id)
            ->where('estado', 'pendiente')
            ->get();

        return view('grupos.index', [
            'grupos' => $grupos,
            'pendientes' => $pendientes,
            'teDeben' => $grupos->where('mi_saldo', '>', 0)->sum('mi_saldo'),
            'debes' => -$grupos->where('mi_saldo', '<', 0)->sum('mi_saldo'),
        ]);
    }

    public function create()
    {
        return view('grupos.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:40'],
            'integrantes' => ['array', 'max:30'],
            'integrantes.*' => ['nullable', 'string', 'max:30'],
        ]);

        $grupo = DB::transaction(function () use ($datos, $request) {
            $grupo = Grupo::create([
                'nombre' => $datos['nombre'],
                'creador_id' => $request->user()->id,
            ]);

            $grupo->agregarMiembro($request->user()->name, $request->user(), admin: true);

            foreach (array_filter(array_map('trim', $datos['integrantes'] ?? [])) as $nombre) {
                $grupo->agregarMiembro($nombre);
            }

            return $grupo;
        });

        return redirect()->route('invitar.show', $grupo)
            ->with('ok', 'Grupo creado. Invita a tus amigos con el código.');
    }

    public function show(Grupo $grupo)
    {
        $grupo->load('miembros', 'gastos.pagador', 'gastos.partes', 'gastos.items.partes');
        $yo = $this->miembroActual($grupo);
        $saldos = $grupo->saldos();
        arsort($saldos);

        return view('grupos.show', [
            'grupo' => $grupo,
            'yo' => $yo,
            'saldos' => $saldos,
            'gastosPorDia' => $grupo->gastos->groupBy(fn ($g) => $g->created_at->toDateString()),
        ]);
    }

    public function saldos(Grupo $grupo)
    {
        $grupo->load('miembros', 'gastos.partes', 'pagos.deMiembro', 'pagos.paraMiembro');
        $yo = $this->miembroActual($grupo);
        $saldos = $grupo->saldos();
        arsort($saldos);

        return view('grupos.saldos', [
            'grupo' => $grupo,
            'yo' => $yo,
            'saldos' => $saldos,
            'maximo' => max(1, ...array_map('abs', $saldos ?: [0])),
            'transferencias' => $grupo->transferencias(),
        ]);
    }

    public function estadoJson(Grupo $grupo)
    {
        $this->miembroActual($grupo);

        $version = md5( json_encode([
            'gastos_count' => $grupo->gastos()->count(),
            'gastos_max' => $grupo->gastos()->max('update_at'),
            'pagos_count' => $grupo->pagos()->count(),
            'pagos_max' => $grupo->pagos()->max('update_at'),
            'miembros_count' => $grupo->miembros()->count(),
            'miembros_max' => $grupo->miembros()->max('update_at'),
            'solicitudes_pendientes' => $grupo->solicitudes()->where('estado', 'pendiente')->count(),
        ]));

        return response()->json([
            'version' => $version,
        ]);
    }
}
