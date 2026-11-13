<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GastoItem extends Model
{
    protected $fillable = ['gasto_id', 'nombre', 'cantidad', 'precio_unitario', 'subtotal'];

    public function gasto(): BelongsTo
    {
        return $this->belongsTo(Gasto::class);
    }

    public function partes(): HasMany
    {
        return $this->hasMany(GastoParte::class);
    }
}
