<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::User => 'Usuario',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Admin => 'indigo',
            self::User => 'zinc',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Admin => 'shield-check',
            self::User => 'user',
        };
    }
}
