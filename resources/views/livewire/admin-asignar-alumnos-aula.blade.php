<section class="content">
  <div class="container-fluid">
    <div class="card card-default">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Asignar Alumnos a Aula</h3>
      </div>
      <div class="card-body">
        @if ($flash)
          <div class="alert alert-info">{{ $flash }}</div>
        @endif

        <div class="row mb-3">
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
            <h5>Alumnos en el aula</h5>
            <div class="mb-2" style="max-height: 260px; overflow:auto; border:1px solid #ddd;">
              @forelse ($alumnosAula as $al)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="{{ $al['id'] }}" wire:model="seleccionQuitar">
                  <label class="form-check-label">{{ $al['nombre'] }}</label>
                </div>
              @empty
                <div class="p-2 text-muted">Sin alumnos.</div>
              @endforelse
            </div>
            <button class="btn btn-outline-danger" wire:click="quitarDelAula">Quitar seleccionados</button>
          </div>

          <div class="col-md-6">
            <h5>Alumnos sin aula</h5>
            <div class="mb-2" style="max-height: 260px; overflow:auto; border:1px solid #ddd;">
              @foreach ($alumnosSinAula as $al)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="{{ $al['id'] }}" wire:model="seleccionAgregar">
                  <label class="form-check-label">{{ $al['nombre'] }}</label>
                </div>
              @endforeach
            </div>
            <button class="btn btn-success" wire:click="agregarAlAula">Agregar al aula</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
