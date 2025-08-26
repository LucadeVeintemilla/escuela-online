<form id="form-{{ strtolower($modelo) }}" wire:submit.prevent="{{ $id ? 'actualizar' : 'insertar(' . json_encode($modelo) . ')' }}">
    @if ($id)
        @livewire('CamposNoModificables', ['id' => $id, 'created_at' => $created_at, 'updated_at' => $updated_at], key($modelo . $id . $created_at . $updated_at))
    @endif

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="calificacion">Calificación</label>
                <input id="calificacion" type="text" class="form-control" wire:model.live='calificacion'>
                @error('calificacion')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="abreviatura">Abreviatura</label>
                <input id="abreviatura" type="text" class="form-control" wire:model.live='abreviatura'>
                @error('abreviatura')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" class="form-control" rows="2" wire:model.live='descripcion'></textarea>
                @error('descripcion')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="observacion">Observación</label>
                <textarea id="observacion" class="form-control" rows="2" wire:model.live='observacion'></textarea>
                @error('observacion')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>
</form>