<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        // Fecha	¿Que se encontró?	¿En donde?	Actividad relevante	Tipo de actividad	¿Es actividad anómala?
        Schema::create('event_records', function (Blueprint $table) {
            $table->id();
            $table->timestamp('fecha');
            $table->string('que_se_encontro');
            $table->string('en_donde');
            $table->string('actividad_relevante');
            $table->string('tipo_de_actividad');
            $table->boolean('es_actividad_anomala');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_records');
    }
};
