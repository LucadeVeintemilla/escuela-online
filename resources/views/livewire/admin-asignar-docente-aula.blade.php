<section class="content">
  <div class="container-fluid">
    <div class="card card-default">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Asignar Docente a Aula</h3>
      </div>
      <div class="card-body">
        @if ($flash)
          <div class="alert alert-info">{{ $flash }}</div>
        @endif

        <div class="row mb-3">
          <div class="col-md-6">
            <label>Docente</label>
            <select class="form-control" wire:model="docente_id">
              <option value="">-- Seleccionar --</option>
              @foreach ($docentes as $d)
                <option value="{{ $d['id'] }}">{{ $d['nombre'] }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label>Aula</label>
            <select class="form-control" wire:model="aula_id">
              <option value="">-- Seleccionar --</option>
              @foreach ($aulas as $a)
                <option value="{{ $a['id'] }}">{{ $a['nombre'] }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <button class="btn btn-primary" wire:click="guardar">Guardar</button>
      </div>
    </div>
  </div>
</section>
