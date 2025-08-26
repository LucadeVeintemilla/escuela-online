<form id="form-{{ strtolower($modelo) }}" wire:submit.prevent="{{ $id ? 'actualizar' : 'insertar(' . json_encode($modelo) . ')' }}" class="">
    @if ($id)
        @livewire('CamposNoModificables', ['id' => $id, 'created_at' => $created_at, 'updated_at' => $updated_at], key($modelo . $id . $created_at . $updated_at))
    @endif

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="grado">Grado</label>
                <select class="custom-select" id="grado" wire:model.live='grado_id'>
                    @foreach ($grados as $grado)
                        <option value={{ $grado->id }}> {{$grado->grado}} </option>
                    @endforeach
                </select>
                @error('grado_id')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="seccion">Sección</label>
                <select class="custom-select" id="seccion" wire:model.live='seccion_id'>
                    @foreach ($seccions as $seccion)
                        <option value={{ $seccion->id }}> {{$seccion->seccion}} </option>
                    @endforeach
                </select>
                @error('seccion_id')<small class="text-danger">{{ $message }}</small>@enderror
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