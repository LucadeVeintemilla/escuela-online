<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
  <li class="nav-item">
    <a href="{{ route('alumno.inicio') }}" class="nav-link {{ request()->routeIs('alumno.inicio') ? 'active' : '' }}">
      <i class="nav-icon fas fa-home"></i>
      <p>Inicio</p>
    </a>
  </li>
  <li class="nav-item">
    <a href="{{ route('alumno.actividades') }}" class="nav-link {{ request()->routeIs('alumno.actividades') ? 'active' : '' }}">
      <i class="nav-icon fas fa-list-ul"></i>
      <p>Mis Actividades</p>
    </a>
  </li>
  <li class="nav-item">
    <a href="{{ route('alumno.mi-aula') }}" class="nav-link {{ request()->routeIs('alumno.mi-aula') ? 'active' : '' }}">
      <i class="nav-icon fas fa-school"></i>
      <p>Mi Grado y Sección</p>
    </a>
  </li>
</ul>
