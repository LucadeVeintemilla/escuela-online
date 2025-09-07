<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Docente;
use App\Models\User;
use App\Models\AsignaturaGrado;

class DocenteAssignCursosSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el docente demo por user_id o por correo
        $user = User::where('email', 'docente@example.com')->first();
        $docente = null;
        if ($user) {
            $docente = Docente::where('user_id', $user->id)->first();
        }
        if (!$docente) {
            $docente = Docente::where('correo', 'docente@example.com')->first();
        }

        if (!$docente) {
            $this->command?->warn('No se encontró Docente para docente@example.com');
            return;
        }
        if (!$docente->aula) {
            $this->command?->warn('El Docente no tiene Aula asignada. Ejecuta DocenteDemoSetupSeeder primero.');
            return;
        }

        // Obtener todas las asignaturas del grado del aula del docente
        $asignaturas = AsignaturaGrado::where('grado_id', $docente->aula->grado_id)->pluck('id')->all();
        if (empty($asignaturas)) {
            $this->command?->warn('No hay asignatura_grados para el grado del aula del docente. Ejecuta los seeders de Asignatura/AsignaturaGrado.');
            return;
        }

        // Asignar todas con aula_id en el pivote
        $payload = [];
        foreach ($asignaturas as $agId) {
            $payload[$agId] = ['aula_id' => $docente->aula_id];
        }
        $docente->asignaturasGrado()->syncWithoutDetaching($payload);

        $this->command?->info('Asignaturas del grado asignadas al Docente correctamente.');
    }
}
