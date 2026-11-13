<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Solicitud extends Model
{
    protected $fillable = ['grupo_id', 'user_id', 'miembro_reclamado_id', 'estado'];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function miembroReclamado(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'miembro_reclamado_id');
    }
}
