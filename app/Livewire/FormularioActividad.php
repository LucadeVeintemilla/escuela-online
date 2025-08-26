<?php

namespace App\Livewire;

use App\Models\Usuario;
use Livewire\Component;
use Illuminate\Validation\Rule;

class FormularioActividad extends Component
{
    public $modelo;
    public $id;

    //objetos relacionados
    public $usuarios;
    public $creador;

    //formulario
    public $actividad;
    public $descripcion;
    public $inicio;
    public $fin;
    public $usuario_id;
    public $observacion;

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
            'actividad' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'inicio' => ['required', 'date'],
            'fin' => ['required', 'date', 'after_or_equal:inicio'],
            'usuario_id' => ['required', 'integer', 'exists:usuarios,id'],
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
        $modeloString = 'App\\Models\\' . ($this->modelo ?? 'Actividad');
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
            $this->creador = $objeto->usuario->nombre_1 . ' ' . $objeto->usuario->nombre_2 . ' ' . $objeto->usuario->apellido_1 . ' ' . $objeto->usuario->apellido_2;
        }
    }

    //OK
    public function inicializarRelaciones(){
        $usuarios = Usuario::all();
        $this->usuarios = $usuarios;

        $usuario = $usuarios->first();
        $this->usuario_id = optional($usuario)->id;
        $this->creador = $usuario ? ($usuario->nombre_1 . ' ' . ($usuario->nombre_2 ?? '') . ' ' . $usuario->apellido_1 . ' ' . $usuario->apellido_2) : null;

        $this->inicio = now()->format('Y-m-d H:i:s');
        $this->fin = now()->format('Y-m-d H:i:s');
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
        return view('livewire.formulario-actividad');
    }
}