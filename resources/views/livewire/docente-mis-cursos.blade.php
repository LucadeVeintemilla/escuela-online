<section class="content">
  <div class="container-fluid">
    <div class="card card-default">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Mis Cursos</h3>
        <a href="{{ route('docente.dashboard') }}" class="btn btn-sm btn-secondary">Volver</a>
      </div>
      <div class="card-body">
        @if ($mensaje)
          <div class="alert alert-warning">{{ $mensaje }}</div>
        @else
          @forelse ($cursos as $curso)
            <div class="card mb-3">
              <div class="card-header"><strong>Aula:</strong> {{ $curso['aula_nombre'] }}</div>
              <div class="card-body">
                <div class="row">
                  @foreach ($curso['materias'] as $m)
                    <div class="col-md-4 mb-2">
                      <div class="d-flex align-items-center justify-content-between border p-2 rounded">
                        <div>
                          {{ $m['nombre'] }}
                          <span class="badge badge-pill badge-info ml-2">{{ $m['actividades_count'] ?? 0 }}</span>
                        </div>
                        @php $disabled = ($m['actividades_count'] ?? 0) === 0; @endphp
                        <button class="btn btn-primary btn-sm" @if($disabled) disabled title="Sin actividades" @endif wire:click="irActividades({{ $curso['aula_id'] }}, {{ $m['asignatura_grado_id'] }})">
                          Ver actividades
                        </button>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          @empty
            <div class="alert alert-info">No tienes cursos asignados todavía.</div>
          @endforelse
        @endif
      </div>
    </div>
  </div>
</section>
