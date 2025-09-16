<div class="card card-primary">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h3 class="card-title">Crear anuncio para alumnos</h3>
    <span class="badge badge-info">Visible solo para alumnos</span>
  </div>
  <div class="card-body">
    @if ($mensaje)
      <div class="alert alert-success">{{ $mensaje }}</div>
    @endif

    <form wire:submit.prevent="guardar">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Título del anuncio</label>
          <input type="text" class="form-control @error('anuncio') is-invalid @enderror" wire:model.defer="anuncio" placeholder="Título" />
          @error('anuncio') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Descripción</label>
          <input type="text" class="form-control @error('descripcion') is-invalid @enderror" wire:model.defer="descripcion" placeholder="Descripción breve" />
          @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
          <label class="form-label">Inicio</label>
          <input type="datetime-local" class="form-control @error('inicio') is-invalid @enderror" wire:model.defer="inicio" />
          @error('inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
          <label class="form-label">Fin</label>
          <input type="datetime-local" class="form-control @error('fin') is-invalid @enderror" wire:model.defer="fin" />
          @error('fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="activoCheck" wire:model="activo">
            <label class="form-check-label" for="activoCheck">Activo</label>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label">Observación</label>
          <input type="text" class="form-control @error('observacion') is-invalid @enderror" wire:model.defer="observacion" placeholder="Observaciones" />
          @error('observacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="mt-3">
        <button type="submit" class="btn btn-primary">
          <span wire:loading.remove wire:target="guardar">Publicar</span>
          <span wire:loading wire:target="guardar">Publicando...</span>
        </button>
      </div>
    </form>
  </div>
</div>
