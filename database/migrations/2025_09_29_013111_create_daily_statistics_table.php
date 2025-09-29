<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_statistics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique(); // Fecha de las estadísticas
            $table->integer('total_users'); // Total de usuarios
            $table->integer('active_users'); // Usuarios con tokens activos
            $table->integer('new_registrations'); // Registros del día
            $table->integer('total_countries'); // Total de países sincronizados
            $table->integer('admin_users'); // Total de administradores
            $table->decimal('verification_rate', 5, 2); // Porcentaje de verificación
            $table->json('users_by_region')->nullable(); // Usuarios por región
            $table->json('registrations_by_hour')->nullable(); // Registros por hora del día
            $table->timestamps();

            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_statistics');
    }
};
