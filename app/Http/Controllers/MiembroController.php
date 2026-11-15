<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\Request;

/** Agrega a alguien que todavía no tiene la app, para anotarle gastos. */
class MiembroController extends Controller
{
    public function store(Request $request, Grupo $grupo)
    {
        $this->miembroActual($grupo);
        $datos = $request->validate(['nombre_alias' => ['required', 'string', 'max:30']]);
        $grupo->agregarMiembro(trim($datos['nombre_alias']));

        return back()->with('ok', "{$datos['nombre_alias']} agregado al grupo.");
    }
}
