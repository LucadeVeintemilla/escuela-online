<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividads', function (Blueprint $table) {
            $table->unsignedInteger('max_intentos')->nullable()->after('asignatura_grado_id');
        });
    }

    public function down(): void
    {
        Schema::table('actividads', function (Blueprint $table) {
            $table->dropColumn('max_intentos');
        });
    }
};
