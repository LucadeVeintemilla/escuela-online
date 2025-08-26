<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Validation\Rule;
use App\Models\Alumno;
use App\Models\AsignaturaGrado;
use App\Models\Calificacion;

class FormularioCalificacionAsignaturaAlumno extends Component
{
    public $modelo;
    public $id;

    // relaciones
    public $alumnos;
    public $asignaturaGrados;
    public $calificaciones;

    // formulario
    public $alumno_id;
    public $asignatura_grado_id;
    public $calificacion_id;
    public $observacion;

    public $created_at;
    public $updated_at;

    protected $listeners = [
        'actualizar',
        'insertar',
        'inicializar',
        'consultar',
        'eliminar',
    ];

    public function eliminar()
    {
        $modeloString = 'App\\Models\\' . $this->modelo;
        $objeto = $modeloString::find($this->id);
        if ($objeto) {
            $objeto->delete();
            $this->dispatch('eliminarFila2', id: $this->id)->to(Fila::class);
        }
    }

    public function formularioAlObjeto($modelo, &$objeto)
    {
        $modeloString = 'App\\Models\\' . $modelo;
        $camposModificables = $modeloString::camposModificables();
        foreach ($camposModificables as $campo) {
            $objeto->$campo = (empty($this->$campo) ? null : $this->$campo);
        }
    }

    protected function reglas(?int $id = null): array
    {
        return [
            'alumno_id' => ['required', 'integer', 'exists:alumnos,id'],
            'asignatura_grado_id' => ['required', 'integer', 'exists:asignatura_grados,id'],
            'calificacion_id' => [
                'required',
                'integer',
                'exists:calificacions,id',
                // Evitar duplicados del mismo trio, ignorando soft deletes
                Rule::unique('calificacion_asignatura_alumnos')
                    ->where(fn($q) => $q->where('alumno_id', $this->alumno_id)
                                          ->where('asignatura_grado_id', $this->asignatura_grado_id)
                                          ->where('calificacion_id', $this->calificacion_id)
                                          ->whereNull('deleted_at'))
                    ->ignore($id),
            ],
            'observacion' => 'nullable|string|max:255',
        ];
    }

    public function insertar($modelo)
    {
        $this->id = null;
        $this->validate($this->reglas());

        $modeloString = 'App\\Models\\' . $modelo;
        $objeto = new $modeloString;

        $this->formularioAlObjeto($modelo, $objeto);
        $objeto->save();

        $this->dispatch('actualizarMasivo')->to(Tabla::class);
        $this->dispatch('irALaUltimaPagina')->to(Tabla::class);
        $this->js("window.dispatchEvent(new CustomEvent('close-insert-modal'))");
        $this->inicializar($modelo);
    }

    public function actualizar()
    {
        $this->validate($this->reglas($this->id));
        $modeloString = 'App\\Models\\' . ($this->modelo ?? 'CalificacionAsignaturaAlumno');

        $objeto = $modeloString::withTrashed()->find($this->id);
        if (!$objeto) {
            return;
        }

        $campos = $modeloString::camposModificables();
        $data = [];
        foreach ($campos as $campo) {
            $data[$campo] = ($this->$campo === '' ? null : $this->$campo);
        }

        $objeto->forceFill($data);
        $saved = $objeto->save();
        if ($saved) {
            $this->dispatch('actualizar')->to(Fila::class);
            $this->dispatch('paginar')->to(Tabla::class);
            $this->dispatch('$refresh');
            $this->js("$('#modalDetallesObjeto').modal('hide')");
        }
    }

    public function consultar($modelo, $id)
    {
        $modeloString = 'App\\Models\\' . $modelo;
        $objeto = $modeloString::find($id);
        if ($objeto) {
            $camposModificables = $modeloString::camposModificables();
            foreach ($camposModificables as $campo) {
                $this->$campo = $objeto->$campo;
            }

            if (method_exists($modeloString, 'camposNoModificables')) {
                $camposNoModificables = $modeloString::camposNoModificables();
                foreach ($camposNoModificables as $campo) {
                    $this->$campo = $objeto->$campo;
                }
            }

            $this->id = $id;
        }
    }

    public function inicializarRelaciones()
    {
        $alumnos = Alumno::with('usuario')->get();
        $this->alumnos = $alumnos;
        $this->alumno_id = optional($alumnos->first())->id;

        $ags = AsignaturaGrado::with(['asignatura', 'grado'])->get();
        $this->asignaturaGrados = $ags;
        $this->asignatura_grado_id = optional($ags->first())->id;

        $calificaciones = Calificacion::all();
        $this->calificaciones = $calificaciones;
        $this->calificacion_id = optional($calificaciones->first())->id;
    }

    public function inicializar($modelo)
    {
        $modeloString = 'App\\Models\\' . $modelo;
        if (method_exists($modeloString, 'camposModificables')) {
            $camposModificables = $modeloString::camposModificables();
            foreach ($camposModificables as $campo) {
                $this->$campo = null;
            }
        }

        $this->inicializarRelaciones();
        $this->id = null;
    }

    public function mount($modelo, $id)
    {
        $this->inicializar($modelo);
        if ($id) {
            $this->consultar($modelo, $id);
        }
        $this->modelo = $modelo;
        $this->id = $id;
    }

    public function render()
    {
        return view('livewire.formulario-calificacion-asignatura-alumno');
    }
}
