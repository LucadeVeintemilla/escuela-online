<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DocenteEstudiantes extends Component
{
    public array $cursos = [];
    public ?int $cursoSeleccionado = null; // aula_id
    public array $estudiantes = [];
    public string $mensaje = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) { $this->mensaje = 'No autenticado'; return; }

        // Obtener docente_id del usuario autenticado
        $docenteId = DB::table('docentes')->where('user_id', $user->id)->value('id');
        if (!$docenteId) { $this->mensaje = 'Tu usuario no está vinculado a un Docente.'; return; }

        // Cargar cursos (aulas) del docente desde asignaciones
        $rows = DB::table('docente_asignatura_grado as dag')
            ->join('aulas as au', 'au.id', '=', 'dag.aula_id')
            ->join('grados as g', 'g.id', '=', 'au.grado_id')
            ->join('seccions as s', 's.id', '=', 'au.seccion_id')
            ->where('dag.docente_id', $docenteId)
            ->select(['au.id as id', DB::raw("CONCAT(g.grado, ' - ', s.seccion) as nombre")])
            ->distinct()
            ->orderBy('nombre')
            ->get();
        $this->cursos = $rows->map(fn($r) => ['id' => (int)$r->id, 'nombre' => $r->nombre])->toArray();
        if (empty($this->cursos)) {
            $this->mensaje = 'No tienes cursos asignados.';
            return;
        }
        // Auto-seleccionar el primer curso y cargar estudiantes
        if (empty($this->cursoSeleccionado)) {
            $this->cursoSeleccionado = (int)$this->cursos[0]['id'];
            $this->updatedCursoSeleccionado();
        }
    }

    public function updated($property): void
    {
        if ($property === 'cursoSeleccionado') {
            // Asegurar entero
            $this->cursoSeleccionado = $this->cursoSeleccionado ? (int)$this->cursoSeleccionado : null;
            $this->updatedCursoSeleccionado();
        }
    }

    public function updatedCursoSeleccionado(): void
    {
        $this->estudiantes = [];
        if (!$this->cursoSeleccionado) { return; }

        // Listado de alumnos del aula seleccionada
        $rows = DB::table('alumnos as al')
            ->leftJoin('users as u', 'u.id', '=', 'al.user_id')
            ->leftJoin('generos as gen', 'gen.id', '=', 'al.genero_id')
            ->where('al.aula_id', (int)$this->cursoSeleccionado)
            ->whereNull('al.deleted_at')
            ->select([
                'al.id as id',
                DB::raw("CONCAT(al.apellido_1,' ',al.apellido_2,', ',al.nombre_1,' ',al.nombre_2) as nombre_completo"),
                'al.dni',
                'u.email as correo',
                'gen.genero as genero',
                'al.created_at as registrado'
            ])
            ->orderBy('apellido_1')
            ->orderBy('apellido_2')
            ->orderBy('nombre_1')
            ->get();

        $this->estudiantes = $rows->map(function($r){
            return [
                'id' => (int)$r->id,
                'nombre' => $r->nombre_completo,
                'dni' => $r->dni,
                'correo' => $r->correo,
                'genero' => $r->genero,
                'registrado' => $r->registrado,
            ];
        })->toArray();

        // Avisar al navegador para depuración (Livewire v3 emite evento del navegador por defecto)
        $this->dispatch(
            'docente-estudiantes:curso-seleccionado',
            cursoId: (int)$this->cursoSeleccionado,
            estudiantes: count($this->estudiantes)
        );
    }

    public function render()
    {
        return view('livewire.components.docente-estudiantes');
    }
}
