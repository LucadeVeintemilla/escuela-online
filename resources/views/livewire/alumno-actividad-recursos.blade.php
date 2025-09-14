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

          <h5 class="mt-2">Recursos subidos por el docente</h5>
          @php $recursosDocente = array_values(array_filter($recursos, fn($x) => ($x['propietario'] ?? '') !== 'Alumno')); @endphp
          @if (empty($recursosDocente))
            <div class="alert alert-info">Aún no hay recursos del docente en esta actividad.</div>
          @else
            <ul class="list-group mb-3">
              @foreach ($recursosDocente as $r)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <div class="fw-bold">{{ $r['titulo'] }}</div>
                    <small class="text-muted">{{ $r['fecha'] }}</small>
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

          <hr />
          @if (!$disponible)
            <div class="alert alert-info">Esta actividad no está disponible para subir recursos en este momento.</div>
          @endif
          @if ($intentosMax === null)
            <div class="alert alert-secondary py-2">Intentos: Ilimitado</div>
          @else
            @php $restantes = max(0, $intentosMax - $intentosUsados); @endphp
            <div class="alert alert-secondary py-2">Intentos usados: {{ $intentosUsados }} / {{ $intentosMax }} — Restantes: {{ $restantes }}</div>
          @endif
          <form wire:submit.prevent="subir">
            <div class="mb-3">
              <label class="form-label">Título (opcional)</label>
              <input type="text" class="form-control" wire:model.defer="titulo" placeholder="Ej. Mi archivo de trabajo" @disabled(!$disponible) />
            </div>
            <div class="mb-3">
              <label class="form-label">Archivo</label>
              <input type="file" class="form-control" wire:model="archivo" @disabled(!$disponible) />
              @error('archivo') <div class="text-danger small">{{ $message }}</div> @enderror
              <div class="small text-muted mt-1" wire:loading wire:target="archivo">Cargando archivo...</div>
            </div>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="archivo,subir" @disabled(!$disponible)>
              <span wire:loading.remove wire:target="archivo,subir">Subir recurso</span>
              <span wire:loading wire:target="archivo,subir">Subiendo...</span>
            </button>
          </form>

          <hr />
          <h5 class="mt-4">Mis recursos</h5>
          @php $misRecursos = array_values(array_filter($recursos, fn($x) => ($x['propietario'] ?? '') === 'Alumno')); @endphp
          @if (empty($misRecursos))
            <div class="alert alert-info">Aún no has subido recursos.</div>
          @else
            <ul class="list-group">
              @foreach ($misRecursos as $r)
                <li class="list-group-item">
                  @if ($editRecursoId === $r['id'])
                    <div class="row g-2 align-items-end">
                      <div class="col-md-5">
                        <label class="form-label">Título</label>
                        <input type="text" class="form-control form-control-sm" wire:model.defer="editRecursoTitulo" />
                      </div>
                      <div class="col-md-5">
                        <label class="form-label">Reemplazar archivo (opcional)</label>
                        <input type="file" class="form-control form-control-sm" wire:model="replaceArchivo" />
                        @error('replaceArchivo') <div class="text-danger small">{{ $message }}</div> @enderror
                      </div>
                      <div class="col-md-2 d-flex gap-1">
                        <button class="btn btn-sm btn-success mr-1" wire:click="guardarRecursoEditado" type="button">Guardar</button>
                        <button class="btn btn-sm btn-secondary" wire:click="$set('editRecursoId', null)" type="button">Cancelar</button>
                      </div>
                    </div>
                  @else
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <div class="fw-bold">{{ $r['titulo'] }}</div>
                        <small class="text-muted">{{ $r['fecha'] }}</small>
                      </div>
                      <div>
                        <button class="btn btn-sm btn-outline-primary mr-1" wire:click="iniciarEditarRecurso({{ $r['id'] }})">Editar</button>
                        <button class="btn btn-sm btn-outline-danger" wire:click="eliminarRecurso({{ $r['id'] }})">Eliminar</button>
                        @if (!empty($r['url']))
                          <a class="btn btn-sm btn-outline-secondary ml-1" href="{{ $r['url'] }}" target="_blank">Ver</a>
                        @endif
                      </div>
                    </div>
                  @endif
                </li>
              @endforeach
            </ul>
          @endif
        @endif
      </div>
    </div>
  </div>
</section>
