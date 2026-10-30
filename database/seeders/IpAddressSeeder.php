<?php

namespace Database\Seeders;

use App\Enums\IpType;
use App\Models\IpAddress;
use Illuminate\Database\Seeder;

class IpAddressSeeder extends Seeder
{
    /**
     * Demo inventory so the dashboard and charts have information to show.
     */
    public function run(): void
    {
        $records = [
            // Servidores
            ['ip' => '10.0.0.10', 'hostname' => 'srv-dc01.sigip.local', 'type' => IpType::Server, 'model' => 'Dell PowerEdge R740', 'name' => 'SRV-DC01', 'is_active' => true, 'last_pinged_at' => now()->subMinutes(12)],
            ['ip' => '10.0.0.11', 'hostname' => 'srv-dc02.sigip.local', 'type' => IpType::Server, 'model' => 'Dell PowerEdge R740', 'name' => 'SRV-DC02', 'is_active' => true, 'last_pinged_at' => now()->subMinutes(12)],
            ['ip' => '10.0.0.12', 'hostname' => 'srv-files.sigip.local', 'type' => IpType::Server, 'model' => 'HP ProLiant DL380 Gen10', 'name' => 'SRV-FILES', 'is_active' => true, 'last_pinged_at' => now()->subHours(2)],
            ['ip' => '10.0.0.13', 'hostname' => 'srv-backup.sigip.local', 'type' => IpType::Server, 'model' => 'Lenovo ThinkSystem SR650', 'name' => 'SRV-BACKUP', 'is_active' => false, 'last_pinged_at' => now()->subDay()],
            ['ip' => '10.0.0.14', 'hostname' => null, 'type' => IpType::Server, 'model' => 'Dell PowerEdge T350', 'name' => 'SRV-PRINT', 'is_active' => null, 'last_pinged_at' => null],
            ['ip' => '10.0.0.15', 'hostname' => 'srv-erp.sigip.local', 'type' => IpType::Server, 'model' => 'Dell PowerEdge R750', 'name' => 'SRV-ERP', 'is_active' => true, 'last_pinged_at' => now()->subMinutes(45)],

            // Máquinas virtuales
            ['ip' => '10.0.10.21', 'hostname' => 'vm-app01.sigip.local', 'type' => IpType::VirtualMachine, 'model' => 'VMware ESXi 8', 'name' => 'VM-APP01', 'is_active' => true, 'last_pinged_at' => now()->subMinutes(8)],
            ['ip' => '10.0.10.22', 'hostname' => 'vm-app02.sigip.local', 'type' => IpType::VirtualMachine, 'model' => 'VMware ESXi 8', 'name' => 'VM-APP02', 'is_active' => true, 'last_pinged_at' => now()->subMinutes(8)],
            ['ip' => '10.0.10.23', 'hostname' => 'vm-db01.sigip.local', 'type' => IpType::VirtualMachine, 'model' => 'VMware ESXi 8', 'name' => 'VM-DB01', 'is_active' => true, 'last_pinged_at' => now()->subMinutes(20)],
            ['ip' => '10.0.10.24', 'hostname' => 'vm-test01.sigip.local', 'type' => IpType::VirtualMachine, 'model' => 'Proxmox VE 8', 'name' => 'VM-TEST01', 'is_active' => false, 'last_pinged_at' => now()->subHours(5)],
            ['ip' => '10.0.10.25', 'hostname' => 'vm-reportes.sigip.local', 'type' => IpType::VirtualMachine, 'model' => 'Hyper-V Gen2', 'name' => 'VM-REPORTES', 'is_active' => true, 'last_pinged_at' => now()->subHours(3)],
            ['ip' => '10.0.10.26', 'hostname' => null, 'type' => IpType::VirtualMachine, 'model' => 'Proxmox VE 8', 'name' => 'VM-LAB01', 'is_active' => null, 'last_pinged_at' => null],
            ['ip' => '10.0.10.27', 'hostname' => 'vm-crm.sigip.local', 'type' => IpType::VirtualMachine, 'model' => 'VMware ESXi 8', 'name' => 'VM-CRM', 'is_active' => true, 'last_pinged_at' => now()->subMinutes(33)],

            // Red
            ['ip' => '10.0.0.1', 'hostname' => 'gw-core.sigip.local', 'type' => IpType::Network, 'model' => 'Fortinet FortiGate 100F', 'is_active' => true, 'last_pinged_at' => now()->subMinutes(1)],
            ['ip' => '10.0.0.2', 'hostname' => 'sw-core.sigip.local', 'type' => IpType::Network, 'model' => 'Cisco Catalyst 2960-X', 'is_active' => true, 'last_pinged_at' => now()->subMinutes(15)],
            ['ip' => '10.0.0.3', 'hostname' => 'sw-piso2.sigip.local', 'type' => IpType::Network, 'model' => 'Cisco Catalyst 2960', 'is_active' => true, 'last_pinged_at' => now()->subHours(1)],
            ['ip' => '10.0.0.4', 'hostname' => 'ap-recepcion.sigip.local', 'type' => IpType::Network, 'model' => 'Ubiquiti UniFi AP AC Pro', 'is_active' => false, 'last_pinged_at' => now()->subHours(4)],
            ['ip' => '10.0.0.5', 'hostname' => null, 'type' => IpType::Network, 'model' => 'TP-Link TL-SG1024', 'is_active' => null, 'last_pinged_at' => null],

            // Estaciones de trabajo
            ['ip' => '192.168.20.31', 'hostname' => 'pc-mlopez.sigip.local', 'type' => IpType::Workstation, 'model' => 'Dell OptiPlex 7090', 'office' => 'Oficina Central - Piso 2', 'staff' => 'María López', 'position' => 'Contabilidad', 'is_active' => true, 'last_pinged_at' => now()->subMinutes(25)],
            ['ip' => '192.168.20.32', 'hostname' => 'pc-jperez.sigip.local', 'type' => IpType::Workstation, 'model' => 'HP EliteDesk 800 G6', 'office' => 'Oficina Central - Piso 1', 'staff' => 'Juan Pérez', 'position' => 'Soporte Técnico', 'is_active' => true, 'last_pinged_at' => now()->subMinutes(25)],
            ['ip' => '192.168.20.33', 'hostname' => 'pc-aramirez.sigip.local', 'type' => IpType::Workstation, 'model' => 'Lenovo ThinkPad T14', 'office' => 'Sucursal Norte', 'staff' => 'Ana Ramírez', 'position' => 'Gerencia', 'is_active' => false, 'last_pinged_at' => now()->subHours(6)],
            ['ip' => '192.168.20.34', 'hostname' => null, 'type' => IpType::Workstation, 'model' => 'Dell Latitude 5420', 'office' => 'Sucursal Norte', 'staff' => 'Carlos Méndez', 'position' => 'Ventas', 'is_active' => true, 'last_pinged_at' => now()->subHours(2)],
            ['ip' => '192.168.20.35', 'hostname' => 'pc-lsuarez.sigip.local', 'type' => IpType::Workstation, 'model' => 'Dell OptiPlex 3080', 'office' => 'Almacén', 'staff' => 'Laura Suárez', 'position' => 'Logística', 'is_active' => null, 'last_pinged_at' => null],
            ['ip' => '192.168.20.36', 'hostname' => 'pc-rgomez.sigip.local', 'type' => IpType::Workstation, 'model' => 'HP ProDesk 400 G7', 'office' => 'Oficina Central - Piso 3', 'staff' => 'Roberto Gómez', 'position' => 'Recursos Humanos', 'is_active' => true, 'last_pinged_at' => now()->subHours(1)],
            ['ip' => '192.168.20.37', 'hostname' => 'pc-ctorres.sigip.local', 'type' => IpType::Workstation, 'model' => 'Lenovo ThinkCentre M70q', 'office' => 'Oficina Central - Piso 2', 'staff' => 'Carla Torres', 'position' => 'Administración', 'is_active' => false, 'last_pinged_at' => now()->subDay()],
            ['ip' => '192.168.20.38', 'hostname' => null, 'type' => IpType::Workstation, 'model' => 'Dell Vostro 3510', 'office' => 'Almacén', 'staff' => 'Diego Herrera', 'position' => 'Operaciones', 'is_active' => null, 'last_pinged_at' => null],

            // Estaciones de trabajo con IP dinámica (DHCP)
            ['ip' => null, 'hostname' => 'pc-ortiz.sigip.local', 'type' => IpType::Workstation, 'is_dhcp' => true, 'model' => 'HP EliteDesk 800 G8', 'office' => 'Recepción', 'staff' => 'Paula Ortiz', 'position' => 'Recepción', 'is_active' => null, 'last_pinged_at' => null],
            ['ip' => null, 'hostname' => 'laptop-visitas.sigip.local', 'type' => IpType::Workstation, 'is_dhcp' => true, 'model' => 'Lenovo ThinkPad E14', 'office' => 'Sala de reuniones', 'staff' => 'Equipo de visitas', 'position' => 'Invitados', 'is_active' => null, 'last_pinged_at' => null],
        ];

        foreach ($records as $record) {
            $key = $record['ip'] !== null
                ? ['ip' => $record['ip']]
                : ['hostname' => $record['hostname']];

            IpAddress::updateOrCreate($key, $record);
        }
    }
}
