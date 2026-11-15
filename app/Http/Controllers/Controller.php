<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Miembro;

abstract class Controller
{
    /** El integrante del usuario conectado en ese grupo; 403 si no es parte. */
    protected function miembroActual(Grupo $grupo): Miembro
    {
        $miembro = $grupo->miembros->firstWhere('user_id', auth()->id());
        abort_unless($miembro, 403, 'No eres parte de este grupo.');

        return $miembro;
    }
}
