<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GastoParte extends Model
{
    protected $fillable = ['gasto_item_id', 'miembro_id', 'monto_asignado'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(GastoItem::class, 'gasto_item_id');
    }

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class);
    }
}
