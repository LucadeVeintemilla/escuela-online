<form x-data @submit-insertar-form.window.prevent="$el.requestSubmit()" @submit-actualizar-form.window.prevent="$wire.actualizar()" wire:submit.prevent="insertar('{{ $modelo }}')">
    @if ($id)
        @livewire('CamposNoModificables', ['id' => $id, 'created_at' => $created_at, 'updated_at' => $updated_at], key($modelo . $id . $created_at . $updated_at))
    @endif

    <div class="row d-flex justify-content-center">
        <div class="col-md-3">
            <div class="form-group">
                <label for="seccion">Sección</label>
                <input id="seccion" type="text" class="form-control" wire:model.live='seccion'>
                @error('seccion')<small class="text-danger">{{ $message }}</small>@enderror
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