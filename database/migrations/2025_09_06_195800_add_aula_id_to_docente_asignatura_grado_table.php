<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('docente_asignatura_grado') && !Schema::hasColumn('docente_asignatura_grado', 'aula_id')) {
            Schema::table('docente_asignatura_grado', function (Blueprint $table) {
                $table->foreignId('aula_id')->nullable()->after('asignatura_grado_id')->constrained('aulas')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('docente_asignatura_grado') && Schema::hasColumn('docente_asignatura_grado', 'aula_id')) {
            Schema::table('docente_asignatura_grado', function (Blueprint $table) {
                $table->dropForeign(['aula_id']);
                $table->dropColumn('aula_id');
            });
        }
    }
};
