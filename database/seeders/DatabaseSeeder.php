<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Gasto;
use App\Models\Grupo;
use App\Models\Solicitud;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $vicente = User::factory()->create([
            'name' => 'Vicente',
            'email' => 'Vicente@cuentasclaras.cl',
            'password' => Hash::make('contraseña'),
        ]);

        $maria = User::factory()->create([
            'name' => 'Maria',
            'email' => 'Maria@cuentasclaras.cl',
            'password' => Hash::make('contraseña'),
        ]);

        $matias = User::factory()->create([
            'name' => 'Matias',
            'email' => 'Matias@cuentasclaras.cl',
            'password' => Hash::make('contraseña'),
        ]);

        $viaje = Grupo::create([
            'nombre' => 'Viaje a Pucón',
            'codigo_invitacion' => 'PUC-206',
            'creador_id' => $vicente->id,
        ]);

        $mVicente = $viaje->agregarMiembro('Vicente', $vicente, admin: true);
        $mMaria = $viaje->agregarMiembro('Maria', $maria);
        $mCaro = $viaje->agregarMiembro('Caro');
        $mBenja = $viaje->agregarMiembro('Benja');


        $todos = [$mVicente->id, $mMaria->id, $mCaro->id, $mBenja->id];

        Gasto::registrar(
            $viaje,
            $mVicente,
            'Cena en La Maga',
            [
                ['nombre' => 'Tabla compartida', 'cantidad' => 1, 'precio_unitario' => 24000, 'miembros' => $todos],
                ['nombre' => 'Pisco Sour', 'cantidad' => 2, 'precio_unitario' => 5500, 'miembros' => [$mCaro->id, $mMaria->id]],
                ['nombre' => 'Jugo natural', 'cantidad' => 2, 'precio_unitario' => 3500, 'miembros' => [$mVicente->id, $mBenja->id]],
            ],
            propina: 4200
        );

        Gasto::registrar(
            $viaje,
            $mMaria,
            'Copec Ruta 5',
            [
                ['nombre' => 'Bencina 95', 'cantidad' => 1, 'precio_unitario' => 42000, 'miembros' => $todos],
            ]
        );

        $viaje->pagos()->create([
            'de_miembro_id' => $mCaro->id,
            'para_miembro_id' => $mVicente->id,
            'monto' => 5000,
        ]);

        Solicitud::create([
            'grupo_id' => $viaje->id,
            'user_id' => $matias->id,
            'miembro_reclamado_id' => $mBenja->id,
            'estado' => 'pendiente',
        ]);

        $asado = Grupo::create([
            'nombre' => 'Asado del viernes',
            'codigo_invitacion' => 'ASA-999',
            'creador_id' => $maria->id,
        ]);

        $aMaria = $asado->agregarMiembro('Maria', $maria, admin:true);
        $aVicente = $asado->agregarMiembro('Vicente', $vicente);

        Gasto::registrar(
            $asado,
            $aMaria,
            'Supermercado Lider',
            [
                ['nombre' => 'Carne y choripanes', 'cantidad' => 1, 'precio_unitario' => 28000, 'miembros' => [$aMaria->id, $aVicente->id]],
                ['nombre' => 'Carbon', 'cantidad' => 1, 'precio_unitario' => 4500, 'miembros' => [$aMaria->id, $aVicente->id]],
            ]
        );
    }
}
