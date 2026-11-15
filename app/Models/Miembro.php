<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Miembro extends Model
{
    protected $fillable = ['grupo_id', 'user_id', 'nombre_alias', 'es_admin'];

    protected function casts(): array
    {
        return ['es_admin' => 'boolean'];
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gastosPagados(): HasMany
    {
        return $this->hasMany(Gasto::class, 'pagado_por_miembro_id');
    }

    public function partes(): HasMany
    {
        return $this->hasMany(GastoParte::class);
    }

    public function inicial(): string
    {
        return mb_strtoupper(mb_substr($this->nombre_alias, 0, 1));
    }

    public function tieneCuenta(): bool
    {
        return $this->user_id !== null;
    }
}
