<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['rol' => 'Admin',   'observacion' => 'Administrador del sistema'],
            ['rol' => 'Docente', 'observacion' => 'Profesor'],
            ['rol' => 'Alumno',  'observacion' => 'Estudiante'],
        ];

        foreach ($roles as $data) {
            Rol::withTrashed()->updateOrCreate(
                ['rol' => $data['rol']],
                ['observacion' => $data['observacion']]
            );
        }
    }
}
