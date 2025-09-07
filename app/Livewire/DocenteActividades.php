<?php

namespace App\Livewire;

use App\Models\Actividad;
use App\Models\Docente;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DocenteActividades extends Component
{
    public ?Docente $docente = null;
    public ?int $aula_id = null;
    public ?int $asignatura_grado_id = null;

    public array $actividades = [];
    public string $mensaje = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) { $this->mensaje = 'No autenticado'; return; }
        $this->docente = Docente::where('user_id', $user->id)->first() ?? Docente::where('correo', $user->email)->first();
        if (!$this->docente) { $this->mensaje = 'Tu usuario no está vinculado a un Docente.'; return; }

        $this->aula_id = request()->integer('aula_id');
        $this->asignatura_grado_id = request()->integer('asignatura_grado_id');

        $this->cargarActividades();
    }

    protected function cargarActividades(): void
    {
        if (!$this->aula_id || !$this->asignatura_grado_id) { $this->actividades = []; return; }
        $this->actividades = Actividad::where('docente_id', $this->docente->id)
            ->where('aula_id', $this->aula_id)
            ->where('asignatura_grado_id', $this->asignatura_grado_id)
            ->orderByDesc('inicio')->orderByDesc('id')
            ->get(['id','actividad','inicio','fin','activa'])
            ->map(fn($a) => [
                'id' => $a->id,
                'titulo' => $a->actividad,
                'inicio' => $a->inicio,
                'fin' => $a->fin,
                'activa' => (bool)$a->activa,
            ])->toArray();
    }

    public function irCalificar($actividad_id)
    {
        return redirect()->route('docente.actividad.calificar', ['actividad_id' => $actividad_id]);
    }

    public function render()
    {
        return view('livewire.docente-actividades');
    }
}
