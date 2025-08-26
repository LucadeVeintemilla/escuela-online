<div class="modal fade" id="modalInsertarObjeto" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title bi bi-database-up">
                    &nbsp;&nbsp;&nbsp;Insertar {{ $modelo }}
                </h5>

                <button type="button" class="close" data-dismiss="modal" title="Cerrar">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if ($modelo)
                    @livewire('Formulario' . $modelo, ['modelo' => $modelo, 'id' => null], key($modelo))
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success bi bi-database-up" onclick="(function(){const modal=document.getElementById('modalInsertarObjeto');const form=modal?modal.querySelector('form'):null;if(form){if(form.requestSubmit){form.requestSubmit();}else{form.submit();}}})();">&nbsp;&nbsp;&nbsp;Insertar</button>
                <button type="button" class="btn btn-secondary bi bi-x-lg" data-dismiss="modal">&nbsp;&nbsp;&nbsp;Cancelar</button>
            </div>
        </div>
    </div>
</div>  

<script>
    window.addEventListener('close-insert-modal', () => {
        // Cierra el modal solo cuando la inserción fue exitosa
        const $modal = $('#modalInsertarObjeto');
        $modal.modal('hide');
        // Limpieza defensiva de backdrop/clases por si el DOM fue re-renderizado
        $('.modal-backdrop').remove();
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
    });
    window.addEventListener('open-insert-modal', () => {
        $('#modalInsertarObjeto').modal('show');
    });

    // También cuando Bootstrap emite hidden, asegúrate de limpiar backdrop si persiste
    $('#modalInsertarObjeto').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
    });
</script>