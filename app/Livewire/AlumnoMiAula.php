<?php

namespace App\Livewire;

use App\Models\Alumno;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AlumnoMiAula extends Component
{
    public ?Alumno $alumno = null;
    public ?array $aula = null; // ['grado' => ..., 'seccion' => ...]
    public string $mensaje = '';
    public array $notas = [];
    public array $asignaturas = [];

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) { $this->mensaje = 'No autenticado'; return; }

        $this->alumno = Alumno::with(['aula.grado', 'aula.seccion'])->where('user_id', $user->id)->first();
        if (!$this->alumno) { $this->mensaje = 'Tu usuario no está vinculado a un Alumno.'; return; }
        if (!$this->alumno->aula) { $this->mensaje = 'No tienes un aula asignada aún.'; return; }

        $this->aula = [
            'grado' => optional($this->alumno->aula->grado)->grado,
            'seccion' => optional($this->alumno->aula->seccion)->seccion,
        ];

        // Cargar asignaturas del grado del alumno
        $gradoId = $this->alumno->aula->grado_id;
        $this->asignaturas = DB::table('asignatura_grados as ag')
            ->join('asignaturas as a', 'a.id', '=', 'ag.asignatura_id')
            ->where('ag.grado_id', $gradoId)
            ->orderBy('a.asignatura')
            ->get(['a.id as id','a.asignatura as nombre'])
            ->map(fn($r) => ['id' => $r->id, 'nombre' => $r->nombre])
            ->toArray();

        // Cargar calificaciones del alumno en su aula
        $this->notas = DB::table('actividad_calificacion_alumnos as aca')
            ->join('actividads as act', 'act.id', '=', 'aca.actividad_id')
            ->join('calificacions as cal', 'cal.id', '=', 'aca.calificacion_id')
            ->where('aca.alumno_id', $this->alumno->id)
            ->where('act.aula_id', $this->alumno->aula_id)
            ->whereNull('aca.deleted_at')
            ->whereNull('act.deleted_at')
            ->select([
                'aca.id',
                'aca.actividad_id',
                'act.actividad as titulo',
                'act.inicio',
                'act.fin',
                'aca.calificacion_id',
                'cal.calificacion',
                'cal.abreviatura',
                'aca.observacion',
                'aca.created_at',
            ])
            ->orderByDesc('aca.id')
            ->get()
            ->map(function($r){
                return [
                    'id' => $r->id,
                    'actividad_id' => $r->actividad_id,
                    'titulo' => $r->titulo,
                    'calificacion' => $r->calificacion,
                    'abreviatura' => $r->abreviatura,
                    'observacion' => $r->observacion,
                    'fecha' => $r->created_at,
                ];
            })->toArray();
    }

    public function render()
    {
        return view('livewire.components.alumno-mi-aula');
    }
}
