<?php

namespace App\Livewire;

use App\Models\Alumno;
use App\Models\AsignaturaGrado;
use App\Models\Aula;
use App\Models\Docente;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AdminAsignaciones extends Component
{
    public array $docentes = [];
    public array $aulas = [];

    public ?int $docente_id = null;
    public ?int $aula_id = null;

    // materias asignadas al docente (pivote)
    public array $asignatura_grado_ids = [];

    // materias disponibles (por grado del aula seleccionada)
    public array $asignaturasDisponibles = [];

    // alumnos del aula seleccionada
    public array $alumnosAula = [];

    // alumnos sin aula (para mover a la aula seleccionada)
    public array $alumnosSinAula = [];

    // selección para mover alumnos
    public array $alumnosSeleccionados = [];

    public string $flash = '';

    public function mount(): void
    {
        $this->docentes = Docente::orderBy('apellido_1')->orderBy('nombre_1')->get(['id','nombre_1','nombre_2','apellido_1','apellido_2'])->map(function($d){
            return [
                'id' => $d->id,
                'nombre' => trim($d->apellido_1.' '.$d->apellido_2.', '.$d->nombre_1.' '.$d->nombre_2)
            ];
        })->toArray();

        $this->aulas = Aula::with(['grado','seccion'])->orderBy('grado_id')->orderBy('seccion_id')->get()->map(function($a){
            return [
                'id' => $a->id,
                'nombre' => ($a->grado->grado ?? 'Grado').' - '.($a->seccion->seccion ?? 'Sección')
            ];
        })->toArray();

        // precargar primeros datos si quieres
    }

    public function updatedDocenteId($value): void
    {
        $this->cargarEstadoDocente();
    }

    public function updatedAulaId($value): void
    {
        $this->cargarAsignaturasDisponibles();
        $this->cargarAlumnosDeAula();
    }

    protected function cargarEstadoDocente(): void
    {
        $this->asignatura_grado_ids = [];
        $this->aula_id = null;
        $docente = $this->docenteSeleccionado();
        if (!$docente) return;

        // Aula actual del docente
        $this->aula_id = $docente->aula_id;

        // Materias asignadas actualmente al docente (pivot)
        $this->asignatura_grado_ids = $docente->asignaturasGrado()->pluck('asignatura_grados.id')->all();

        $this->cargarAsignaturasDisponibles();
        $this->cargarAlumnosDeAula();
    }

    protected function cargarAsignaturasDisponibles(): void
    {
        $this->asignaturasDisponibles = [];
        if (!$this->aula_id) return;
        $aula = Aula::with('grado')->find($this->aula_id);
        if (!$aula) return;

        $this->asignaturasDisponibles = AsignaturaGrado::with('asignatura')
            ->where('grado_id', $aula->grado_id)
            ->orderBy('id')
            ->get()
            ->map(fn($ag) => ['id' => $ag->id, 'nombre' => $ag->asignatura->asignatura ?? ('Asignatura #'.$ag->id)])
            ->toArray();
    }

    protected function cargarAlumnosDeAula(): void
    {
        $this->alumnosAula = [];
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
            ->limit(100)
            ->get(['id','nombre_1','nombre_2','apellido_1','apellido_2'])
            ->map(fn($al) => [
                'id' => $al->id,
                'nombre' => trim($al->apellido_1.' '.$al->apellido_2.', '.$al->nombre_1.' '.$al->nombre_2)
            ])->toArray();

        $this->alumnosSeleccionados = [];
    }

    public function guardarAsignaciones(): void
    {
        $docente = $this->docenteSeleccionado();
        if (!$docente) { $this->flash = 'Selecciona un docente.'; return; }

        // Asignar Aula
        if ($this->aula_id) {
            $docente->aula_id = $this->aula_id;
            $docente->save();
        }

        // Asignar materias (pivot)
        $ids = array_filter($this->asignatura_grado_ids ?? [], fn($v) => !empty($v));
        $payload = [];
        foreach ($ids as $id) {
            $payload[$id] = ['aula_id' => $this->aula_id];
        }
        // sincroniza manteniendo solo las materias seleccionadas y setea aula_id en pivot
        $docente->asignaturasGrado()->sync($payload);

        $this->flash = 'Asignaciones guardadas.';
    }

    public function moverAlumnosAula(): void
    {
        if (!$this->aula_id) { $this->flash = 'Selecciona un aula antes de mover alumnos.'; return; }
        $ids = array_filter($this->alumnosSeleccionados ?? [], fn($v) => !empty($v));
        if (empty($ids)) { $this->flash = 'Selecciona alumnos para mover.'; return; }

        Alumno::whereIn('id', $ids)->update(['aula_id' => $this->aula_id]);
        $this->flash = 'Alumnos movidos al aula seleccionada.';
        $this->cargarAlumnosDeAula();
    }

    protected function docenteSeleccionado(): ?Docente
    {
        return $this->docente_id ? Docente::find($this->docente_id) : null;
    }

    public function render()
    {
        return view('livewire.admin-asignaciones');
    }
}
