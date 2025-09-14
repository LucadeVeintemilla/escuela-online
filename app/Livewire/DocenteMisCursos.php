<?php

namespace App\Livewire;

use App\Models\Docente;
use App\Models\Actividad;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DocenteMisCursos extends Component
{
    public ?Docente $docente = null;
    public array $cursos = [];
    protected array $actividadCountMap = [];
    public string $mensaje = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) { $this->mensaje = 'No autenticado'; return; }

        $this->docente = Docente::where('user_id', $user->id)->first() ?? Docente::where('correo', $user->email)->first();
        if (!$this->docente) { $this->mensaje = 'Tu usuario no está vinculado a un Docente.'; return; }

        $agrupado = [];
        $asigs = $this->docente->asignaturasGrado()->with(['asignatura','grado'])->withPivot('aula_id')->get();
        // Construir mapa de conteos de actividades por (aula_id, asignatura_grado_id)
        $conteos = Actividad::select('aula_id','asignatura_grado_id', \DB::raw('COUNT(*) as c'))
            ->where('docente_id', $this->docente->id)
            ->groupBy('aula_id','asignatura_grado_id')
            ->get();
        $this->actividadCountMap = [];
        foreach ($conteos as $row) {
            $key = ((int)$row->aula_id).'|'.((int)$row->asignatura_grado_id);
            $this->actividadCountMap[$key] = (int)$row->c;
        }
        foreach ($asigs as $ag) {
            $aulaId = (int)($ag->pivot->aula_id);
            if (!isset($agrupado[$aulaId])) {
                $aula = \App\Models\Aula::with(['grado','seccion'])->find($aulaId);
                $nombreAula = ($aula->grado->grado ?? 'Grado').' - '.($aula->seccion->seccion ?? 'Sección');
                $agrupado[$aulaId] = [
                    'aula_id' => $aulaId,
                    'aula_nombre' => $nombreAula,
                    'materias' => []
                ];
            }
            $key = $aulaId.'|'.$ag->id;
            $count = $this->actividadCountMap[$key] ?? 0;
            $agrupado[$aulaId]['materias'][] = [
                'asignatura_grado_id' => $ag->id,
                'nombre' => $ag->asignatura->asignatura ?? ('Asignatura #'.$ag->id)
                , 'actividades_count' => $count
            ];
        }
        $this->cursos = array_values($agrupado);
    }

    public function irActividades($aula_id, $asignatura_grado_id)
    {
        return redirect()->route('docente.actividades', ['aula_id' => $aula_id, 'asignatura_grado_id' => $asignatura_grado_id]);
    }

    public function irNuevaActividad($aula_id, $asignatura_grado_id)
    {
        return redirect()->route('docente.actividades', [
            'aula_id' => $aula_id,
            'asignatura_grado_id' => $asignatura_grado_id,
            'crear' => 1,
        ]);
    }

    public function render()
    {
        return view('livewire.docente-mis-cursos');
    }
}
