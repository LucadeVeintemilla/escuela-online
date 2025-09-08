<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Rol;
use App\Models\Alumno;
use App\Models\Aula;

class AlumnoUserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear rol Alumno si no existe
        $alumnoRole = Rol::firstOrCreate(
            ['rol' => 'Alumno'],
            ['observacion' => 'Estudiante']
        );

        // Crear usuario de prueba
        $user = User::updateOrCreate(
            ['email' => 'alumno@test.com'],
            [
                'name' => 'Alumno Demo',
                'password' => Hash::make('password'),
                'role_id' => $alumnoRole->id,
                'email_verified_at' => now(),
            ]
        );

        // Intentar vincular con un Alumno existente (preferencia: con aula asignada)
        $alumno = Alumno::whereNull('user_id')->whereNotNull('aula_id')->first();
        if (!$alumno) {
            $alumno = Alumno::whereNull('user_id')->first();
        }
        if ($alumno) {
            $alumno->user_id = $user->id;
            $alumno->save();
            return;
        }

        // Si no hay ningún alumno disponible, crear uno de prueba
        $aula = Aula::first();
        $alumno = new Alumno();
        $alumno->nombre_1 = 'Alumno';
        $alumno->apellido_1 = 'Demo';
        $alumno->genero_id = 1; // asume que 1 existe
        $alumno->dni = rand(10000000, 99999999);
        if ($aula) { $alumno->aula_id = $aula->id; }
        $alumno->user_id = $user->id;
        $alumno->save();
    }
}
