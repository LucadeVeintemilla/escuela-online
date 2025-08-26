<?php

namespace App\Livewire;

use App\Models\Aula;
use App\Models\Genero;
use Livewire\Component;
use Illuminate\Validation\Rule;

class FormularioDocente extends Component
{
    public $modelo;
    public $id;

    //objetos relacionados
    public $generos;
    public $aulas;

    //formulario
    public $nombre_1;
    public $nombre_2;
    public $apellido_1;
    public $apellido_2;
    public $dni;
    public $correo;
    public $celular;
    
    public $observacion;
    
    public $genero_id;
    public $aula_id;

    public $created_at;
    public $updated_at;

    //escuchadores
    protected $listeners = [
        'actualizar',
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
            $objeto->forceDelete();
            $this->dispatch('eliminarFila2', id: $this->id)->to(Fila::class);
        }
    }

    //OK
    public function formularioAlObjeto($modelo, &$objeto){
        $modeloString = 'App\\Models\\' . $modelo;
        $camposModificables = $modeloString::camposModificables();

        foreach ($camposModificables as $key => $campo) {
            $objeto->$campo = ($this->$campo === '' ? null : $this->$campo);
        }
    }

    protected function reglas(?int $id = null): array
    {
        return [
            'nombre_1'   => 'required|string|max:255',
            'nombre_2'   => 'nullable|string|max:255',
            'apellido_1' => 'required|string|max:255',
            'apellido_2' => 'required|string|max:255',
            'dni'        => [
                'required',
                'digits:8',
                Rule::unique('docentes', 'dni')->ignore($id)->whereNull('deleted_at'),
            ],
            'genero_id'  => 'required|exists:generos,id',
            'aula_id'    => 'nullable|exists:aulas,id',
            'correo'     => [
                'nullable', 'email',
                Rule::unique('docentes', 'correo')->ignore($id)->whereNull('deleted_at'),
            ],
            'celular'    => [
                'nullable', 'string',
                Rule::unique('docentes', 'celular')->ignore($id)->whereNull('deleted_at'),
            ],
            'observacion'=> 'nullable|string|max:255',
        ];
    }

    //OK
    public function insertar($modelo)
    {
        // Siempre inserta un nuevo registro
        $this->id = null;

        $this->validate($this->reglas());
        $modeloString = 'App\\Models\\' . $modelo;
        $objeto =  new $modeloString;

        $this->formularioAlObjeto($modelo, $objeto);
        $objeto->save();

        // Refrescar y navegar a última página
        $this->dispatch('actualizarMasivo')->to(Tabla::class);
        $this->dispatch('irALaUltimaPagina')->to(Tabla::class);

        // Cerrar modal
        $this->js("window.dispatchEvent(new CustomEvent('close-insert-modal'))");

        // Resetear formulario
        $this->inicializar($modelo);
    }

    //OK
    public function actualizar()
    {
        $this->validate($this->reglas($this->id));
        $modeloString = 'App\\Models\\' . $this->modelo;
        $objeto = $modeloString::find($this->id);

        if ($objeto) {
            $this->formularioAlObjeto($this->modelo, $objeto);
            $objeto->update();
            $this->dispatch('actualizar', $objeto->id)->to(Fila::class);
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
        $this->generos = Genero::all();
        $this->genero_id = $this->generos->first()->id;

        $this->aulas = Aula::all();
        $this->aula_id = null;
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
        return view('livewire.formulario-docente');
    }
}