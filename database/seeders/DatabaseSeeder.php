<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles and default admin first
        $this->call(RolSeeder::class);
        $this->call(AdminUserSeeder::class);

        $this->call(GradoSeeder::class);
        $this->call(AsignaturaSeeder::class);
        $this->call(AsignaturaGradoSeeder::class);
        $this->call(SeccionSeeder::class);
        $this->call(AulaSeeder::class);
        $this->call(GeneroSeeder::class);
        $this->call(UsuarioSeeder::class);
        $this->call(ContactoSeeder::class);
        $this->call(DirectivoSeeder::class);
        $this->call(DocenteSeeder::class);
        $this->call(TutorSeeder::class);
        $this->call(AlumnoSeeder::class);
        $this->call(AlumnoTutorSeeder::class);
        $this->call(TipoContenidoSeeder::class);
        $this->call(ContenidoSeeder::class);
        $this->call(AnuncioSeeder::class);
        $this->call(AnuncioContenidoSeeder::class);
        $this->call(ActividadSeeder::class);
        $this->call(ActividadContenidoSeeder::class);
        $this->call(CalificacionSeeder::class);
    }
}