<li class="nav-item menu-open">
    <a href="#" class="nav-link">
        <i class="nav-icon bi bi-subtract"></i>
        <p>
            Paneles
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>

    @livewire('Submenu', 
        ['tituloSubmenu' => 'Cuadros de Mando', 
        'iconoSubmenu' => 'intersect', 
        'opcionesSubmenu' => [
            ['tituloOpcion' => 'Docencia',
             'iconoOpcion' => 'pie-chart', 
             'tituloAreaTrabajo' => 'Cuadros de Mando', 
             'codigoAreaTrabajo' => '00'],
            ['tituloOpcion' => 'Anuncios',
             'iconoOpcion' => 'megaphone',
             'tituloAreaTrabajo' => 'Anuncios',
             'codigoAreaTrabajo' => '22'],]]
    )
</li>