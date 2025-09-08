<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contenidos', function (Blueprint $table) {
            if (!Schema::hasColumn('contenidos', 'actividad_id')) {
                $table->unsignedBigInteger('actividad_id')->nullable()->after('usuario_id');
            }
            if (!Schema::hasColumn('contenidos', 'alumno_id')) {
                $table->unsignedBigInteger('alumno_id')->nullable()->after('actividad_id');
            }
        });

        Schema::table('contenidos', function (Blueprint $table) {
            if (Schema::hasColumn('contenidos', 'actividad_id')) {
                $table->foreign('actividad_id')->references('id')->on('actividads')->nullOnDelete();
            }
            if (Schema::hasColumn('contenidos', 'alumno_id')) {
                $table->foreign('alumno_id')->references('id')->on('alumnos')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contenidos', function (Blueprint $table) {
            if (Schema::hasColumn('contenidos', 'actividad_id')) {
                $table->dropForeign(['actividad_id']);
                $table->dropColumn('actividad_id');
            }
            if (Schema::hasColumn('contenidos', 'alumno_id')) {
                $table->dropForeign(['alumno_id']);
                $table->dropColumn('alumno_id');
            }
        });
    }
};
