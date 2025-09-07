<?php

namespace App\Livewire;

use App\Models\Actividad;
use App\Models\AsignaturaGrado;
use App\Models\Aula;
use App\Models\Docente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AdminActividadesCurriculares extends Component
{
    public array $docentes = [];
    public array $aulas = [];
    public array $asignaturas = [];

    public ?int $docente_id = null;
    public ?int $aula_id = null;
    public ?int $asignatura_grado_id = null;

    public array $actividades = [];
    public array $catalogoActividades = [];
    public ?int $actividad_id = null; // actividad base creada en Evaluación > Actividades

    public string $flash = '';

    public function mount(): void
    {
        $this->docentes = Docente::orderBy('apellido_1')->orderBy('nombre_1')
            ->get(['id','nombre_1','nombre_2','apellido_1','apellido_2'])
            ->map(fn($d) => [
                'id' => $d->id,
                'nombre' => trim($d->apellido_1.' '.$d->apellido_2.', '.$d->nombre_1.' '.$d->nombre_2),
            ])->toArray();

        $this->aulas = Aula::with(['grado','seccion'])->orderBy('grado_id')->orderBy('seccion_id')
            ->get()->map(fn($a) => [
                'id' => $a->id,
                'nombre' => ($a->grado->grado ?? 'Grado').' - '.($a->seccion->seccion ?? 'Sección')
            ])->toArray();

        $this->cargarAsignaturas();
        $this->cargarActividades();
        $this->cargarCatalogo();
    }

    public function updatedDocenteId($value): void
    {
        $this->cargarActividades();
        $this->cargarCatalogo();
    }

    public function updatedAulaId($value): void
    {
        $this->cargarAsignaturas();
        $this->cargarActividades();
        $this->cargarCatalogo();
    }

    public function updatedAsignaturaGradoId($value): void
    {
        $this->cargarActividades();
        $this->cargarCatalogo();
    }

    public function aulaChanged(): void { $this->updatedAulaId($this->aula_id); }

    protected function cargarAsignaturas(): void
    {
        $this->asignaturas = [];
        $this->asignatura_grado_id = null;
        if (!$this->aula_id) return;
        $aula = Aula::with('grado')->find($this->aula_id);
        if (!$aula) return;
        $this->asignaturas = AsignaturaGrado::with('asignatura')
            ->where('grado_id', $aula->grado_id)
            ->orderBy('id')
            ->get()
            ->map(fn($ag) => ['id' => $ag->id, 'nombre' => $ag->asignatura->asignatura ?? ('Asignatura #'.$ag->id)])
            ->toArray();
    }

    protected function cargarActividades(): void
    {
        $q = Actividad::query()->with(['docente','aula.grado','aula.seccion','asignaturaGrado.asignatura']);
        if ($this->docente_id) $q->where('docente_id', $this->docente_id);
        if ($this->aula_id) $q->where('aula_id', $this->aula_id);
        if ($this->asignatura_grado_id) $q->where('asignatura_grado_id', $this->asignatura_grado_id);
        $this->actividades = $q->orderByDesc('id')->limit(100)->get()->map(function($ac){
            return [
                'id' => $ac->id,
                'actividad' => $ac->actividad,
                'docente' => $ac->docente ? ($ac->docente->apellido_1.' '.$ac->docente->apellido_2.', '.$ac->docente->nombre_1) : '-',
                'aula' => $ac->aula ? (($ac->aula->grado->grado ?? 'Grado').' - '.($ac->aula->seccion->seccion ?? 'Sección')) : '-',
                'asignatura' => $ac->asignaturaGrado && $ac->asignaturaGrado->asignatura ? $ac->asignaturaGrado->asignatura->asignatura : '-',
                'inicio' => $ac->inicio,
                'fin' => $ac->fin,
                'activa' => (bool)$ac->activa,
            ];
        })->toArray();
    }

    protected function cargarCatalogo(): void
    {
        // Catálogo de actividades (CRUD existente en Evaluación > Actividades):
        // listamos sin vínculos o con independencia de filtros, máx 200
        $this->catalogoActividades = Actividad::orderByDesc('id')
            ->limit(200)
            ->get(['id','actividad','inicio','fin'])
            ->map(fn($a) => [
                'id' => $a->id,
                'label' => $a->actividad.($a->inicio ? ' ('.$a->inicio.')' : ''),
            ])->toArray();
    }

    public function vincularActividad(): void
    {
        $this->flash = '';
        if (!$this->actividad_id) { $this->flash = 'Selecciona una actividad del catálogo.'; return; }
        if (!$this->docente_id) { $this->flash = 'Selecciona un docente.'; return; }
        if (!$this->aula_id) { $this->flash = 'Selecciona un aula.'; return; }
        if (!$this->asignatura_grado_id) { $this->flash = 'Selecciona una asignatura.'; return; }

        // Validar pivote (docente + asignatura_grado + aula)
        $existePivot = DB::table('docente_asignatura_grado')
            ->where('docente_id', $this->docente_id)
            ->where('asignatura_grado_id', $this->asignatura_grado_id)
            ->where('aula_id', $this->aula_id)
            ->exists();
        if (!$existePivot) { $this->flash = 'El docente no está asignado a esa materia para el aula seleccionada.'; return; }

        $ac = Actividad::find($this->actividad_id);
        if (!$ac) { $this->flash = 'Actividad no encontrada.'; return; }

        // Vincular
        $ac->docente_id = $this->docente_id;
        $ac->aula_id = $this->aula_id;
        $ac->asignatura_grado_id = $this->asignatura_grado_id;
        if (!$ac->usuario_id) { $ac->usuario_id = Auth::id(); }
        $ac->save();

        $this->flash = 'Actividad vinculada correctamente.';
        $this->cargarActividades();
    }

    public function desvincularActividad(int $actividadId): void
    {
        $this->flash = '';
        $ac = Actividad::find($actividadId);
        if (!$ac) { $this->flash = 'Actividad no encontrada.'; return; }
        // Desvincular sin eliminar del catálogo
        $ac->docente_id = null;
        $ac->aula_id = null;
        $ac->asignatura_grado_id = null;
        $ac->save();
        $this->flash = 'Actividad desvinculada.';
        $this->cargarActividades();
    }

    public function render()
    {
        return view('livewire.admin-actividades-curriculares');
    }
}
