<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('role')->default('user')->index()->after('password');
            $table->boolean('is_active')->default(true)->after('role');
        });

        // Split the existing "name" into first and last name (best effort).
        DB::table('users')->orderBy('id')->get()->each(function (object $user) {
            $parts = preg_split('/\s+/', trim((string) $user->name)) ?: [];
            $firstName = array_shift($parts) ?: 'Usuario';
            $lastName = implode(' ', $parts) ?: 'Sin apellido';

            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
        });

        // Guarantee at least one administrator so the module is reachable.
        $firstUser = DB::table('users')->orderBy('id')->first();

        if ($firstUser !== null) {
            DB::table('users')->where('id', $firstUser->id)->update(['role' => 'admin']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'role', 'is_active']);
        });
    }
};
