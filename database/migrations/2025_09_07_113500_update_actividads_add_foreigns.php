<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('actividads')) {
            Schema::table('actividads', function (Blueprint $table) {
                if (!Schema::hasColumn('actividads', 'docente_id')) {
                    $table->foreignId('docente_id')->nullable()->after('usuario_id')->constrained('docentes')->nullOnDelete();
                }
                if (!Schema::hasColumn('actividads', 'aula_id')) {
                    $table->foreignId('aula_id')->nullable()->after('docente_id')->constrained('aulas')->nullOnDelete();
                }
                if (!Schema::hasColumn('actividads', 'asignatura_grado_id')) {
                    $table->foreignId('asignatura_grado_id')->nullable()->after('aula_id')->constrained('asignatura_grados')->cascadeOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('actividads')) {
            Schema::table('actividads', function (Blueprint $table) {
                if (Schema::hasColumn('actividads', 'asignatura_grado_id')) {
                    $table->dropForeign(['asignatura_grado_id']);
                    $table->dropColumn('asignatura_grado_id');
                }
                if (Schema::hasColumn('actividads', 'aula_id')) {
                    $table->dropForeign(['aula_id']);
                    $table->dropColumn('aula_id');
                }
                if (Schema::hasColumn('actividads', 'docente_id')) {
                    $table->dropForeign(['docente_id']);
                    $table->dropColumn('docente_id');
                }
            });
        }
    }
};
