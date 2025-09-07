<?php

namespace App\Livewire;

use App\Models\Alumno;
use App\Models\Aula;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AdminAsignarAlumnosAula extends Component
{
    public array $aulas = [];
    public ?int $aula_id = null;

    public array $alumnosAula = [];
    public array $alumnosSinAula = [];

    public array $seleccionAgregar = [];
    public array $seleccionQuitar = [];

    public string $flash = '';

    public function mount(): void
    {
        $this->aulas = Aula::with(['grado','seccion'])->orderBy('grado_id')->orderBy('seccion_id')
            ->get()->map(fn($a) => [
                'id' => $a->id,
                'nombre' => ($a->grado->grado ?? 'Grado').' - '.($a->seccion->seccion ?? 'Sección')
            ])->toArray();

        $this->cargarListas();
    }

    public function updatedAulaId($value): void
    {
        $this->cargarListas();
    }

    public function aulaChanged(): void
    {
        $this->cargarListas();
    }

    protected function cargarListas(): void
    {
        $this->alumnosAula = [];
        $this->alumnosSinAula = [];
        $this->seleccionAgregar = [];
        $this->seleccionQuitar = [];

        if ($this->aula_id) {
            $this->alumnosAula = Alumno::where('aula_id', $this->aula_id)
                ->orderBy('apellido_1')->orderBy('nombre_1')
                ->get(['id','nombre_1','nombre_2','apellido_1','apellido_2'])
                ->map(fn($al) => [
                    'id' => $al->id,
                    'nombre' => trim($al->apellido_1.' '.$al->apellido_2.', '.$al->nombre_1.' '.$al->nombre_2)
                ])->toArray();
        }

        $this->alumnosSinAula = Alumno::whereNull('aula_id')
            ->orderBy('apellido_1')->orderBy('nombre_1')
            ->limit(200)
            ->get(['id','nombre_1','nombre_2','apellido_1','apellido_2'])
            ->map(fn($al) => [
                'id' => $al->id,
                'nombre' => trim($al->apellido_1.' '.$al->apellido_2.', '.$al->nombre_1.' '.$al->nombre_2)
            ])->toArray();
    }

    public function agregarAlAula(): void
    {
        if (!$this->aula_id) { $this->flash = 'Selecciona un aula.'; return; }
        $ids = array_filter($this->seleccionAgregar ?? [], fn($v) => !empty($v));
        if (empty($ids)) { $this->flash = 'Selecciona alumnos para agregar.'; return; }

        // Detectar alumnos con aula previa distinta
        $alumnosPrevios = Alumno::whereIn('id', $ids)->whereNotNull('aula_id')->get(['id','aula_id']);
        if ($alumnosPrevios->isNotEmpty()) {
            $this->flash = 'Alerta: Algunos alumnos ya tenían asignación previa y serán reasignados.';
        }

        // Cerrar historial anterior y crear nuevo registro en alumno_aulas
        DB::transaction(function() use ($ids) {
            // Cerrar ended_at para asignaciones activas anteriores
            DB::table('alumno_aulas')
                ->whereIn('alumno_id', $ids)
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);

            // Crear nuevos registros
            $rows = array_map(fn($alId) => [
                'alumno_id' => (int)$alId,
                'aula_id' => (int)$this->aula_id,
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ], $ids);
            DB::table('alumno_aulas')->insert($rows);

            // Actualizar aula actual del alumno
            Alumno::whereIn('id', $ids)->update(['aula_id' => $this->aula_id]);
        });

        $this->flash .= ' Alumnos agregados al aula.';
        $this->cargarListas();
    }

    public function quitarDelAula(): void
    {
        if (!$this->aula_id) { $this->flash = 'Selecciona un aula.'; return; }
        $ids = array_filter($this->seleccionQuitar ?? [], fn($v) => !empty($v));
        if (empty($ids)) { $this->flash = 'Selecciona alumnos para quitar.'; return; }

        DB::transaction(function() use ($ids) {
            // Cerrar ended_at para la asignación actual en esta aula
            DB::table('alumno_aulas')
                ->whereIn('alumno_id', $ids)
                ->where('aula_id', $this->aula_id)
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);

            // Quitar de la aula actual
            Alumno::whereIn('id', $ids)->where('aula_id', $this->aula_id)->update(['aula_id' => null]);
        });

        $this->flash = 'Alumnos quitados del aula.';
        $this->cargarListas();
    }

    public function render()
    {
        return view('livewire.admin-asignar-alumnos-aula');
    }
}
