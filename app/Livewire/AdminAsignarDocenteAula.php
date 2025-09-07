<?php

namespace App\Livewire;

use App\Models\Aula;
use App\Models\Docente;
use Livewire\Component;

class AdminAsignarDocenteAula extends Component
{
    public array $docentes = [];
    public array $aulas = [];

    public ?int $docente_id = null;
    public ?int $aula_id = null;

    public string $flash = '';

    public function mount(): void
    {
        $this->docentes = Docente::orderBy('apellido_1')->orderBy('nombre_1')
            ->get(['id','nombre_1','nombre_2','apellido_1','apellido_2','aula_id'])
            ->map(fn($d) => [
                'id' => $d->id,
                'nombre' => trim($d->apellido_1.' '.$d->apellido_2.', '.$d->nombre_1.' '.$d->nombre_2),
                'aula_id' => $d->aula_id,
            ])->toArray();

        $this->aulas = Aula::with(['grado','seccion'])->orderBy('grado_id')->orderBy('seccion_id')
            ->get()->map(fn($a) => [
                'id' => $a->id,
                'nombre' => ($a->grado->grado ?? 'Grado').' - '.($a->seccion->seccion ?? 'Sección')
            ])->toArray();
    }

    public function guardar(): void
    {
        if (!$this->docente_id || !$this->aula_id) { $this->flash = 'Selecciona docente y aula.'; return; }
        $docente = Docente::find($this->docente_id);
        if (!$docente) { $this->flash = 'Docente inválido.'; return; }
        $docente->aula_id = $this->aula_id;
        $docente->save();
        $this->flash = 'Docente asignado al aula correctamente.';
        $this->mount();
    }

    public function render()
    {
        return view('livewire.admin-asignar-docente-aula');
    }
}
