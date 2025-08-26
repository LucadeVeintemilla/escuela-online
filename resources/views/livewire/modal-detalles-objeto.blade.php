<div class="modal fade" id="modalDetallesObjeto" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title bi bi-chevron-bar-expand">
                    &nbsp;&nbsp;&nbsp;Detalles de {{ $modelo }}
                </h5>

                <button type="button" class="close" data-dismiss="modal" title="Cerrar">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{ $id }}
                @if ($modelo)
                    @livewire('Formulario' . $modelo, ['modelo' => $modelo, 'id' => $id], key($modelo . $id))
                @endif
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-warning bi bi-database-down" form="form-{{ strtolower($modelo) }}">&nbsp;&nbsp;&nbsp;Actualizar</button>
                <button type="button" class="btn btn-danger bi bi-trash" data-dismiss="modal" wire:click="eliminar" data-toggle="modal">&nbsp;&nbsp;&nbsp;Eliminar</button>
                <button type="button" class="btn btn-secondary bi bi-x-lg" data-dismiss="modal">&nbsp;&nbsp;&nbsp;Cancelar</button>
            </div>
        </div>
    </div>
</div>