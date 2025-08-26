<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Validation\Rule;

class FormularioGenero extends Component
{
    public $modelo;
    public $id;

    //formulario
    public $genero;
    public $abreviatura;
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
            'genero' => ['required', 'string', 'max:255', Rule::unique('generos', 'genero')->ignore($id)->whereNull('deleted_at')],
            'abreviatura' => ['required', 'string', 'max:10', Rule::unique('generos', 'abreviatura')->ignore($id)->whereNull('deleted_at')],
            'observacion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function insertar($modelo)
    {
        // Forzar inserción
        \Log::info('Genero insertar: inicio', [
            'modelo' => $modelo,
            'genero' => $this->genero,
            'abreviatura' => $this->abreviatura,
            'observacion' => $this->observacion,
        ]);
        $this->id = null;
        $this->validate($this->reglas());

        $modeloString = 'App\\Models\\' . $modelo;
        $objeto =  new $modeloString;

        $this->formularioAlObjeto($modelo, $objeto);
        $saved = $objeto->save();
        \Log::info('Genero insertar: resultado', ['saved' => (bool)$saved, 'id' => $objeto->id ?? null]);

        $this->dispatch('actualizarMasivo')->to(Tabla::class);
        $this->dispatch('irALaUltimaPagina')->to(Tabla::class);
        $this->js("window.dispatchEvent(new CustomEvent('close-insert-modal'))");
        $this->inicializar($modelo);
    }

    public function actualizar()
    {
        $this->validate($this->reglas($this->id));
        $modeloString = 'App\\Models\\' . ($this->modelo ?? 'Genero');
        $objeto = $modeloString::withTrashed()->find($this->id);

        if (!$objeto) {
            return;
        }

        // Normalizar entradas
        $this->genero = is_string($this->genero) ? trim($this->genero) : $this->genero;
        $this->abreviatura = is_string($this->abreviatura) ? trim($this->abreviatura) : $this->abreviatura;
        $this->observacion = is_string($this->observacion) ? trim($this->observacion) : $this->observacion;

        $campos = $modeloString::camposModificables();
        $data = [];
        foreach ($campos as $campo) {
            $data[$campo] = ($this->$campo === '' ? null : $this->$campo);
        }

        $objeto->forceFill($data);
        $saved = $objeto->save();
        if ($saved) {
            // Sincronizar propiedades locales para evitar reversión visual
            foreach ($campos as $campo) {
                $this->$campo = $objeto->$campo;
            }
            $this->dispatch('actualizar')->to(Fila::class);
            $this->dispatch('paginar')->to(Tabla::class);
            $this->dispatch('$refresh');
            // Notificar al front para cerrar el modal y limpiar el backdrop
            $this->js("window.dispatchEvent(new CustomEvent('close-modal-tipo-contenido'))");
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
        return view('livewire.formulario-genero');
    }
}