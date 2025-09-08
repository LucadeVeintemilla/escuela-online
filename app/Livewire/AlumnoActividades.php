<?php

namespace App\Livewire;

use App\Models\Alumno;
use App\Models\Actividad;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AlumnoActividades extends Component
{
    public ?Alumno $alumno = null;
    public array $actividades = [];
    public string $mensaje = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) { $this->mensaje = 'No autenticado'; return; }
        // Empatar solo por user_id (correo puede no existir en alumnos)
        $this->alumno = Alumno::where('user_id', $user->id)->first();
        if (!$this->alumno) { $this->mensaje = 'Tu usuario no está vinculado a un Alumno.'; return; }
        if (!$this->alumno->aula_id) { $this->mensaje = 'No tienes aula asignada.'; return; }

        $this->actividades = Actividad::where('aula_id', $this->alumno->aula_id)
            ->orderByDesc('inicio')->orderByDesc('id')
            ->get(['id','actividad','inicio','fin'])
            ->map(function($a){
                return [
                    'id' => $a->id,
                    'titulo' => $a->actividad,
                    'inicio' => $a->inicio,
                    'fin' => $a->fin,
                ];
            })->toArray();

        if (empty($this->actividades)) {
            $this->mensaje = 'No hay actividades creadas para tu aula (aula_id: '.$this->alumno->aula_id.'). Si esperabas ver actividades, verifica que las actividades tengan aula_id = '.$this->alumno->aula_id.'.';
        }
    }

    public function ver($actividad_id)
    {
        return redirect()->route('alumno.actividad', ['actividad_id' => $actividad_id]);
    }

    public function render()
    {
        return view('livewire.alumno-actividades');
    }
}
