<form id="form-{{ strtolower($modelo) }}" wire:submit.prevent="{{ $id ? 'actualizar' : 'insertar(\'' . $modelo . '\')' }}">
    @if ($id)
        @livewire('CamposNoModificables', ['id' => $id, 'created_at' => $created_at, 'updated_at' => $updated_at], key($modelo . $id . $created_at . $updated_at))
    @endif

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="nombre_1">Tipo de contenido</label>
                <input id="nombre_1" type="text" class="form-control" wire:model.live='tipo'>
                @error('tipo')<small class="text-danger">{{ $message }}</small>@enderror
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