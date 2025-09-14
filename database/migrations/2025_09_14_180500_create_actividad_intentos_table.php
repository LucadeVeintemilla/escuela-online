<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividad_intentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actividad_id');
            $table->unsignedBigInteger('alumno_id');
            $table->string('tipo', 20); // upload | edit
            $table->timestamps();

            $table->index(['actividad_id', 'alumno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividad_intentos');
    }
};
