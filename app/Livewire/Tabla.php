<?php

namespace App\Livewire;

use Livewire\Component;

class Tabla extends Component
{
    public $titulo;
    
    public $campos;
    public $modelo;
    public $modeloString;

    public $totalObjetos;//cantidad total de objetos
    public $objetosPaginados;//lista de objetos en la página ya obtenidos
    public $objetosPagina;//cantidad de objetos por página

    public $pagina;//número de la página mostrada
    public $totalPaginas;//número de la última página coincide con el total de paginas

    public $estadoFilas=false;

    protected $listeners = [
        'confirmarEliminarObjeto',
        'setPagina',
        'setTotalObjetos',
        'paginar',
        'actualizarMasivo'
    ];

    public function invertirEstadoFilas(){
        $this->estadoFilas = !$this->estadoFilas;
        $this->dispatch('setEstadoFila', $this->estadoFilas)->to(Fila::class);
    }

    //OK
    public function verInsertarObjeto(){
        $this->dispatch('inicializar', $this->modelo)->to('App\\Livewire\\Formulario' . $this->modelo);
    }

    //OK
    public function eliminarMasivo(){
        $this->dispatch('eliminarMasivo')->to(Fila::class);
    }

    //OK
    public function actualizarMasivo(){
        $this->paginar();
        $this->dispatch('actualizar')->to(Fila::class);
    }

    //OK
    public function setTotalObjetos(){
        // Include soft-deleted rows in the total count
        $this->totalObjetos = $this->modeloString::withTrashed()->count();
        if ($this->pagina >= $this->totalPaginas) {
            $this->pagina = $this->totalPaginas;
        }
        $this->paginar();
    }

    //OK
    public function setObjetosPagina($objetos){
        if ($objetos >= 1) {
            $this->objetosPagina = $objetos;
            $this->paginar();
        }
    }

    //OK
    public function setPagina($pagina)
    {
        if ($pagina >= 1 && $pagina <= $this->totalPaginas) {
            $this->pagina = $pagina;
            $this->paginar();
        }
    }

    //OK
    public function paginar(){
        // Include soft-deleted rows in the total count
        $this->totalObjetos = $this->modeloString::withTrashed()->count();
        $this->totalPaginas = ceil($this->totalObjetos / $this->objetosPagina);
        if ($this->pagina > $this->totalPaginas) {
            $this->pagina = $this->totalPaginas;
        }
        // Bring both active and soft-deleted rows
        $this->objetosPaginados = $this->modeloString::withTrashed()->get()->forPage($this->pagina,$this->objetosPagina);
    }

    public function setEncabezados(){

    }

    public function mount($titulo, $modelo, $pagina, $objetosPagina)
    {
        $this->titulo = $titulo;
        $this->modelo = $modelo;

        $this->pagina = $pagina;
        $this->objetosPagina = $objetosPagina;
        
        $this->modeloString = 'App\\Models\\' . $modelo;
        $this->totalObjetos = $this->modeloString::count();
        $this->campos = $this->modeloString::camposTabla();
        
        $this->paginar();
    }

    public function render()
    {
        return view('livewire.tabla');
    }
}