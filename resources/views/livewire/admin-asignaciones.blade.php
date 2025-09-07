<section class="content">
  <div class="container-fluid">
    <div class="card card-default">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Asignaciones de Docentes</h3>
        <a href="{{ url('/app') }}" class="btn btn-sm btn-secondary">Volver al panel</a>
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
            <select class="form-control" wire:model.change="aula_id">
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
            <div class="form-group">
              @foreach ($asignaturasDisponibles as $ag)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="{{ $ag['id'] }}" wire:model="asignatura_grado_ids">
                  <label class="form-check-label">{{ $ag['nombre'] }}</label>
                </div>
              @endforeach
            </div>
            <button class="btn btn-primary" wire:click="guardarAsignaciones" @disabled(empty($docente_id))>Guardar Asignaciones</button>
          </div>

          <div class="col-md-6">
            <h5>Alumnos del aula</h5>
            <ul class="list-group mb-3">
              @forelse ($alumnosAula as $al)
                <li class="list-group-item">{{ $al['nombre'] }}</li>
              @empty
                <li class="list-group-item">Sin alumnos.</li>
              @endforelse
            </ul>

            <h5>Mover alumnos sin aula a la aula seleccionada</h5>
            <div class="mb-2" style="max-height: 250px; overflow:auto; border:1px solid #ddd;">
              @foreach ($alumnosSinAula as $al)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="{{ $al['id'] }}" wire:model="alumnosSeleccionados">
                  <label class="form-check-label">{{ $al['nombre'] }}</label>
                </div>
              @endforeach
            </div>
            <button class="btn btn-success" wire:click="moverAlumnosAula" @disabled(empty($aula_id))>Mover a aula</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
