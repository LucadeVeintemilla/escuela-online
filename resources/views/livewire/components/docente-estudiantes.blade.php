<div class="card card-default">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">Mis Estudiantes</h3>
  </div>
  <div class="card-body">
    @if ($mensaje)
      <div class="alert alert-warning">{{ $mensaje }}</div>
    @endif

    <div class="row g-3 align-items-end">
      <div class="col-md-6">
        <label class="form-label">Curso (Aula)</label>
        <select class="form-control" wire:model="cursoSeleccionado" wire:change="$set('cursoSeleccionado', $event.target.value)" wire:key="select-aula" >
          @if (empty($cursos))
            <option value="">-- Sin cursos --</option>
          @else
            <option value="">-- Selecciona --</option>
            @foreach ($cursos as $c)
              <option value="{{ $c['id'] }}">{{ $c['nombre'] }}</option>
            @endforeach
          @endif
        </select>
      </div>
    </div>

    <hr />

    @if (!$cursoSeleccionado)
      <div class="alert alert-info mb-0">Selecciona un curso para ver sus estudiantes.</div>
    @elseif (empty($estudiantes))
      <div class="alert alert-info mb-0">No hay estudiantes para mostrar.</div>
    @else
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Alumno</th>
              <th>DNI</th>
              <th>Correo</th>
              <th>Género</th>
              <th>Registrado</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($estudiantes as $idx => $e)
              <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $e['nombre'] }}</td>
                <td>{{ $e['dni'] }}</td>
                <td>{{ $e['correo'] }}</td>
                <td>{{ $e['genero'] }}</td>
                <td>{{ $e['registrado'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
