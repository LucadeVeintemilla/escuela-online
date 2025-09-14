<section class="content">
  <div class="container-fluid">
    <div class="card card-default">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Actividades</h3>
        <div class="card-tools">
          <a href="{{ route('docente.cursos') }}" class="btn btn-sm btn-secondary">Volver</a>
          <button class="btn btn-sm btn-primary ml-2" wire:click="nueva">Nueva actividad</button>
        </div>
      </div>
      <div class="card-body">
        @if ($mensaje)
          <div class="alert alert-info">{{ $mensaje }}</div>
        @endif

        @if ($mostrarFormulario)
          <div class="card card-body mb-3">
            <h5 class="mb-3">{{ $actividad_id ? 'Editar actividad' : 'Nueva actividad' }}</h5>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Título</label>
                <input type="text" class="form-control @error('form_titulo') is-invalid @enderror" wire:model.defer="form_titulo" placeholder="Título de la actividad" />
                @error('form_titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Descripción</label>
                <input type="text" class="form-control @error('form_descripcion') is-invalid @enderror" wire:model.defer="form_descripcion" placeholder="Descripción breve" />
                @error('form_descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-3">
                <label class="form-label">Inicio</label>
                <input type="datetime-local" class="form-control @error('form_inicio') is-invalid @enderror" wire:model.defer="form_inicio" />
                @error('form_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-3">
                <label class="form-label">Fin</label>
                <input type="datetime-local" class="form-control @error('form_fin') is-invalid @enderror" wire:model.defer="form_fin" />
                @error('form_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-md-3">
                <label class="form-label">Número de intentos (alumno)</label>
                <input type="number" min="0" class="form-control @error('form_max_intentos') is-invalid @enderror" wire:model.defer="form_max_intentos" placeholder="Ej. 1, 2... (vacío o 0 = ilimitado)" />
                @error('form_max_intentos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="text-muted">0 o vacío significa ilimitado. Controla cuántas veces puede reemplazar su envío.</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Recurso del docente (opcional) — Título</label>
                <input type="text" class="form-control @error('recursoTitulo') is-invalid @enderror" wire:model.defer="recursoTitulo" placeholder="Ej. Guía de trabajo" />
                @error('recursoTitulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Archivo</label>
                <input type="file" class="form-control @error('archivoDocente') is-invalid @enderror" wire:model="archivoDocente" />
                @error('archivoDocente') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                <div class="small text-muted mt-1" wire:loading wire:target="archivoDocente">Cargando archivo...</div>
              </div>

              <div class="col-md-6 d-flex align-items-end justify-content-end">
                <button class="btn btn-secondary mr-2" wire:click="cancelarFormulario" type="button">Cancelar</button>
                <button class="btn btn-success" wire:click="guardar" type="button">
                  <span wire:loading.remove wire:target="guardar">Guardar</span>
                  <span wire:loading wire:target="guardar">Guardando...</span>
                </button>
              </div>
            </div>
          </div>
        @endif

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
                  <button class="btn btn-outline-primary btn-sm" wire:click="editar({{ $ac['id'] }})">Editar</button>
                  <button class="btn btn-outline-danger btn-sm" wire:click="confirmarEliminar({{ $ac['id'] }})">Eliminar</button>
                  <button class="btn btn-primary btn-sm" wire:click="irCalificar({{ $ac['id'] }})">Calificar</button>
                </div>
              </div>
            @endforeach
          </div>
        @endif

        @if ($actividad_id && $mostrarFormulario)
          <hr />
          <h5 class="mt-2">Recursos del docente</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Subir recurso (solo archivo)</label>
              <input type="file" class="form-control @error('archivoDocente') is-invalid @enderror" wire:model="archivoDocente" />
              @error('archivoDocente') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              <div class="small text-muted mt-1" wire:loading wire:target="archivoDocente">Cargando archivo...</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Título (opcional)</label>
              <input type="text" class="form-control" wire:model.defer="recursoTitulo" placeholder="Ej. Guía PDF" />
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button class="btn btn-outline-success w-100" type="button" wire:click="subirSoloRecurso" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="subirSoloRecurso">Subir</span>
                <span wire:loading wire:target="subirSoloRecurso">Subiendo...</span>
              </button>
            </div>
          </div>

          @if (empty($recursosActividad))
            <div class="alert alert-info mt-3">Aún no hay recursos del docente en esta actividad.</div>
          @else
            <ul class="list-group mt-3">
              @foreach ($recursosActividad as $r)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div class="flex-fill">
                    @if ($editRecursoId === $r['id'])
                      <input type="text" class="form-control form-control-sm" wire:model.defer="editRecursoTitulo" />
                      <small class="text-muted">{{ $r['fecha'] }}</small>
                    @else
                      <div class="fw-bold">{{ $r['titulo'] }}</div>
                      <small class="text-muted">{{ $r['fecha'] }}</small>
                    @endif
                  </div>
                  <div class="ml-2">
                    @if ($editRecursoId === $r['id'])
                      <button class="btn btn-sm btn-success" wire:click="guardarRecursoEditado">Guardar</button>
                      <button class="btn btn-sm btn-secondary" wire:click="$set('editRecursoId', null)">Cancelar</button>
                    @else
                      <button class="btn btn-sm btn-outline-primary" wire:click="iniciarEditarRecurso({{ $r['id'] }})">Renombrar</button>
                      <button class="btn btn-sm btn-outline-danger" wire:click="eliminarRecurso({{ $r['id'] }})">Eliminar</button>
                    @endif
                  </div>
                </li>
              @endforeach
            </ul>
          @endif
        @endif

        @if ($actividad_id && str_contains($mensaje, 'Confirma eliminar'))
          <div class="alert alert-warning mt-3 d-flex justify-content-between align-items-center">
            <span>{{ $mensaje }}</span>
            <div>
              <button class="btn btn-sm btn-secondary" wire:click="$set('actividad_id', null)">Cancelar</button>
              <button class="btn btn-sm btn-danger" wire:click="eliminar">Eliminar</button>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
