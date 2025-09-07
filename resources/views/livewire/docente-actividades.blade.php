<section class="content">
  <div class="container-fluid">
    <div class="card card-default">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Actividades</h3>
        <div class="card-tools">
          <a href="{{ route('docente.cursos') }}" class="btn btn-sm btn-secondary">Volver</a>
        </div>
      </div>
      <div class="card-body">
        @if ($mensaje)
          <div class="alert alert-warning">{{ $mensaje }}</div>
        @else
          @if (empty($actividades))
            <div class="alert alert-info">No hay actividades vinculadas para este curso todavía.</div>
          @else
            <div class="list-group">
              @foreach ($actividades as $ac)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <div class="fw-bold">{{ $ac['titulo'] }}</div>
                    <small class="text-muted">{{ $ac['inicio'] }} {{ $ac['fin'] ? '— '.$ac['fin'] : '' }}</small>
                  </div>
                  <div>
                    <button class="btn btn-primary btn-sm" wire:click="irCalificar({{ $ac['id'] }})">Calificar</button>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        @endif
      </div>
    </div>
  </div>
</section>
