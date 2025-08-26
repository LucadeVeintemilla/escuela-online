<form id="form-{{ strtolower($modelo) }}" wire:submit.prevent="{{ $id ? 'actualizar' : 'insertar(' . json_encode($modelo) . ')' }}">
    @if ($id)
        @livewire('CamposNoModificables', ['id' => $id, 'created_at' => $created_at, 'updated_at' => $updated_at], key($modelo . $id . $created_at . $updated_at))
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="alumno_id">Alumno</label>
                <select id="alumno_id" class="custom-select" wire:model.live="alumno_id">
                    @foreach ($alumnos as $al)
                        <option value="{{ $al->id }}">{{ $al->nombre_1 }} {{ $al->nombre_2 }} {{ $al->apellido_1 }} {{ $al->apellido_2 }}</option>
                    @endforeach
                </select>
                @error('alumno_id')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="asignatura_grado_id">Curso</label>
                <select id="asignatura_grado_id" class="custom-select" wire:model.live="asignatura_grado_id">
                    @foreach ($asignaturaGrados as $ag)
                        <option value="{{ $ag->id }}">{{ optional($ag->asignatura)->asignatura }} - {{ optional($ag->grado)->grado }}</option>
                    @endforeach
                </select>
                @error('asignatura_grado_id')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="calificacion_id">Calificación</label>
                <select id="calificacion_id" class="custom-select" wire:model.live="calificacion_id">
                    @foreach ($calificaciones as $cal)
                        <option value="{{ $cal->id }}">{{ $cal->calificacion }} ({{ $cal->abreviatura }})</option>
                    @endforeach
                </select>
                @error('calificacion_id')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="observacion">Observación</label>
                <textarea id="observacion" class="form-control" rows="2" wire:model.live="observacion"></textarea>
                @error('observacion')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>
</form>
