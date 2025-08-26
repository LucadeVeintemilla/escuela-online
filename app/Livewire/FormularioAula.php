<?php

namespace App\Livewire;

use App\Models\Grado;
use App\Models\Seccion;
use Livewire\Component;
use Illuminate\Validation\Rule;

class FormularioAula extends Component
{
    public $modelo;
    public $id;

    //objetos relacionados
    public $grados;
    public $seccions;

    //formulario
    public $grado_id;
    public $seccion_id;
    public $observacion;

    public $created_at;
    public $updated_at;

    //escuchadores
    protected $listeners = [
        'insertar',
        'inicializar',
        'consultar',
        'eliminar',
    ];

    //OK
    public function eliminar()
    {
        $modeloString = 'App\\Models\\' . $this->modelo;
        $objeto = $modeloString::find($this->id);

        if ($objeto) {
            $objeto->delete();
            $this->dispatch('eliminarFila2', id: $this->id)->to(Fila::class);
        }
    }

    //OK
    public function formularioAlObjeto($modelo, &$objeto){
        $modeloString = 'App\\Models\\' . $modelo;
        $camposModificables = $modeloString::camposModificables();

        foreach ($camposModificables as $key => $campo) {
            $objeto->$campo = (empty($this->$campo) ? null : $this->$campo);
        }
    }

    protected function reglas(?int $id = null): array
    {
        return [
            'grado_id'    => ['required', 'integer', 'exists:grados,id'],
            'seccion_id'  => [
                'required',
                'integer',
                'exists:seccions,id',
                Rule::unique('aulas')
                    ->where(fn($q) => $q->where('grado_id', $this->grado_id)
                                          ->where('seccion_id', $this->seccion_id)
                                          ->whereNull('deleted_at'))
                    ->ignore($id),
            ],
            'observacion' => 'nullable|string|max:255',
        ];
    }

    public function insertar($modelo)
    {
        // Forzar inserción de nuevo registro
        $this->id = null;
        $this->validate($this->reglas());

        $modeloString = 'App\\Models\\' . $modelo;
        $objeto =  new $modeloString;

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
        $modeloString = 'App\\Models\\' . ($this->modelo ?? 'Aula');

        $objeto = $modeloString::withTrashed()->find($this->id);
        if (!$objeto) {
            return;
        }

        // Normalizar entradas
        $this->grado_id = $this->grado_id ? (int) $this->grado_id : null;
        $this->seccion_id = $this->seccion_id ? (int) $this->seccion_id : null;
        $this->observacion = is_string($this->observacion) ? trim($this->observacion) : $this->observacion;

        $campos = $modeloString::camposModificables();
        $data = [];
        foreach ($campos as $campo) {
            $data[$campo] = ($this->$campo === '' ? null : $this->$campo);
        }

        $objeto->forceFill($data);
        $saved = $objeto->save();
        if ($saved) {
            // Sincronizar propiedades locales tras guardar
            foreach ($campos as $campo) {
                $this->$campo = $objeto->$campo;
            }
            $this->dispatch('actualizar')->to(Fila::class);
            $this->dispatch('paginar')->to(Tabla::class);
            $this->dispatch('$refresh');
            // Cerrar modal via browser event
            $this->js("window.dispatchEvent(new CustomEvent('close-modal-aula'))");
        }
    }

    //OK
    public function consultar($modelo, $id)
    {
        $modeloString = 'App\\Models\\' . $modelo;
        $objeto = $modeloString::find($id);
        
        if ($objeto) {
            $camposModificables = $modeloString::camposModificables();
            foreach ($camposModificables as $key => $campo) {
                $this->$campo = $objeto->$campo;
            }

            $camposNoModificables = $modeloString::camposNoModificables();
            foreach ($camposNoModificables as $key => $campo) {
                $this->$campo = $objeto->$campo;
            }

            $this->id = $id;
        }
    }

    //OK
    public function inicializarRelaciones(){
        $grados = Grado::all();
        $this->grados = $grados;
        $this->grado_id = optional($grados->first())->id;

        $seccions = Seccion::all();
        $this->seccions = $seccions;
        $this->seccion_id = optional($seccions->first())->id;
    }

    //OK
    public function inicializar($modelo){
        $modeloString = 'App\\Models\\' . $modelo;
        $camposModificables = $modeloString::camposModificables();

        foreach ($camposModificables as $key => $campo) {
            $this->$campo = null;
        }

        $this->inicializarRelaciones();
        $this->id = null;
    }

    //OK
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
        return view('livewire.formulario-aula');
    }
}