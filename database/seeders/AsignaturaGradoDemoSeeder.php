<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asignatura;
use App\Models\AsignaturaGrado;
use App\Models\Grado;

class AsignaturaGradoDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Materias base
        $materiasBase = [
            ['nombre' => 'Matemáticas', 'abrev' => 'MAT'],
            ['nombre' => 'Comunicación', 'abrev' => 'COM'],
            ['nombre' => 'Ciencia y Tecnología', 'abrev' => 'CTEC'],
            ['nombre' => 'Personal Social', 'abrev' => 'PSOC'],
            ['nombre' => 'Inglés', 'abrev' => 'ING'],
        ];

        // Asegurar asignaturas base
        $asignaturas = [];
        foreach ($materiasBase as $item) {
            $nombre = $item['nombre'];
            $abrev = $item['abrev'];
            // Buscar por abreviatura primero (único), luego por nombre
            $asig = Asignatura::where('abreviatura', $abrev)->first();
            if (!$asig) {
                $asig = Asignatura::where('asignatura', $nombre)->first();
            }
            if (!$asig) {
                $asig = new Asignatura();
                $asig->asignatura = $nombre;
                $asig->abreviatura = $abrev; // reservar esta abreviatura nueva
                $asig->observacion = 'Demo';
                $asig->save();
            } else {
                // Completar datos faltantes sin violar unicidad
                if (empty($asig->asignatura)) {
                    $asig->asignatura = $nombre;
                }
                if (empty($asig->abreviatura)) {
                    $asig->abreviatura = $abrev; // solo si está vacío
                }
                if (empty($asig->observacion)) {
                    $asig->observacion = 'Demo';
                }
                $asig->save();
            }
            $asignaturas[$nombre] = $asig;
        }

        $grados = Grado::all();
        if ($grados->isEmpty()) {
            $this->command?->warn('No hay grados creados. Crea grados antes de ejecutar este seeder.');
            return;
        }

        $creados = 0;
        foreach ($grados as $grado) {
            foreach ($asignaturas as $asig) {
                $exists = AsignaturaGrado::where('grado_id', $grado->id)
                    ->where('asignatura_id', $asig->id)
                    ->exists();
                if (!$exists) {
                    AsignaturaGrado::create([
                        'asignatura_id' => $asig->id,
                        'grado_id' => $grado->id,
                        'observacion' => 'Auto-creado por AsignaturaGradoDemoSeeder',
                    ]);
                    $creados++;
                }
            }
        }

        $this->command?->info("Cursos creados/asegurados: $creados");
    }
}
