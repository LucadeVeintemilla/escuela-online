<?php

namespace App\Livewire;

use App\Models\AsignaturaGrado;
use App\Models\Aula;
use App\Models\Docente;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AdminAsignarMateriasDocente extends Component
{
    public array $docentes = [];
    public array $aulas = [];

    public ?int $docente_id = null;
    public ?int $aula_id = null;

    public array $asignaturasDisponibles = [];
    public array $asignatura_grado_ids = [];

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

    public function updatedDocenteId($value): void
    {
        // Si el docente tiene aula, preseleccionarla
        $doc = $this->docenteSeleccionado();
        $this->aula_id = $doc?->aula_id;
        // Limpiar selección temporal para evitar arrastre entre docentes
        $this->asignatura_grado_ids = [];
        $this->cargarAsignaturasDisponibles();
        $this->cargarAsignadas();
    }

    public function updatedAulaId($value): void
    {
        // Limpiar selección temporal para evitar arrastre entre aulas
        $this->asignatura_grado_ids = [];
        $this->cargarAsignaturasDisponibles();
        $this->cargarAsignadas();
    }

    // Handlers explícitos por si el navegador no envía el update inmediato
    public function docenteChanged(): void
    {
        $this->updatedDocenteId($this->docente_id);
    }

    public function aulaChanged(): void
    {
        $this->updatedAulaId($this->aula_id);
    }

    protected function cargarAsignaturasDisponibles(): void
    {
        $this->asignaturasDisponibles = [];
        if (!$this->aula_id) return;
        $aula = Aula::with('grado')->find($this->aula_id);
        if (!$aula) return;
        $this->asignaturasDisponibles = AsignaturaGrado::with('asignatura')
            ->where('grado_id', $aula->grado_id)
            ->orderBy('id')
            ->get()
            ->map(fn($ag) => ['id' => $ag->id, 'nombre' => $ag->asignatura->asignatura ?? ('Asignatura #'.$ag->id)])
            ->toArray();
    }

    protected function cargarAsignadas(): void
    {
        // Reiniciar selección antes de cargar
        $this->asignatura_grado_ids = [];
        $doc = $this->docenteSeleccionado();
        if (!$doc || !$this->aula_id) return;

        // Cargar solo las asignaturas asignadas al docente para este aula específico
        $ids = $doc->asignaturasGrado()
            ->wherePivot('aula_id', $this->aula_id)
            ->pluck('asignatura_grados.id')
            ->all();
        // Normalizar a strings para que coincidan con los values del checkbox (HTML es string)
        $this->asignatura_grado_ids = array_map(fn($v) => (string)$v, $ids);
    }

    public function guardar(): void
    {
        $docente = $this->docenteSeleccionado();
        if (!$docente) { $this->flash = 'Selecciona un docente.'; return; }
        if (!$this->aula_id) { $this->flash = 'Selecciona un aula.'; return; }

        $ids = array_filter($this->asignatura_grado_ids ?? [], fn($v) => $v !== null && $v !== '');
        // Normalizar seleccionados a enteros para comparar correctamente
        $idsInt = array_map(fn($v) => (int)$v, $ids);

        // Sincronizar solo para este aula: eliminamos vínculos anteriores de ese aula y agregamos los nuevos
        $actuales = $docente->asignaturasGrado()->wherePivot('aula_id', $this->aula_id)->pluck('asignatura_grados.id')->all();
        $actualesInt = array_map(fn($v) => (int)$v, $actuales);
        $quitar = array_diff($actualesInt, $idsInt);
        if (!empty($quitar)) {
            // Eliminar en pivote acotando por docente + asignatura_grado + aula_id
            DB::table('docente_asignatura_grado')
                ->where('docente_id', $docente->id)
                ->whereIn('asignatura_grado_id', $quitar)
                ->where('aula_id', $this->aula_id)
                ->delete();
        }
        // Adjuntar/actualizar seleccionadas con aula_id
        $payload = [];
        foreach ($idsInt as $id) { $payload[$id] = ['aula_id' => $this->aula_id]; }
        // syncWithoutDetaching para agregar faltantes, y upsert manual para asegurar aula_id correcto
        $docente->asignaturasGrado()->syncWithoutDetaching($payload);
        
        $this->flash = 'Materias asignadas al docente para el aula seleccionada.';
        // Refrescar listas y selección
        $this->cargarAsignaturasDisponibles();
        $this->cargarAsignadas();
    }

    protected function docenteSeleccionado(): ?Docente
    {
        return $this->docente_id ? Docente::find($this->docente_id) : null;
    }

    public function render()
    {
        return view('livewire.admin-asignar-materias-docente');
    }
}
