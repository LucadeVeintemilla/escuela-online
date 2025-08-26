<?php

namespace App\Livewire;

use App\Models\Asignatura;
use App\Models\Grado;
use Livewire\Component;
use Illuminate\Validation\Rule;

class FormularioAsignaturaGrado extends Component
{
    public $modelo;
    public $id;

    // relaciones
    public $asignaturas;
    public $grados;

    // formulario
    public $asignatura_id;
    public $grado_id;
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
            'asignatura_id' => ['required', 'integer', 'exists:asignaturas,id'],
            'grado_id' => [
                'required',
                'integer',
                'exists:grados,id',
                Rule::unique('asignatura_grados')
                    ->where(fn($q) => $q->where('asignatura_id', $this->asignatura_id)
                                          ->where('grado_id', $this->grado_id)
                                          ->whereNull('deleted_at'))
                    ->ignore($id),
            ],
            'observacion' => 'nullable|string|max:255',
        ];
    }

    public function insertar($modelo)
    {
        // Forzar nueva inserción
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
        $modeloString = 'App\\Models\\' . ($this->modelo ?? 'AsignaturaGrado');

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
        $asignaturas = Asignatura::all();
        $this->asignaturas = $asignaturas;
        $this->asignatura_id = optional($asignaturas->first())->id;

        $grados = Grado::all();
        $this->grados = $grados;
        $this->grado_id = optional($grados->first())->id;
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
        return view('livewire.formulario-asignatura-grado');
    }
}
