<?php

namespace App\Models;

use App\Enums\IpType;
use Database\Factories\IpAddressFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpAddress extends Model
{
    /** @use HasFactory<IpAddressFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * The shared columns (ip, hostname) are always present. The remaining
     * columns are optional and only used by the types that require them.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ip',
        'hostname',
        'type',
        'model',
        'name',
        'office',
        'staff',
        'position',
        'is_active',
        'last_pinged_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => IpType::class,
            'is_active' => 'boolean',
            'last_pinged_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeOfType(Builder $query, IpType|string $type): void
    {
        $query->where('type', $type instanceof IpType ? $type->value : $type);
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeInactive(Builder $query): void
    {
        $query->where('is_active', false);
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeUnchecked(Builder $query): void
    {
        $query->whereNull('is_active');
    }

    public function statusLabel(): string
    {
        return match ($this->is_active) {
            true => 'Activo',
            false => 'Inactivo',
            null => 'Sin verificar',
        };
    }

    public function statusColor(): string
    {
        return match ($this->is_active) {
            true => 'emerald',
            false => 'red',
            null => 'zinc',
        };
    }
}
