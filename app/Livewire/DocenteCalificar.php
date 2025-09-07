<?php

namespace App\Livewire;

use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Calificacion;
use App\Models\CalificacionAsignaturaAlumno;
use App\Models\AsignaturaGrado;
use App\Models\Docente;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DocenteCalificar extends Component
{
    public ?Docente $docente = null;
    public ?Aula $aula = null; // Aula determinada por la asignatura seleccionada (pivot)

    public $alumnos = [];
    public $asignaturas = [];
    public array $asignaturasOptions = [];
    public $calificaciones = [];

    public ?int $asignatura_grado_id = null;
    public ?int $aula_id = null; // aula_id proveniente del pivot

    // calificaciones seleccionadas por alumno: [alumno_id => calificacion_id]
    public array $notas = [];
    public array $observaciones = [];

    public string $mensaje = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->mensaje = 'No autenticado';
            return;
        }

        // Intentar relación formal (user_id); fallback por correo
        $this->docente = Docente::where('user_id', $user->id)->first();
        if (!$this->docente) {
            $this->docente = Docente::where('correo', $user->email)->first();
        }
        if (!$this->docente) {
            $this->mensaje = 'Tu usuario no está vinculado a un Docente (no se encontró coincidencia por email).';
            return;
        }

        // Asignaturas asignadas explícitamente al docente (pivot con aula_id)
        $this->asignaturas = $this->docente->asignaturasGrado()
            ->with('asignatura')
            ->withPivot('aula_id')
            ->orderBy('asignatura_grados.id')
            ->get()
            ->unique('id') // evitar duplicados de cursos
            ->values();
        // Construir opciones con nombre de aula
        $this->asignaturasOptions = [];
        foreach ($this->asignaturas as $ag) {
            $aula = $ag->pivot->aula_id ? \App\Models\Aula::with(['grado','seccion'])->find($ag->pivot->aula_id) : null;
            $aulaNombre = $aula ? (($aula->grado->grado ?? 'Grado').' - '.($aula->seccion->seccion ?? 'Sección')) : 'Aula no asignada';
            $this->asignaturasOptions[] = [
                'id' => $ag->id,
                'label' => ($ag->asignatura->asignatura ?? ('Asignatura #'.$ag->id)).' · '.$aulaNombre,
            ];
        }
        if ($this->asignaturas->isEmpty()) {
            $this->mensaje = 'No tienes asignaturas asignadas. Solicita al administrador que te asigne materias del curso.';
        }

        $this->calificaciones = Calificacion::orderBy('id')->get();

        // Pre-cargar notas existentes si hay una asignatura seleccionada más adelante
    }

    public function updatedAsignaturaGradoId($value): void
    {
        $this->cargarContextoAulaYAlumnos();
        $this->cargarNotasExistentes();
    }

    protected function cargarNotasExistentes(): void
    {
        $this->notas = [];
        $this->observaciones = [];
        if (!$this->asignatura_grado_id || empty($this->alumnos)) return;

        $ids = collect($this->alumnos)->pluck('id')->all();
        $existentes = CalificacionAsignaturaAlumno::whereIn('alumno_id', $ids)
            ->where('asignatura_grado_id', $this->asignatura_grado_id)
            ->get();

        foreach ($existentes as $row) {
            $this->notas[$row->alumno_id] = $row->calificacion_id;
            $this->observaciones[$row->alumno_id] = $row->observacion;
        }
    }

    protected function cargarContextoAulaYAlumnos(): void
    {
        $this->aula = null;
        $this->aula_id = null;
        $this->alumnos = [];

        if (!$this->asignatura_grado_id) return;

        // Buscar la asignatura seleccionada dentro de la colección con su pivot
        $ag = $this->asignaturas->firstWhere('id', (int)$this->asignatura_grado_id);
        if (!$ag) return;

        $this->aula_id = $ag->pivot->aula_id ?? null;
        if (!$this->aula_id) {
            $this->mensaje = 'La asignatura seleccionada no tiene un aula asociada en la asignación. Pida al admin que la configure.';
            return;
        }

        $this->aula = Aula::find($this->aula_id);
        $this->alumnos = Alumno::where('aula_id', $this->aula_id)
            ->orderBy('apellido_1')->orderBy('nombre_1')->get();
    }

    public function guardar(): void
    {
        if (!$this->asignatura_grado_id) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Selecciona una asignatura.']);
            return;
        }
        if (!$this->aula_id) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'La asignatura no está vinculada a un aula.']);
            return;
        }
        foreach ($this->alumnos as $alumno) {
            $calificacionId = $this->notas[$alumno->id] ?? null;
            $obs = $this->observaciones[$alumno->id] ?? null;

            if ($calificacionId) {
                CalificacionAsignaturaAlumno::updateOrCreate(
                    [
                        'alumno_id' => $alumno->id,
                        'asignatura_grado_id' => $this->asignatura_grado_id,
                    ],
                    [
                        'calificacion_id' => $calificacionId,
                        'observacion' => $obs,
                    ]
                );
            }
        }

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Calificaciones guardadas.']);
    }

    public function render()
    {
        return view('livewire.docente-calificar');
    }

    // Hook genérico para detectar cambios en asignatura_grado_id (snake_case)
    public function updated($name, $value): void
    {
        if ($name === 'asignatura_grado_id') {
            $this->cargarContextoAulaYAlumnos();
            $this->cargarNotasExistentes();
        }
    }
}
