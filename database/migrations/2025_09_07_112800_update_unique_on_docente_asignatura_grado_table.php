<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Asegurar columna aula_id existe
        Schema::table('docente_asignatura_grado', function (Blueprint $table) {
            if (!Schema::hasColumn('docente_asignatura_grado', 'aula_id')) {
                $table->foreignId('aula_id')->nullable()->after('asignatura_grado_id')->constrained('aulas')->nullOnDelete();
            }
        });

        // MySQL no permite eliminar un índice si está usado por una FK. Hay que soltar FKs primero.
        Schema::table('docente_asignatura_grado', function (Blueprint $table) {
            // Drop FKs temporariamente
            try { $table->dropForeign('docente_asignatura_grado_docente_id_foreign'); } catch (\Throwable $e) {}
            try { $table->dropForeign('docente_asignatura_grado_asignatura_grado_id_foreign'); } catch (\Throwable $e) {}
            try { $table->dropForeign('docente_asignatura_grado_aula_id_foreign'); } catch (\Throwable $e) {}

            // Drop unique (docente_id, asignatura_grado_id)
            try { $table->dropUnique('docente_asignatura_grado_docente_id_asignatura_grado_id_unique'); } catch (\Throwable $e) {}
        });

        Schema::table('docente_asignatura_grado', function (Blueprint $table) {
            // Re-crear FKs
            $table->foreign('docente_id')->references('id')->on('docentes')->cascadeOnDelete();
            $table->foreign('asignatura_grado_id')->references('id')->on('asignatura_grados')->cascadeOnDelete();
            $table->foreign('aula_id')->references('id')->on('aulas')->nullOnDelete();

            // Crear unique triple
            $table->unique(['docente_id', 'asignatura_grado_id', 'aula_id'], 'dag_docente_asig_aula_unique');
        });
    }

    public function down(): void
    {
        Schema::table('docente_asignatura_grado', function (Blueprint $table) {
            // Soltar FKs para eliminar unique
            try { $table->dropForeign('docente_asignatura_grado_docente_id_foreign'); } catch (\Throwable $e) {}
            try { $table->dropForeign('docente_asignatura_grado_asignatura_grado_id_foreign'); } catch (\Throwable $e) {}
            try { $table->dropForeign('docente_asignatura_grado_aula_id_foreign'); } catch (\Throwable $e) {}

            try { $table->dropUnique('dag_docente_asig_aula_unique'); } catch (\Throwable $e) {}

            // Restaurar unique doble
            $table->unique(['docente_id', 'asignatura_grado_id'], 'docente_asignatura_grado_docente_id_asignatura_grado_id_unique');

            // Re-crear FKs mínimos
            $table->foreign('docente_id')->references('id')->on('docentes')->cascadeOnDelete();
            $table->foreign('asignatura_grado_id')->references('id')->on('asignatura_grados')->cascadeOnDelete();
            $table->foreign('aula_id')->references('id')->on('aulas')->nullOnDelete();
        });
    }
};
