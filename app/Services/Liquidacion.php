<?php

namespace App\Services;

use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Las cuentas del grupo: cuánto le toca a cada uno de un gasto,
 * el saldo de cada integrante y quién le transfiere a quién para quedar a mano.
 * Todos los montos son enteros en pesos.
 */
class Liquidacion
{
    /**
     * Reparte un gasto entre los integrantes según los ítems que consumió cada uno.
     * Un ítem compartido se divide en partes iguales. La propina se cobra en
     * proporción a lo consumido. Los pesos que sobran del redondeo se asignan
     * a las partes con la fracción más alta, así todo suma exacto el total.
     *
     * @param  array<int, array{subtotal: int, miembros: array<int>}>  $items
     * @return array{total: int, partes: array<int, array<int, int>>} partes: índice del ítem => [miembro_id => monto]
     */
    public static function repartir(array $items, int $propina = 0): array
    {
        $subtotal = array_sum(array_column($items, 'subtotal'));
        $total = $subtotal + $propina;
        $factor = $subtotal > 0 ? $total / $subtotal : 0;

        $exacto = []; // [índice del ítem, miembro_id, monto con decimales]
        foreach ($items as $i => $item) {
            $miembros = array_values(array_unique($item['miembros']));
            if ($miembros === []) {
                throw new InvalidArgumentException("El ítem {$i} no tiene a nadie asignado.");
            }
            foreach ($miembros as $id) {
                $exacto[] = [$i, $id, $item['subtotal'] / count($miembros) * $factor];
            }
        }

        $partes = [];
        foreach ($exacto as [$i, $id, $monto]) {
            $partes[$i][$id] = (int) floor($monto);
        }
        $sobrante = $total - array_sum(array_map('array_sum', $partes));

        // Ordenar por la parte decimal, de mayor a menor, y repartir el sobrante de a $1
        usort($exacto, fn ($x, $y) => ($y[2] - floor($y[2])) <=> ($x[2] - floor($x[2])));
        foreach ($exacto as [$i, $id]) {
            if ($sobrante <= 0) {
                break;
            }
            $partes[$i][$id]++;
            $sobrante--;
        }

        return ['total' => $total, 'partes' => $partes];
    }

    /**
     * Suma por integrante lo que le toca en cada ítem.
     *
     * @param  array<int, array<int, int>>  $partes  como las devuelve repartir()
     * @return array<int, int> miembro_id => monto
     */
    public static function porMiembro(array $partes): array
    {
        $suma = [];
        foreach ($partes as $porItem) {
            foreach ($porItem as $id => $monto) {
                $suma[$id] = ($suma[$id] ?? 0) + $monto;
            }
        }

        return $suma;
    }

    /**
     * @return array<int, int> miembro_id => saldo (positivo = le deben)
     */
    public static function saldos(Collection $miembros, Collection $gastos, Collection $pagos): array
    {
        $saldos = $miembros->mapWithKeys(fn ($m) => [$m->id => 0])->all();

        foreach ($gastos as $gasto) {
            $saldos[$gasto->pagado_por_miembro_id] = ($saldos[$gasto->pagado_por_miembro_id] ?? 0) + $gasto->total;
            foreach ($gasto->partes as $parte) {
                $saldos[$parte->miembro_id] = ($saldos[$parte->miembro_id] ?? 0) - $parte->monto_asignado;
            }
        }

        // Quien devuelve plata sube su saldo; quien la recibe, lo baja
        foreach ($pagos as $pago) {
            $saldos[$pago->de_miembro_id] = ($saldos[$pago->de_miembro_id] ?? 0) + $pago->monto;
            $saldos[$pago->para_miembro_id] = ($saldos[$pago->para_miembro_id] ?? 0) - $pago->monto;
        }

        return $saldos;
    }

    /**
     * Propone pocas transferencias para dejar todos los saldos en cero:
     * en cada paso, el que más debe le paga al que más le deben.
     *
     * @param  array<int, int>  $saldos
     * @return array<int, array{de: int, a: int, monto: int}>
     */
    public static function transferencias(array $saldos): array
    {
        $deudores = array_filter($saldos, fn ($s) => $s < 0);
        $acreedores = array_filter($saldos, fn ($s) => $s > 0);
        $resultado = [];

        while ($deudores && $acreedores) {
            asort($deudores);   // el más negativo primero
            arsort($acreedores); // el más positivo primero

            $de = array_key_first($deudores);
            $a = array_key_first($acreedores);
            $monto = min(-$deudores[$de], $acreedores[$a]);

            $resultado[] = ['de' => $de, 'a' => $a, 'monto' => $monto];

            $deudores[$de] += $monto;
            $acreedores[$a] -= $monto;
            if ($deudores[$de] === 0) {
                unset($deudores[$de]);
            }
            if ($acreedores[$a] === 0) {
                unset($acreedores[$a]);
            }
        }

        return $resultado;
    }
}
