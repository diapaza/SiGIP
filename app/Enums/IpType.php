<?php

namespace App\Enums;

enum IpType: string
{
    case Server = 'server';
    case VirtualMachine = 'virtual_machine';
    case Network = 'network';
    case UserEquipment = 'user_equipment';

    /**
     * Plural label shown in tabs and navigation.
     */
    public function label(): string
    {
        return match ($this) {
            self::Server => 'Servidores',
            self::VirtualMachine => 'Máquinas Virtuales',
            self::Network => 'Red',
            self::UserEquipment => 'Equipos de Usuarios',
        };
    }

    /**
     * Singular label used in forms and messages.
     */
    public function singularLabel(): string
    {
        return match ($this) {
            self::Server => 'Servidor',
            self::VirtualMachine => 'Máquina Virtual',
            self::Network => 'Equipo de Red',
            self::UserEquipment => 'Equipo de Usuario',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Server => 'server-stack',
            self::VirtualMachine => 'cpu-chip',
            self::Network => 'wifi',
            self::UserEquipment => 'computer-desktop',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Server => 'indigo',
            self::VirtualMachine => 'violet',
            self::Network => 'sky',
            self::UserEquipment => 'amber',
        };
    }

    /**
     * Hex color used by the dashboard charts.
     */
    public function chartColor(): string
    {
        return match ($this) {
            self::Server => '#6366f1',
            self::VirtualMachine => '#8b5cf6',
            self::Network => '#0ea5e9',
            self::UserEquipment => '#f59e0b',
        };
    }

    public function requiresName(): bool
    {
        return in_array($this, [self::Server, self::VirtualMachine], true);
    }

    public function requiresUserDetails(): bool
    {
        return $this === self::UserEquipment;
    }

    /**
     * Human readable list of requirements, used as a hint in the UI.
     */
    public function requirementsLabel(): string
    {
        return match ($this) {
            self::Server, self::VirtualMachine => 'IP, Hostname (opcional), Equipo o Modelo y Nombre.',
            self::Network => 'IP, Hostname (opcional) y Equipo o Modelo.',
            self::UserEquipment => 'IP, Hostname (opcional), Oficina, Equipo o Modelo, Personal y Puesto.',
        };
    }
}
