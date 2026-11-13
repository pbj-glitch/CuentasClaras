<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitacion extends Model
{
    protected $fillable = ['grupo_id', 'token', 'expira_en'];

    protected $casts = [
        'expira_en' => 'datetime',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }
}
