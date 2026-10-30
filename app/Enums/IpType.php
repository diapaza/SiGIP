<?php

namespace App\Enums;

enum IpType: string
{
    case Server = 'server';
    case VirtualMachine = 'virtual_machine';
    case Network = 'network';
    case Workstation = 'workstation';

    /**
     * Plural label shown in tabs and navigation.
     */
    public function label(): string
    {
        return match ($this) {
            self::Server => 'Servidores',
            self::VirtualMachine => 'Máquinas Virtuales',
            self::Network => 'Red',
            self::Workstation => 'Estaciones de Trabajo',
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
            self::Workstation => 'Estación de Trabajo',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Server => 'server-stack',
            self::VirtualMachine => 'cpu-chip',
            self::Network => 'wifi',
            self::Workstation => 'computer-desktop',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Server => 'indigo',
            self::VirtualMachine => 'violet',
            self::Network => 'sky',
            self::Workstation => 'amber',
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
            self::Workstation => '#f59e0b',
        };
    }

    public function requiresName(): bool
    {
        return in_array($this, [self::Server, self::VirtualMachine], true);
    }

    public function requiresWorkstationDetails(): bool
    {
        return $this === self::Workstation;
    }
}
