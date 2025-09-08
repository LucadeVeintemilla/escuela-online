<div class="card card-default">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">Generador de reportes</h3>
  </div>
  <div class="card-body">
    @if ($mensaje)
      <div class="alert alert-warning">{{ $mensaje }}</div>
    @endif

    <div class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Curso (Aula)</label>
        <select class="form-control" wire:model="cursoSeleccionado">
          <option value="">-- Selecciona --</option>
          @foreach ($cursos as $c)
            <option value="{{ $c['id'] }}">{{ $c['nombre'] }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Asignatura</label>
        <select class="form-control" wire:model="asignaturaId" @disabled(empty($asignaturas))>
          <option value="">-- Todas --</option>
          @foreach ($asignaturas as $a)
            <option value="{{ $a['id'] }}">{{ $a['nombre'] }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Desde</label>
        <input type="date" class="form-control" wire:model.defer="filtros.desde" />
      </div>
      <div class="col-md-2">
        <label class="form-label">Hasta</label>
        <input type="date" class="form-control" wire:model.defer="filtros.hasta" />
      </div>
      <div class="col-md-1">
        <button class="btn btn-primary w-100" wire:click="generar" @disabled(empty($cursos))>
          <span wire:loading.remove wire:target="generar">Generar</span>
          <span wire:loading wire:target="generar">Generando...</span>
        </button>
      </div>
    </div>

    <div class="d-flex align-items-center gap-2 mt-3">
      <div class="btn-group" role="group" aria-label="Agrupación">
        <button type="button" class="btn btn-outline-secondary @if($agrupacion==='alumno') active @endif" wire:click="$set('agrupacion','alumno')">Agrupar por Alumno</button>
        <button type="button" class="btn btn-outline-secondary @if($agrupacion==='actividad') active @endif" wire:click="$set('agrupacion','actividad')">Agrupar por Actividad</button>
      </div>
      <div class="ms-auto">
        <button class="btn btn-outline-success" wire:click="exportCsv" @disabled(empty($resultados))>
          <i class="fas fa-file-csv"></i> Exportar CSV
        </button>
        <button class="btn btn-outline-danger" wire:click="exportPdf" @disabled(empty($resultados))>
          <i class="fas fa-file-pdf"></i> Exportar PDF
        </button>
      </div>
    </div>

    <hr />

    @if (empty($resultados))
      <div class="alert alert-info mb-0">Selecciona un curso y filtros para ver resultados.</div>
    @else
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Alumno</th>
              <th>Actividad</th>
              <th style="width:160px">Calificación</th>
              <th>Observación</th>
              <th style="width:160px">Fecha</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($resultados as $r)
              <tr>
                <td>{{ $r['alumno'] }}</td>
                <td>{{ $r['actividad'] }}</td>
                <td>{{ $r['calificacion'] }} ({{ $r['abreviatura'] }})</td>
                <td>{{ $r['observacion'] }}</td>
                <td>{{ $r['fecha'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if (!empty($promediosPorAlumno) || !empty($promediosPorActividad))
        <div class="row mt-3">
          @if (!empty($promediosPorAlumno))
            <div class="col-md-6">
              <div class="card card-body p-2">
                <h6 class="mb-2">Promedios por alumno</h6>
                <ul class="list-group list-group-flush">
                  @foreach ($promediosPorAlumno as $p)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      <span>{{ $p['alumno'] }}</span>
                      <span class="badge badge-primary">{{ $p['promedio'] }}</span>
                    </li>
                  @endforeach
                </ul>
              </div>
            </div>
          @endif
          @if (!empty($promediosPorActividad))
            <div class="col-md-6">
              <div class="card card-body p-2">
                <h6 class="mb-2">Promedios por actividad</h6>
                <ul class="list-group list-group-flush">
                  @foreach ($promediosPorActividad as $p)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      <span>{{ $p['actividad'] }}</span>
                      <span class="badge badge-primary">{{ $p['promedio'] }}</span>
                    </li>
                  @endforeach
                </ul>
              </div>
            </div>
          @endif
        </div>
      @endif
    @endif
  </div>
</div>
