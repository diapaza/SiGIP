<?php

namespace Database\Factories;

use App\Enums\IpType;
use App\Models\IpAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IpAddress>
 */
class IpAddressFactory extends Factory
{
    protected $model = IpAddress::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ip' => fake()->unique()->localIpv4(),
            'hostname' => null,
            'type' => IpType::Server,
            'model' => fake()->randomElement(['Dell PowerEdge R740', 'HP ProLiant DL380', 'Lenovo ThinkSystem SR650']),
            'name' => 'SRV-'.strtoupper(fake()->bothify('???##')),
            'office' => null,
            'staff' => null,
            'position' => null,
            'is_active' => fake()->randomElement([true, true, true, false, null]),
            'last_pinged_at' => fake()->optional(0.7)->dateTimeBetween('-2 days'),
        ];
    }

    public function server(): static
    {
        return $this->state(fn () => [
            'type' => IpType::Server,
            'name' => 'SRV-'.strtoupper(fake()->bothify('???##')),
        ]);
    }

    public function virtualMachine(): static
    {
        return $this->state(fn () => [
            'type' => IpType::VirtualMachine,
            'name' => 'VM-'.strtoupper(fake()->bothify('???##')),
            'model' => fake()->randomElement(['VMware ESXi 8', 'Hyper-V Gen2', 'Proxmox VE 8']),
        ]);
    }

    public function network(): static
    {
        return $this->state(fn () => [
            'type' => IpType::Network,
            'model' => fake()->randomElement(['Cisco Catalyst 2960', 'Fortinet FortiGate 60F', 'Ubiquiti UniFi AP']),
            'name' => null,
        ]);
    }

    public function userEquipment(): static
    {
        return $this->state(fn () => [
            'type' => IpType::UserEquipment,
            'model' => fake()->randomElement(['Dell OptiPlex 7090', 'HP EliteDesk 800 G6', 'Lenovo ThinkPad T14']),
            'name' => null,
            'office' => fake()->randomElement(['Oficina Central', 'Sucursal Norte', 'Almacén']),
            'staff' => fake()->name(),
            'position' => fake()->randomElement(['Analista', 'Contabilidad', 'Soporte Técnico', 'Gerencia']),
        ]);
    }
}
