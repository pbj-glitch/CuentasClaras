<?php

namespace App\Models;

use App\Services\Liquidacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

class Gasto extends Model
{
    /** Así el grupo sube al inicio de la lista cuando tiene movimiento. */
    protected $touches = ['grupo'];

    protected $fillable = ['grupo_id', 'pagado_por_miembro_id', 'comercio', 'subtotal', 'propina', 'total'];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function pagador(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'pagado_por_miembro_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GastoItem::class);
    }

    /** Lo que le toca a cada integrante, ítem por ítem (con su parte de la propina). */
    public function partes(): HasManyThrough
    {
        return $this->hasManyThrough(GastoParte::class, GastoItem::class);
    }

    public function parteDe(?Miembro $miembro): int
    {
        return $miembro ? (int) $this->partes->where('miembro_id', $miembro->id)->sum('monto_asignado') : 0;
    }

    /**
     * Guarda un gasto con sus ítems y lo que le toca a cada uno.
     *
     * @param  array<int, array{nombre: string, cantidad?: int, precio_unitario: int, miembros: array<int>}>  $items
     */
    public static function registrar(Grupo $grupo, Miembro $pagador, ?string $comercio, array $items, int $propina = 0): self
    {
        $items = array_map(fn ($item) => $item + ['cantidad' => 1], $items);
        foreach ($items as &$item) {
            $item['subtotal'] = $item['cantidad'] * $item['precio_unitario'];
        }
        unset($item);

        $reparto = Liquidacion::repartir($items, $propina);

        return DB::transaction(function () use ($grupo, $pagador, $comercio, $items, $propina, $reparto) {
            $gasto = $grupo->gastos()->create([
                'pagado_por_miembro_id' => $pagador->id,
                'comercio' => $comercio,
                'subtotal' => $reparto['total'] - $propina,
                'propina' => $propina,
                'total' => $reparto['total'],
            ]);

            foreach ($items as $i => $item) {
                $gastoItem = $gasto->items()->create([
                    'nombre' => $item['nombre'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                ]);

                foreach ($reparto['partes'][$i] ?? [] as $miembroId => $monto) {
                    $gastoItem->partes()->create(['miembro_id' => $miembroId, 'monto_asignado' => $monto]);
                }
            }

            return $gasto;
        });
    }
}
