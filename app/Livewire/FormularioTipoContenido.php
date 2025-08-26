<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormularioTipoContenido extends Component
{
    public $modelo;
    public $id;

    //formulario
    public $tipo;
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
            'tipo' => ['required', 'string', 'max:255', Rule::unique('tipo_contenidos', 'tipo')->ignore($id)->whereNull('deleted_at')],
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
        Log::info('TipoContenido actualizar: inicio', [
            'this_id' => $this->id,
            'this_modelo' => $this->modelo,
            'tipo' => $this->tipo,
            'observacion' => $this->observacion,
        ]);
        $this->validate($this->reglas($this->id));
        $modeloString = 'App\\Models\\' . ($this->modelo ?? 'TipoContenido');
        $objeto = $modeloString::withTrashed()->find($this->id);

        if (!$objeto) {
            Log::warning('TipoContenido actualizar: objeto no encontrado', ['id' => $this->id]);
            return;
        }

        // Normalizar entradas
        $this->tipo = is_string($this->tipo) ? trim($this->tipo) : $this->tipo;
        $this->observacion = is_string($this->observacion) ? trim($this->observacion) : $this->observacion;

        $campos = $modeloString::camposModificables();
        $data = [];
        foreach ($campos as $campo) {
            $data[$campo] = ($this->$campo === '' ? null : $this->$campo);
        }
        Log::info('TipoContenido actualizar: datos a guardar', $data);

        DB::beginTransaction();
        try {
            $objeto->forceFill($data);
            $saved = $objeto->save();
            $objeto->refresh();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('TipoContenido actualizar: error al guardar', ['id' => $this->id, 'error' => $e->getMessage()]);
            throw $e;
        }

        Log::info('TipoContenido actualizar: resultado de guardado', ['saved' => $saved, 'id' => $objeto->id, 'tipo' => $objeto->tipo]);
        if ($saved) {
            // Sincronizar propiedades para evitar que el formulario "revierte" visualmente
            foreach ($campos as $campo) {
                $this->$campo = $objeto->$campo;
            }
            $this->dispatch('actualizar', id: $objeto->id)->to(Fila::class);
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
        return view('livewire.formulario-tipo-contenido');
    }
}
