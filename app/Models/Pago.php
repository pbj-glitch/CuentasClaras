<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $fillable = ['grupo_id', 'de_miembro_id', 'para_miembro_id', 'monto'];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function deMiembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'de_miembro_id');
    }

    public function paraMiembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'para_miembro_id');
    }
}
