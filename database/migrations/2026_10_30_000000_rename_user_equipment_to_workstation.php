<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename the "Equipos de Usuarios" type to "Estaciones de Trabajo"
     * (App\Enums\IpType::Workstation).
     */
    public function up(): void
    {
        DB::table('ip_addresses')
            ->where('type', 'user_equipment')
            ->update(['type' => 'workstation']);
    }

    public function down(): void
    {
        DB::table('ip_addresses')
            ->where('type', 'workstation')
            ->update(['type' => 'user_equipment']);
    }
};
