<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Aula;
use App\Models\Alumno;
use App\Models\Docente;
use App\Models\Genero;
use App\Models\User;

class DocenteDemoSetupSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurar que exista un Aula
        $aula = Aula::first();
        if (!$aula) {
            $this->command?->warn('No hay Aulas. Ejecuta AulaSeeder antes.');
            return;
        }

        // Crear/actualizar un Docente vinculado al usuario docente@example.com
        $user = User::where('email', 'docente@example.com')->first();
        $docente = Docente::updateOrCreate(
            ['correo' => 'docente@example.com'],
            [
                'nombre_1' => 'Docente',
                'nombre_2' => null,
                'apellido_1' => 'Demo',
                'apellido_2' => 'Test',
                'dni' => (string) rand(10000000, 99999999),
                'genero_id' => 1, // asumiendo que GeneroSeeder creo ID 1
                'aula_id' => $aula->id,
                'activo' => true,
                'celular' => '+51 99 999 9999',
                'observacion' => 'Docente de prueba vinculado por correo',
                'user_id' => $user?->id,
            ]
        );

        // Asignar 20 alumnos al aula del docente si hay alumnos sin aula
        $alumnosSinAula = Alumno::whereNull('aula_id')->limit(20)->get();
        foreach ($alumnosSinAula as $al) {
            $al->aula_id = $aula->id;
            $al->save();
        }

        $this->command?->info('Docente de demo configurado y alumnos asignados al aula.');
    }
}
