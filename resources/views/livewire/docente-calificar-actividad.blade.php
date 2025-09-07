<section class="content">
  <div class="container-fluid">
    <div class="card card-default">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Calificar actividad</h3>
        <div class="card-tools">
          <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">Volver</a>
        </div>
      </div>
      <div class="card-body">
        @if ($mensaje)
          <div class="alert alert-warning">{{ $mensaje }}</div>
        @else
          <div class="mb-3">
            <strong>Actividad:</strong> {{ $actividad->actividad ?? '-' }}<br>
            <small class="text-muted">{{ $actividad->inicio }} {{ $actividad->fin ? '— '.$actividad->fin : '' }}</small>
          </div>

          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Alumno</th>
                  <th style="width:220px">Calificación</th>
                  <th>Observación</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($alumnos as $al)
                  <tr>
                    <td>{{ $al['apellido_1'] }} {{ $al['apellido_2'] }}, {{ $al['nombre_1'] }} {{ $al['nombre_2'] }}</td>
                    <td>
                      <select class="form-control" wire:model="notas.{{ $al['id'] }}">
                        <option value="">--</option>
                        @foreach ($calificaciones as $c)
                          <option value="{{ $c['id'] }}">{{ $c['calificacion'] }} ({{ $c['abreviatura'] }})</option>
                        @endforeach
                      </select>
                    </td>
                    <td>
                      <input type="text" class="form-control" wire:model.defer="observaciones.{{ $al['id'] }}" placeholder="Opcional" />
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="3">No hay alumnos en el aula asociada.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="mt-3">
            <button class="btn btn-primary" wire:click="guardar" @disabled(empty($alumnos))>Guardar</button>
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
