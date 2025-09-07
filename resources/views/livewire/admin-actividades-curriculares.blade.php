<section class="content">
  <div class="container-fluid">
    <div class="card card-default">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Actividades curriculares</h3>
      </div>
      <div class="card-body">
        @if ($flash)
          <div class="alert alert-info">{{ $flash }}</div>
        @endif

        <div class="row mb-3">
          <div class="col-md-4">
            <label>Docente</label>
            <select class="form-control" wire:model.change="docente_id">
              <option value="">-- Seleccionar --</option>
              @foreach ($docentes as $d)
                <option value="{{ $d['id'] }}">{{ $d['nombre'] }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label>Aula</label>
            <select class="form-control" wire:model.change="aula_id" wire:change="aulaChanged">
              <option value="">-- Seleccionar --</option>
              @foreach ($aulas as $a)
                <option value="{{ $a['id'] }}">{{ $a['nombre'] }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label>Asignatura</label>
            <select class="form-control" wire:model.change="asignatura_grado_id">
              <option value="">-- Seleccionar --</option>
              @foreach ($asignaturas as $ag)
                <option value="{{ $ag['id'] }}">{{ $ag['nombre'] }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-header"><strong>Vincular actividad del catálogo</strong></div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-8 mb-2">
                <label>Actividad del catálogo</label>
                <select class="form-control" wire:model.change="actividad_id">
                  <option value="">-- Seleccionar --</option>
                  @foreach ($catalogoActividades as $item)
                    <option value="{{ $item['id'] }}">{{ $item['label'] }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100" wire:click="vincularActividad">Vincular actividad</button>
              </div>
            </div>
            <small class="text-muted">El catálogo proviene de Evaluación → Actividades (CRUD existente). Aquí solo se vinculan con Docente + Aula + Asignatura (curso).</small>
          </div>
        </div>

        <h5>Actividades</h5>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Título</th>
                <th>Docente</th>
                <th>Aula</th>
                <th>Asignatura</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Activa</th>
                <th style="width:120px">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($actividades as $ac)
                <tr>
                  <td>{{ $ac['id'] }}</td>
                  <td>{{ $ac['actividad'] }}</td>
                  <td>{{ $ac['docente'] }}</td>
                  <td>{{ $ac['aula'] }}</td>
                  <td>{{ $ac['asignatura'] }}</td>
                  <td>{{ $ac['inicio'] }}</td>
                  <td>{{ $ac['fin'] }}</td>
                  <td>
                    @if ($ac['activa'])
                      <span class="badge badge-success">Sí</span>
                    @else
                      <span class="badge badge-secondary">No</span>
                    @endif
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Desvincular esta actividad?')" wire:click.prevent="desvincularActividad({{ $ac['id'] }})">Desvincular</button>
                  </td>
                </tr>
              @empty
                <tr><td colspan="8" class="text-muted">Sin actividades.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
