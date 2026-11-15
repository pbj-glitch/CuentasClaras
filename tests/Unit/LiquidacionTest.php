<?php

namespace Tests\Unit;

use App\Services\Liquidacion;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LiquidacionTest extends TestCase
{
    public function test_un_item_compartido_se_divide_en_partes_iguales(): void
    {
        $reparto = Liquidacion::repartir([['subtotal' => 30000, 'miembros' => [1, 2, 3]]]);

        $this->assertSame(30000, $reparto['total']);
        $this->assertSame([0 => [1 => 10000, 2 => 10000, 3 => 10000]], $reparto['partes']);
    }

    public function test_la_propina_es_proporcional_a_lo_consumido(): void
    {
        $reparto = Liquidacion::repartir([
            ['subtotal' => 20000, 'miembros' => [1]],
            ['subtotal' => 10000, 'miembros' => [2]],
        ], 3000);

        $this->assertSame(33000, $reparto['total']);
        $this->assertSame([1 => 22000, 2 => 11000], Liquidacion::porMiembro($reparto['partes']));
    }

    public function test_las_partes_suman_exacto_el_total_aunque_no_sea_divisible(): void
    {
        $reparto = Liquidacion::repartir([['subtotal' => 10000, 'miembros' => [1, 2, 3]]], 1000);

        $this->assertSame(11000, $reparto['total']);
        $this->assertEqualsCanonicalizing([3667, 3667, 3666], array_values($reparto['partes'][0]));
    }

    public function test_una_boleta_con_varios_items_cuadra_al_peso(): void
    {
        $todos = [1, 2, 3, 4, 5];

        $reparto = Liquidacion::repartir([
            ['subtotal' => 25800, 'miembros' => $todos],
            ['subtotal' => 13900, 'miembros' => [4]],
            ['subtotal' => 19600, 'miembros' => [1, 5]],
            ['subtotal' => 7900, 'miembros' => $todos],
            ['subtotal' => 11700, 'miembros' => [2, 3, 4]],
            ['subtotal' => 6900, 'miembros' => [2]],
        ], 8580);

        $porMiembro = Liquidacion::porMiembro($reparto['partes']);
        $this->assertSame(94380, $reparto['total']);
        $this->assertSame(94380, array_sum($porMiembro));
        // (25800/5 + 19600/2 + 7900/5) * 1,1 = 18194,4
        $this->assertEqualsWithDelta(18194, $porMiembro[1], 1);
    }

    public function test_un_item_sin_nadie_asignado_es_un_error(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Liquidacion::repartir([['subtotal' => 5000, 'miembros' => []]]);
    }

    public function test_las_transferencias_dejan_a_todos_en_cero(): void
    {
        $saldos = [1 => 27800, 2 => 14200, 3 => 31500, 4 => -38700, 5 => -34800];

        $transferencias = Liquidacion::transferencias($saldos);

        foreach ($transferencias as $t) {
            $saldos[$t['de']] += $t['monto'];
            $saldos[$t['a']] -= $t['monto'];
        }
        $this->assertSame([0, 0, 0, 0, 0], array_values($saldos));
        $this->assertCount(4, $transferencias);
        $this->assertSame(['de' => 4, 'a' => 3, 'monto' => 31500], $transferencias[0]);
    }

    public function test_sin_deudas_no_hay_transferencias(): void
    {
        $this->assertSame([], Liquidacion::transferencias([1 => 0, 2 => 0]));
    }
}
