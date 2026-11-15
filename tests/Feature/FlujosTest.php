<?php

namespace Tests\Feature;

use App\Models\Grupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Recorre la app como un usuario: cuenta, grupo, gastos, pagos e invitaciones. */
class FlujosTest extends TestCase
{
    use RefreshDatabase;

    private function grupoCon(User $user, array $otros = []): Grupo
    {
        $this->actingAs($user)->post(route('grupos.store'), [
            'nombre' => 'Depto',
            'integrantes' => $otros,
        ]);

        return Grupo::latest('id')->first();
    }

    public function test_registrarse_entra_a_mis_grupos(): void
    {
        $this->post(route('register'), [
            'name' => 'Cata',
            'email' => 'cata@example.com',
            'password' => 'secreto',
            'password_confirmation' => 'secreto',
        ])->assertRedirect(route('grupos.index'));

        $this->assertAuthenticated();
        $this->get(route('grupos.index'))->assertOk()->assertSee('Mis Grupos');
    }

    public function test_login_con_clave_incorrecta_muestra_error(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))
            ->post(route('login'), ['email' => $user->email, 'password' => 'otra'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_sin_sesion_no_se_ven_los_grupos(): void
    {
        $this->get(route('grupos.index'))->assertRedirect(route('login'));
    }

    public function test_crear_grupo_con_integrantes_sin_cuenta(): void
    {
        $user = User::factory()->create(['name' => 'Tomás']);
        $grupo = $this->grupoCon($user, ['Ana', '', 'Benja']);

        $this->assertSame(['Tomás', 'Ana', 'Benja'], $grupo->miembros->pluck('nombre_alias')->all());
        $this->assertTrue($grupo->miembroDe($user)->es_admin);
        $this->get(route('invitar.show', $grupo))->assertOk()->assertSee($grupo->codigo_invitacion);
    }

    public function test_agregar_un_gasto_por_items_y_ver_los_saldos(): void
    {
        $user = User::factory()->create(['name' => 'Tomás']);
        $grupo = $this->grupoCon($user, ['Ana']);
        [$yo, $ana] = $grupo->miembros;

        $this->get(route('gastos.create', $grupo))->assertOk();
        $this->post(route('gastos.store', $grupo), [
            'comercio' => 'Supermercado',
            'pagado_por_miembro_id' => $yo->id,
            'propina' => 0,
            'items' => [
                ['nombre' => 'Pan', 'cantidad' => 2, 'precio_unitario' => 1500, 'miembros' => [$yo->id, $ana->id]],
                ['nombre' => 'Vino', 'cantidad' => 1, 'precio_unitario' => 7000, 'miembros' => [$ana->id]],
            ],
        ])->assertRedirect(route('grupos.show', $grupo));

        $this->assertSame([$yo->id => 8500, $ana->id => -8500], $grupo->fresh()->saldos());
        $this->get(route('grupos.show', $grupo))->assertOk()->assertSee('Supermercado')->assertSee('$10.000');
        $this->get(route('grupos.saldos', $grupo))->assertOk()->assertSee('Marcar recibido');
    }

    public function test_un_item_sin_personas_no_se_guarda(): void
    {
        $user = User::factory()->create();
        $grupo = $this->grupoCon($user);

        $this->post(route('gastos.store', $grupo), [
            'comercio' => 'Algo',
            'pagado_por_miembro_id' => $grupo->miembros[0]->id,
            'items' => [['nombre' => 'Pan', 'cantidad' => 1, 'precio_unitario' => 1000]],
        ])->assertSessionHasErrors('items.0.miembros');

        $this->assertSame(0, $grupo->gastos()->count());
    }

    public function test_marcar_pagado_y_deshacer(): void
    {
        $user = User::factory()->create();
        $grupo = $this->grupoCon($user, ['Ana']);
        [$yo, $ana] = $grupo->miembros;

        $this->post(route('pagos.store', $grupo), [
            'de_miembro_id' => $ana->id, 'para_miembro_id' => $yo->id, 'monto' => 3000,
        ])->assertRedirect(route('grupos.saldos', $grupo));
        $this->assertSame([$yo->id => -3000, $ana->id => 3000], $grupo->fresh()->saldos());

        $this->delete(route('pagos.destroy', [$grupo, $grupo->pagos()->first()]));
        $this->assertSame(0, $grupo->pagos()->count());
    }

    public function test_unirse_con_codigo_reclamando_un_integrante(): void
    {
        $admin = User::factory()->create();
        $grupo = $this->grupoCon($admin, ['Benja']);
        $benja = $grupo->miembros->firstWhere('nombre_alias', 'Benja');
        $nuevo = User::factory()->create(['name' => 'Benjamín']);

        // El código se puede escribir sin guion ni mayúsculas
        $this->actingAs($nuevo)
            ->post(route('unirse.buscar'), ['codigo' => strtolower(str_replace('-', '', $grupo->codigo_invitacion))])
            ->assertRedirect(route('unirse.show', $grupo->codigo_invitacion));
        $this->get(route('unirse.show', $grupo->codigo_invitacion))->assertOk()->assertSee('Benja');

        $this->post(route('solicitudes.store', $grupo), ['miembro' => $benja->id])->assertRedirect(route('grupos.index'));
        $this->get(route('grupos.show', $grupo))->assertForbidden();

        $solicitud = $grupo->solicitudes()->first();
        $this->actingAs($admin)->post(route('solicitudes.aceptar', [$grupo, $solicitud]))->assertRedirect();

        $this->assertSame('aprobada', $solicitud->fresh()->estado);
        $this->assertSame($nuevo->id, $benja->fresh()->user_id);
        $this->actingAs($nuevo)->get(route('grupos.show', $grupo))->assertOk();
    }

    public function test_un_codigo_que_no_existe_vuelve_con_error(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('unirse.show', 'ZZZ-ZZZ'))
            ->assertRedirect(route('unirse.index'))
            ->assertSessionHasErrors('codigo');
    }

    public function test_quien_no_es_del_grupo_no_puede_verlo_ni_tocarlo(): void
    {
        $grupo = $this->grupoCon(User::factory()->create());
        $intruso = User::factory()->create();

        $this->actingAs($intruso)->get(route('grupos.show', $grupo))->assertForbidden();
        $this->post(route('pagos.store', $grupo), [])->assertForbidden();
        $this->post(route('miembros.store', $grupo), ['nombre_alias' => 'X'])->assertForbidden();
    }
}
