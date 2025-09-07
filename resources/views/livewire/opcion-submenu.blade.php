<li class="nav-item">
    @if (!empty($route))
        <a href="{{ route($route) }}" class="nav-link">
            <i class="bi bi-{{ $iconoOpcion }}"></i>
            <p> {{ $tituloOpcion }} </p>
        </a>
    @else
        <a href="#" class="nav-link" wire:click='setAreaTrabajo'>
            <i class="bi bi-{{ $iconoOpcion }}"></i>
            <p> {{ $tituloOpcion }} </p>
        </a>
    @endif
</li>