<?php

namespace App\Models;

use App\Services\Liquidacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Grupo extends Model
{
    protected $fillable = ['nombre', 'codigo_invitacion', 'creador_id'];

    protected static function booted(): void
    {
        static::creating(function (Grupo $grupo) {
            $grupo->codigo_invitacion ??= self::generarCodigo();
        });
    }

    /** Código tipo "7K4-M2Q", sin letras que se confunden (0/O, 1/I). */
    public static function generarCodigo(): string
    {
        $letras = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $codigo = '';
            for ($i = 0; $i < 6; $i++) {
                $codigo .= $letras[random_int(0, strlen($letras) - 1)];
            }
            $codigo = substr($codigo, 0, 3).'-'.substr($codigo, 3);
        } while (self::where('codigo_invitacion', $codigo)->exists());

        return $codigo;
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    public function miembros(): HasMany
    {
        return $this->hasMany(Miembro::class)->orderBy('id');
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class)->latest()->latest('id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class)->latest();
    }

    public function invitaciones(): HasMany
    {
        return $this->hasMany(Invitacion::class);
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }

    /**
     * En rutas como /grupos/{grupo}/solicitudes/{solicitud}, Laravel busca la relación
     * pasando el parámetro a plural en inglés ("solicituds"). Aquí le damos el nombre real.
     */
    protected function childRouteBindingRelationshipName($childType): string
    {
        return [
            'solicitud' => 'solicitudes',
            'invitacion' => 'invitaciones',
        ][$childType] ?? parent::childRouteBindingRelationshipName($childType);
    }

    /** Agrega un integrante; puede no tener cuenta (alguien que todavía no se une a la app). */
    public function agregarMiembro(string $alias, ?User $user = null, bool $admin = false): Miembro
    {
        return $this->miembros()->create([
            'nombre_alias' => $alias,
            'user_id' => $user?->id,
            'es_admin' => $admin,
        ]);
    }

    /**
     * Une a un usuario al grupo. Si dice ser un integrante que aún no tiene cuenta
     * (ej: "Benja", que otro anotó en los gastos), se queda con ese integrante y su saldo.
     */
    public function incorporar(User $user, ?Miembro $existente = null): Miembro
    {
        $yaEsta = $this->miembros()->where('user_id', $user->id)->first();
        if ($yaEsta) {
            return $yaEsta;
        }

        if ($existente && $existente->grupo_id === $this->id && ! $existente->tieneCuenta()) {
            $existente->update(['user_id' => $user->id]);

            return $existente;
        }

        return $this->agregarMiembro($user->name, $user);
    }

    public function miembroDe(User $user): ?Miembro
    {
        return $this->miembros->firstWhere('user_id', $user->id);
    }

    public function total(): int
    {
        return (int) $this->gastos->sum('total');
    }

    /**
     * Saldo de cada integrante: lo que pagó menos lo que le correspondía,
     * ajustado por los reembolsos ya hechos. Positivo = le deben.
     *
     * @return array<int, int> miembro_id => saldo
     */
    public function saldos(): array
    {
        $this->loadMissing('miembros', 'gastos.partes', 'pagos');

        return Liquidacion::saldos($this->miembros, $this->gastos, $this->pagos);
    }

    /** @return array<int, array{de: int, a: int, monto: int}> */
    public function transferencias(): array
    {
        return Liquidacion::transferencias($this->saldos());
    }
}
