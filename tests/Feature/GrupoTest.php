<?php

namespace Tests\Feature;

use App\Models\Gasto;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrupoTest extends TestCase
{
    use RefreshDatabase;

    private function grupoDe(User $user): Grupo
    {
        $grupo = Grupo::create(['nombre' => 'Viaje a Pucón', 'creador_id' => $user->id]);
        $grupo->agregarMiembro($user->name, $user, admin: true);

        return $grupo;
    }

    public function test_al_crear_un_grupo_se_genera_su_codigo_de_invitacion(): void
    {
        $grupo = $this->grupoDe(User::factory()->create());

        $this->assertMatchesRegularExpression('/^[A-Z2-9]{3}-[A-Z2-9]{3}$/', $grupo->codigo_invitacion);
    }

    public function test_un_gasto_registrado_actualiza_los_saldos(): void
    {
        $tomas = User::factory()->create(['name' => 'Tomás']);
        $grupo = $this->grupoDe($tomas);
        $yo = $grupo->miembroDe($tomas);
        $ana = $grupo->agregarMiembro('Ana');
        $benja = $grupo->agregarMiembro('Benja');

        $gasto = Gasto::registrar($grupo, $yo, 'Pizzería', [
            ['nombre' => 'Pizza familiar', 'precio_unitario' => 15000, 'miembros' => [$yo->id, $ana->id, $benja->id]],
            ['nombre' => 'Cerveza', 'cantidad' => 2, 'precio_unitario' => 3000, 'miembros' => [$ana->id]],
        ], propina: 2100);

        $this->assertSame(21000, $gasto->subtotal);
        $this->assertSame(23100, $gasto->total);
        $this->assertSame(23100, (int) $gasto->partes()->sum('monto_asignado'));
        $this->assertSame(5500, $gasto->parteDe($yo));

        // Tomás pagó 23.100 y le tocaban 5.500; Ana debe 5.500 + 6.600; Benja 5.500
        $this->assertSame([$yo->id => 17600, $ana->id => -12100, $benja->id => -5500], $grupo->saldos());
    }

    public function test_un_pago_salda_la_deuda(): void
    {
        $user = User::factory()->create();
        $grupo = $this->grupoDe($user);
        $yo = $grupo->miembroDe($user);
        $ana = $grupo->agregarMiembro('Ana');

        Gasto::registrar($grupo, $yo, null, [
            ['nombre' => 'Supermercado', 'precio_unitario' => 10000, 'miembros' => [$yo->id, $ana->id]],
        ]);
        $this->assertSame([['de' => $ana->id, 'a' => $yo->id, 'monto' => 5000]], $grupo->fresh()->transferencias());

        $grupo->pagos()->create(['de_miembro_id' => $ana->id, 'para_miembro_id' => $yo->id, 'monto' => 5000]);

        $this->assertSame([], $grupo->fresh()->transferencias());
    }

    public function test_un_usuario_puede_reclamar_un_integrante_sin_cuenta(): void
    {
        $grupo = $this->grupoDe(User::factory()->create());
        $benja = $grupo->agregarMiembro('Benja');
        $nuevo = User::factory()->create(['name' => 'Benjamín']);

        $miembro = $grupo->incorporar($nuevo, $benja);

        $this->assertTrue($miembro->is($benja));
        $this->assertSame($nuevo->id, $benja->fresh()->user_id);
        $this->assertCount(2, $grupo->miembros);
        $this->assertTrue($nuevo->grupos->contains($grupo));
    }

    public function test_incorporar_dos_veces_no_duplica_al_integrante(): void
    {
        $user = User::factory()->create();
        $grupo = $this->grupoDe(User::factory()->create());

        $grupo->incorporar($user);
        $grupo->incorporar($user);

        $this->assertSame(1, $grupo->miembros()->where('user_id', $user->id)->count());
    }
}
