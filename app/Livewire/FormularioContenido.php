<?php

namespace App\Livewire;

use App\Models\TipoContenido;
use App\Models\Usuario;
use Livewire\Component;
use Faker\Factory;
use Illuminate\Validation\Rule;

class FormularioContenido extends Component
{
    public $modelo;
    public $id;

    //objetos relacionados
    public $usuarios;
    public $tipoContenidos;

    //formulario
    public $contenido;
    public $usuario_id;
    public $tipo_contenido_id;
    public $path;
    public $observacion;

    public $created_at;
    public $updated_at;

    //campos extras
    public $propietario;

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
            'contenido' => ['required', 'string', 'max:255', Rule::unique('contenidos', 'contenido')->ignore($id)->whereNull('deleted_at')],
            'tipo_contenido_id' => ['required', 'integer', 'exists:tipo_contenidos,id'],
            'usuario_id' => ['required', 'integer', 'exists:usuarios,id'],
            'path' => ['nullable', 'string', 'max:255'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function insertar($modelo)
    {
        // Forzar inserción
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
        $modeloString = 'App\\Models\\' . ($this->modelo ?? 'Contenido');
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
            $this->propietario = $objeto->usuario->nombre_1 . ' ' . $objeto->usuario->nombre_2 . ' ' . $objeto->usuario->apellido_1 . ' ' . $objeto->usuario->apellido_2;   
        }
    }

    //OK
    public function inicializarRelaciones(){
        $usuarios = Usuario::all();
        $this->usuarios = $usuarios;
        
        $usuario = $usuarios->first();
        $this->usuario_id = optional($usuario)->id;
        $this->propietario = $usuario ? ($usuario->nombre_1 . ' ' . ($usuario->nombre_2 ?? '') . ' ' . $usuario->apellido_1 . ' ' . $usuario->apellido_2) : null;

        $this->tipoContenidos = TipoContenido::all();
        $this->tipo_contenido_id = optional($this->tipoContenidos->first())->id;

        $faker = Factory::create();
        $this->path = '/' . $faker->word;
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
        return view('livewire.formulario-contenido');
    }
}
