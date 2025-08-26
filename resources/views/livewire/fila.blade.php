<tr>
    {{-- input + id --}}
    <td class="text-center">
        @if ($objeto)
            <input type="checkbox" id='checkbox{{$objeto->id}}' wire:change='setEstado($event.target.checked)' @if ($estado)
                checked @endif>
        @else
            <input type="checkbox" disabled>
        @endif
    </td>

    {{-- valores --}}
    @foreach ($campos as $campo)
    <td>
        @if ($objeto)
            @if ($campo[1] == 'at')
                @foreach ($campo[2] as $encabezado)
                    {{$objeto->$encabezado}}
                @endforeach
            @elseif ($campo[1] == 'fk')
                @foreach ($campo[2] as $fk => $encabezados)
                    @foreach ($encabezados as $encabezado)
                        {{ optional($objeto->$fk)->$encabezado }}
                    @endforeach    
                @endforeach
            @endif
        @endif
    </td>
    @endforeach

    {{-- botonera de operaciones --}}
    <td>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-danger" title="Eliminar" data-toggle="modal"
                @if($objeto) wire:click="eliminarFila" @else disabled @endif><i class="bi bi-trash-fill"></i>
            </button>
            
            <button type="button" class="btn btn-outline-warning bi bi-arrow-clockwise" title="Recargar" @if($objeto) wire:click="actualizar" @else disabled @endif></button>

            <button type="button" class="btn btn-outline-primary bi bi-chevron-bar-expand" title="Detalles" @if($objeto) wire:click='verDetallesObjeto' @else disabled @endif data-toggle="modal" data-target="#modalDetallesObjeto"></button>
        </div>
    </td>
</tr>