<?php

namespace App\Livewire;

use Livewire\Component;

class OpcionSubmenu extends Component
{
    public $tituloOpcion;
    public $iconoOpcion;
    public $tituloAreaTrabajo;
    public $codigoAreaTrabajo;
    public $route; // optional named route string

    public function mount($tituloOpcion, $iconoOpcion, $tituloAreaTrabajo = null, $codigoAreaTrabajo = null, $route = null){
        $this->tituloOpcion = $tituloOpcion;
        $this->iconoOpcion = $iconoOpcion;
        $this->tituloAreaTrabajo = $tituloAreaTrabajo;
        $this->codigoAreaTrabajo = $codigoAreaTrabajo;
        $this->route = $route;
    }

    public function setAreaTrabajo(){
        if ($this->route) { return; }
        $this->dispatch('setAreaTrabajo', 
            tituloAreaTrabajo: $this->tituloAreaTrabajo, 
            codigoAreaTrabajo: $this->codigoAreaTrabajo)->to(Html::class);
    }
    
    public function render()
    {
        return view('livewire.opcion-submenu');
    }
}
