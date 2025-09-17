<aside class="main-sidebar sidebar-light-primary elevation-4">
    <!-- logo + nombre de la aplicación -->
    @livewire('NombreAplicacion', 
        ['nombreAplicacion' => 'Institucion 15005',

         'logoAplicacion' => 'admin-lte/dist/img/logo.png'],

        key('nombre-aplicacion'))

    <!-- menu lateral izquierdo -->
    <div class="sidebar">
        

        <!-- submenu -->
        @livewire('Menu', ['Menú'])
    </div>
</aside>