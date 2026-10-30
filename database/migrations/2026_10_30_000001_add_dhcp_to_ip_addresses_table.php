<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Workstations may obtain their address automatically (DHCP), so the
     * IP is no longer mandatory for every record.
     */
    public function up(): void
    {
        Schema::table('ip_addresses', function (Blueprint $table) {
            $table->boolean('is_dhcp')->default(false)->after('hostname');
            $table->string('ip')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ip_addresses', function (Blueprint $table) {
            $table->dropColumn('is_dhcp');
            $table->string('ip')->nullable(false)->change();
        });
    }
};
