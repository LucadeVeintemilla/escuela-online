<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

class DocenteUserSeeder extends Seeder
{
    public function run(): void
    {
        $docenteRole = Rol::firstOrCreate(
            ['rol' => 'Docente'],
            ['observacion' => 'Profesor']
        );

        User::updateOrCreate(
            ['email' => 'docente@example.com'],
            [
                'name' => 'Docente Demo',
                'password' => Hash::make('password'),
                'role_id' => $docenteRole->id,
                'email_verified_at' => now(),
            ]
        );
    }
}
