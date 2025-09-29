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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 2)->unique(); // ISO country code
            $table->string('capital')->nullable();
            $table->bigInteger('population')->default(0);
            $table->string('region')->nullable();
            $table->string('subregion')->nullable();
            $table->string('flag_url')->nullable();
            $table->json('currencies')->nullable();
            $table->json('languages')->nullable();
            $table->timestamps();

            $table->index('region');
            $table->index('population');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
