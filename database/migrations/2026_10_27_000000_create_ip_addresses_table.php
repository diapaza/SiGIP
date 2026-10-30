<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_addresses', function (Blueprint $table) {
            $table->id();

            // Shared requirements for every type.
            $table->string('ip')->unique();
            $table->string('hostname')->nullable();

            // Type discriminator (App\Enums\IpType).
            $table->string('type')->index();

            // Type specific requirements. They stay null for types that don't use them.
            $table->string('model')->nullable();     // Equipo o Modelo
            $table->string('name')->nullable();      // Nombre (servidores y máquinas virtuales)
            $table->string('office')->nullable();    // Oficina (equipos de usuarios)
            $table->string('staff')->nullable();     // Personal (equipos de usuarios)
            $table->string('position')->nullable();  // Puesto (equipos de usuarios)

            // Result of the last ping.
            $table->boolean('is_active')->nullable();
            $table->timestamp('last_pinged_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_addresses');
    }
};
