<form x-data @submit-insertar-form.window.prevent="$el.requestSubmit()" @submit-actualizar-form.window.prevent="$wire.actualizar()" wire:submit.prevent="insertar('{{ $modelo }}')">
    @if ($id)
        @livewire('CamposNoModificables', ['id' => $id, 'created_at' => $created_at, 'updated_at' => $updated_at], key($modelo . $id . $created_at . $updated_at))
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="asignatura_id">Asignatura</label>
                <select class="custom-select" id="asignatura_id" wire:model.live='asignatura_id'>
                    @foreach ($asignaturas as $asignatura)
                        <option value="{{ $asignatura->id }}">{{ $asignatura->asignatura }}</option>
                    @endforeach
                </select>
                @error('asignatura_id')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="grado_id">Grado</label>
                <select class="custom-select" id="grado_id" wire:model.live='grado_id'>
                    @foreach ($grados as $grado)
                        <option value="{{ $grado->id }}">{{ $grado->grado }}</option>
                    @endforeach
                </select>
                @error('grado_id')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="observacion">Observación</label>
                <textarea id="observacion" class="form-control" rows="2" wire:model.live='observacion'></textarea>
                @error('observacion')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>
</form>
