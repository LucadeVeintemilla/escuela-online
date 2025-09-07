<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Docente;
use App\Models\Actividad;
use App\Models\Calificacion;
use App\Models\Alumno;

class DocenteCalificarActividad extends Component
{
    public ?Docente $docente = null;
    public ?Actividad $actividad = null;

    public array $alumnos = [];
    public array $calificaciones = [];

    // [alumno_id => calificacion_id]
    public array $notas = [];
    public array $observaciones = [];

    public string $mensaje = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) { $this->mensaje = 'No autenticado'; return; }

        $this->docente = Docente::where('user_id', $user->id)->first() ?? Docente::where('correo', $user->email)->first();
        if (!$this->docente) { $this->mensaje = 'Tu usuario no está vinculado a un Docente.'; return; }

        $actividadId = request()->integer('actividad_id');
        if (!$actividadId) { $this->mensaje = 'Actividad no especificada.'; return; }

        $this->actividad = Actividad::find($actividadId);
        if (!$this->actividad) { $this->mensaje = 'Actividad no encontrada.'; return; }
        if ($this->actividad->docente_id !== $this->docente->id) { $this->mensaje = 'No tienes permiso para calificar esta actividad.'; return; }
        if (!$this->actividad->aula_id) { $this->mensaje = 'La actividad no está vinculada a un aula.'; return; }

        $this->calificaciones = Calificacion::orderBy('id')->get()->toArray();
        $this->cargarAlumnosYNotas();
    }

    protected function cargarAlumnosYNotas(): void
    {
        $this->alumnos = Alumno::where('aula_id', $this->actividad->aula_id)
            ->orderBy('apellido_1')->orderBy('nombre_1')
            ->get(['id','nombre_1','nombre_2','apellido_1','apellido_2'])
            ->toArray();

        $this->notas = [];
        $this->observaciones = [];
        if (empty($this->alumnos)) return;

        $ids = array_map(fn($a) => $a['id'], $this->alumnos);
        $existentes = DB::table('actividad_calificacion_alumnos')
            ->where('actividad_id', $this->actividad->id)
            ->whereIn('alumno_id', $ids)
            ->get();
        foreach ($existentes as $row) {
            $this->notas[$row->alumno_id] = $row->calificacion_id;
            $this->observaciones[$row->alumno_id] = $row->observacion;
        }
    }

    public function guardar(): void
    {
        if (!$this->actividad) { return; }
        foreach ($this->alumnos as $alumno) {
            $alumnoId = $alumno['id'];
            $calificacionId = $this->notas[$alumnoId] ?? null;
            $obs = $this->observaciones[$alumnoId] ?? null;

            if ($calificacionId) {
                // upsert por (actividad_id, alumno_id)
                DB::table('actividad_calificacion_alumnos')->updateOrInsert(
                    [
                        'actividad_id' => $this->actividad->id,
                        'alumno_id' => $alumnoId,
                    ],
                    [
                        'calificacion_id' => $calificacionId,
                        'observacion' => $obs,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            } else {
                // si no hay calificación seleccionada, opcionalmente eliminar
                DB::table('actividad_calificacion_alumnos')
                    ->where('actividad_id', $this->actividad->id)
                    ->where('alumno_id', $alumnoId)
                    ->delete();
            }
        }

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Calificaciones guardadas.']);
        $this->cargarAlumnosYNotas();
    }

    public function render()
    {
        return view('livewire.docente-calificar-actividad');
    }
}
