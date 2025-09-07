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
    // selección compuesta: "asignatura_grado_id|aula_id"
    public ?string $seleccion = null;
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
            ->get();
        // Construir opciones con nombre de aula
        $this->asignaturasOptions = [];
        foreach ($this->asignaturas as $ag) {
            $aula = $ag->pivot->aula_id ? \App\Models\Aula::with(['grado','seccion'])->find($ag->pivot->aula_id) : null;
            $aulaNombre = $aula ? (($aula->grado->grado ?? 'Grado').' - '.($aula->seccion->seccion ?? 'Sección')) : 'Aula no asignada';
            $this->asignaturasOptions[] = [
                'id' => $ag->id.'|'.($ag->pivot->aula_id ?? 0),
                'label' => ($ag->asignatura->asignatura ?? ('Asignatura #'.$ag->id)).' · '.$aulaNombre,
            ];
        }
        if ($this->asignaturas->isEmpty()) {
            $this->mensaje = 'No tienes asignaturas asignadas. Solicita al administrador que te asigne materias del curso.';
        }

        $this->calificaciones = Calificacion::orderBy('id')->get();

        // Pre-cargar notas existentes si hay una asignatura seleccionada más adelante
    }

    public function updatedSeleccion($value): void
    {
        // Espera formato "agid|aulaid"
        $this->asignatura_grado_id = null;
        $this->aula_id = null;
        if (!$value) { $this->alumnos = []; return; }
        [$agId, $aulaId] = array_pad(explode('|', (string)$value), 2, null);
        $this->asignatura_grado_id = $agId ? (int)$agId : null;
        $this->aula_id = $aulaId ? (int)$aulaId : null;
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
