<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Livewire\Component;

class DocenteReportes extends Component
{
    public array $cursos = [];
    public ?int $cursoSeleccionado = null; // aula_id o asignatura_grado_id según el modelo
    public array $asignaturas = [];
    public ?int $asignaturaId = null;
    public array $filtros = [
        'desde' => null,
        'hasta' => null,
    ];
    public array $resultados = [];
    public array $promediosPorAlumno = [];
    public array $promediosPorActividad = [];
    public string $agrupacion = 'alumno'; // 'alumno' | 'actividad'
    public string $mensaje = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) { $this->mensaje = 'No autenticado'; return; }

        // Obtener docente_id del usuario autenticado
        $docenteId = DB::table('docentes')->where('user_id', $user->id)->value('id');
        if (!$docenteId) { $this->mensaje = 'Tu usuario no está vinculado a un Docente.'; return; }

        // Cargar cursos del docente: aulas donde el docente tiene asignación
        $rows = DB::table('docente_asignatura_grado as dag')
            ->join('aulas as au', 'au.id', '=', 'dag.aula_id')
            ->join('grados as g', 'g.id', '=', 'au.grado_id')
            ->join('seccions as s', 's.id', '=', 'au.seccion_id')
            ->where('dag.docente_id', $docenteId)
            ->select(['au.id as id', DB::raw("CONCAT(g.grado, ' - ', s.seccion) as nombre")])
            ->distinct()
            ->orderBy('nombre')
            ->get();
        $this->cursos = $rows->map(fn($r) => ['id' => $r->id, 'nombre' => $r->nombre])->toArray();
        if (empty($this->cursos)) {
            $this->mensaje = 'No tienes cursos asignados.';
        }
        // Auto-seleccionar el primer curso para facilitar la generación
        if (!$this->mensaje && empty($this->cursoSeleccionado) && !empty($this->cursos)) {
            $this->cursoSeleccionado = $this->cursos[0]['id'];
            $this->updatedCursoSeleccionado();
        }
    }

    public function updatedCursoSeleccionado(): void
    {
        $this->asignaturaId = null;
        $this->resultados = [];
        $this->promediosPorAlumno = [];
        $this->promediosPorActividad = [];
        if (!$this->cursoSeleccionado) { $this->asignaturas = []; return; }

        // Asignaturas del docente en ese curso (aula)
        $this->asignaturas = DB::table('docente_asignatura_grado as dag')
            ->join('asignatura_grados as ag', 'ag.id', '=', 'dag.asignatura_grado_id')
            ->join('asignaturas as a', 'a.id', '=', 'ag.asignatura_id')
            ->where('dag.aula_id', $this->cursoSeleccionado)
            ->select(['a.id as id', 'a.asignatura as nombre'])
            ->distinct()
            ->orderBy('nombre')
            ->get()->map(fn($r) => ['id' => $r->id, 'nombre' => $r->nombre])->toArray();
    }

    public function generar(): void
    {
        if (!$this->cursoSeleccionado) { $this->mensaje = 'Selecciona un curso.'; return; }
        $desde = $this->filtros['desde'] ? date('Y-m-d 00:00:00', strtotime($this->filtros['desde'])) : null;
        $hasta = $this->filtros['hasta'] ? date('Y-m-d 23:59:59', strtotime($this->filtros['hasta'])) : null;

        $q = DB::table('actividad_calificacion_alumnos as aca')
            ->join('actividads as act', 'act.id', '=', 'aca.actividad_id')
            ->leftJoin('asignatura_grados as ag', 'ag.id', '=', 'act.asignatura_grado_id')
            ->leftJoin('asignaturas as asig', 'asig.id', '=', 'ag.asignatura_id')
            ->join('alumnos as al', 'al.id', '=', 'aca.alumno_id')
            ->join('calificacions as cal', 'cal.id', '=', 'aca.calificacion_id')
            ->where('act.aula_id', $this->cursoSeleccionado)
            ->whereNull('aca.deleted_at')
            ->whereNull('act.deleted_at');
        if ($desde) $q->where('aca.created_at', '>=', $desde);
        if ($hasta) $q->where('aca.created_at', '<=', $hasta);
        if ($this->asignaturaId) $q->where('asig.id', $this->asignaturaId);

        $rows = $q->select([
                'al.id as alumno_id', DB::raw("CONCAT(al.apellido_1,' ',al.apellido_2,', ',al.nombre_1,' ',al.nombre_2) as alumno"),
                'act.id as actividad_id', 'act.actividad as actividad',
                'asig.id as asignatura_id', 'asig.asignatura as asignatura',
                'cal.id as calificacion_id', 'cal.calificacion', 'cal.abreviatura',
                'aca.observacion', 'aca.created_at as fecha'
            ])->orderBy('alumno')->orderBy('actividad_id')->get();

        $this->resultados = $rows->map(function($r){
            return [
                'alumno' => $r->alumno,
                'actividad' => $r->actividad,
                'asignatura' => $r->asignatura,
                'calificacion' => $r->calificacion,
                'abreviatura' => $r->abreviatura,
                'observacion' => $r->observacion,
                'fecha' => $r->fecha,
            ];
        })->toArray();

        $this->calcularPromedios();
    }

    protected function calcularPromedios(): void
    {
        // Nota: asumimos que calificacion es un valor numérico interpretable; si no, usar otra tabla de pesos.
        $porAlumno = [];
        $porActividad = [];
        foreach ($this->resultados as $r) {
            $al = $r['alumno'];
            $ac = $r['actividad'];
            $val = is_numeric($r['calificacion']) ? (float)$r['calificacion'] : null;
            if ($val !== null) {
                $porAlumno[$al]['s'] = ($porAlumno[$al]['s'] ?? 0) + $val;
                $porAlumno[$al]['n'] = ($porAlumno[$al]['n'] ?? 0) + 1;
                $porActividad[$ac]['s'] = ($porActividad[$ac]['s'] ?? 0) + $val;
                $porActividad[$ac]['n'] = ($porActividad[$ac]['n'] ?? 0) + 1;
            }
        }
        $this->promediosPorAlumno = [];
        foreach ($porAlumno as $k => $v) { $this->promediosPorAlumno[] = ['alumno' => $k, 'promedio' => round($v['s'] / max(1,$v['n']), 2)]; }
        $this->promediosPorActividad = [];
        foreach ($porActividad as $k => $v) { $this->promediosPorActividad[] = ['actividad' => $k, 'promedio' => round($v['s'] / max(1,$v['n']), 2)]; }
    }

    public function exportCsv()
    {
        $filename = 'reporte_calificaciones_'.date('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];
        $rows = $this->resultados;
        return Response::stream(function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Alumno','Asignatura','Actividad','Calificación','Abrev.','Observación','Fecha']);
            foreach ($rows as $r) {
                fputcsv($out, [$r['alumno'], $r['asignatura'], $r['actividad'], $r['calificacion'], $r['abreviatura'], $r['observacion'], $r['fecha']]);
            }
            fclose($out);
        }, 200, $headers);
    }

    public function exportPdf()
    {
        if (!class_exists(\Dompdf\Dompdf::class)) {
            $this->mensaje = 'Para exportar a PDF, instala Dompdf: composer require barryvdh/laravel-dompdf';
            return null;
        }
        $html = view('livewire.exports.reporte-pdf', [
            'resultados' => $this->resultados,
            'curso' => collect($this->cursos)->firstWhere('id', $this->cursoSeleccionado)['nombre'] ?? '-',
            'asignatura' => collect($this->asignaturas)->firstWhere('id', $this->asignaturaId)['nombre'] ?? 'Todas',
            'desde' => $this->filtros['desde'],
            'hasta' => $this->filtros['hasta'],
        ])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();

        $filename = 'reporte_calificaciones_'.date('Ymd_His').'.pdf';
        return Response::streamDownload(function() use ($output) {
            echo $output;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function render()
    {
        return view('livewire.components.docente-reportes');
    }
}
