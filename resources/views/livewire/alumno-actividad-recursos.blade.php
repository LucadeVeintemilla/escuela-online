<section class="content">
  <div class="container-fluid">
    <div class="card card-default">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Actividad: {{ $actividad->actividad ?? 'Sin título' }}</h3>
      </div>
      <div class="card-body">
        @if ($mensaje)
          <div class="alert alert-warning">{{ $mensaje }}</div>
        @else
          <form wire:submit.prevent="subir">
            <div class="mb-3">
              <label class="form-label">Título (opcional)</label>
              <input type="text" class="form-control" wire:model.defer="titulo" placeholder="Ej. Mi archivo de trabajo" />
            </div>
            <div class="mb-3">
              <label class="form-label">Archivo</label>
              <input type="file" class="form-control" wire:model="archivo" />
              @error('archivo') <div class="text-danger small">{{ $message }}</div> @enderror
              <div class="small text-muted mt-1" wire:loading wire:target="archivo">Cargando archivo...</div>
            </div>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="archivo,subir">
              <span wire:loading.remove wire:target="archivo,subir">Subir recurso</span>
              <span wire:loading wire:target="archivo,subir">Subiendo...</span>
            </button>
          </form>

          <script>
            (function(){
              console.log('[AlumnoActividadRecursos] Debug script loaded');
              function onEvent(name, payload){
                console.log('[AlumnoActividadRecursos]', name, payload || {});
              }
              // Livewire v2
              if (window.Livewire && typeof window.Livewire.on === 'function') {
                window.Livewire.on('debug', payload => onEvent('debug', payload));
                window.Livewire.on('notify', payload => onEvent('notify', payload));
              }
              // Livewire v3
              if (window.document.addEventListener) {
                document.addEventListener('livewire:init', () => {
                  if (window.Livewire && typeof window.Livewire.on === 'function') {
                    window.Livewire.on('debug', payload => onEvent('debug', payload));
                    window.Livewire.on('notify', payload => onEvent('notify', payload));
                  }
                });
              }
            })();
          </script>

          <hr />
          <h5 class="mt-4">Recursos</h5>
          @if (empty($recursos))
            <div class="alert alert-info">Aún no hay recursos en esta actividad.</div>
          @else
            <ul class="list-group">
              @foreach ($recursos as $r)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <div class="fw-bold">{{ $r['titulo'] }}</div>
                    <small class="text-muted">{{ $r['propietario'] }} • {{ $r['fecha'] }}</small>
                  </div>
                  <div>
                    @if ($r['url'])
                      <a class="btn btn-outline-secondary btn-sm" href="{{ $r['url'] }}" target="_blank">Ver/Descargar</a>
                    @endif
                  </div>
                </li>
              @endforeach
            </ul>
          @endif
        @endif
      </div>
    </div>
  </div>
</section>
