<section class="content">
  <div class="container-fluid">
    <div class="card card-default">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Asignar Materias a Docente</h3>
      </div>
      <div class="card-body">
        @if ($flash)
          <div class="alert alert-info">{{ $flash }}</div>
        @endif

        <div class="row mb-3">
          <div class="col-md-6">
            <label>Docente</label>
            <select class="form-control" wire:model.change="docente_id" wire:change="docenteChanged">
              <option value="">-- Seleccionar --</option>
              @foreach ($docentes as $d)
                <option value="{{ $d['id'] }}">{{ $d['nombre'] }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label>Aula</label>
            <select class="form-control" wire:model.change="aula_id" wire:change="aulaChanged">
              <option value="">-- Seleccionar --</option>
              @foreach ($aulas as $a)
                <option value="{{ $a['id'] }}">{{ $a['nombre'] }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <h5>Materias del grado del aula</h5>
            <div class="form-group" style="max-height: 300px; overflow:auto; border:1px solid #eee; padding:8px;">
              @forelse ($asignaturasDisponibles as $ag)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="{{ $ag['id'] }}" wire:model="asignatura_grado_ids">
                  <label class="form-check-label">{{ $ag['nombre'] }}</label>
                </div>
              @empty
                <div class="text-muted">No hay materias para el grado de esta aula.</div>
              @endforelse
            </div>
            <button class="btn btn-primary" wire:click="guardar">Guardar</button>
          </div>

          <div class="col-md-6">
            <div class="alert alert-secondary">
              Selecciona un Docente y un Aula para listar y asignar las materias (cursos) correspondientes a ese grado. Estas materias quedarán ligadas al docente para esa aula específica.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
