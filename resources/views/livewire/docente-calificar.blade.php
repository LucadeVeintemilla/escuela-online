<section class="content">
    <div class="container-fluid">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title">Calificar alumnos</h3>
                <div class="card-tools">
                    <a href="{{ route('docente.dashboard') }}" class="btn btn-sm btn-secondary">Volver</a>
                </div>
            </div>

            <div class="card-body">
                @if ($mensaje)
                    <div class="alert alert-warning">{{ $mensaje }}</div>
                @else
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Asignatura</label>
                            <select class="form-control" wire:model.change="seleccion">
                                <option value="">-- Seleccionar --</option>
                                @foreach ($asignaturasOptions as $opt)
                                    <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Alumno</th>
                                    <th style="width:220px">Calificación</th>
                                    <th>Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($alumnos as $alumno)
                                    <tr>
                                        <td>
                                            {{ $alumno->apellido_1 }} {{ $alumno->apellido_2 }}, {{ $alumno->nombre_1 }} {{ $alumno->nombre_2 }}
                                        </td>
                                        <td>
                                            <select class="form-control" wire:model="notas.{{ $alumno->id }}">
                                                <option value="">--</option>
                                                @foreach ($calificaciones as $c)
                                                    <option value="{{ $c->id }}">{{ $c->calificacion }} ({{ $c->abreviatura }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" wire:model.defer="observaciones.{{ $alumno->id }}" placeholder="Opcional" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No hay alumnos en tu aula.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-primary" wire:click="guardar" @disabled(empty($alumnos))>Guardar</button>
                        <a href="{{ route('docente.dashboard') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
