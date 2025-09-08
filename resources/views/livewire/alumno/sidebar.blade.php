<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
  <li class="nav-item">
    <a href="{{ route('alumno.actividades') }}" class="nav-link {{ request()->routeIs('alumno.actividades') ? 'active' : '' }}">
      <i class="nav-icon fas fa-list-ul"></i>
      <p>Mis Actividades</p>
    </a>
  </li>
</ul>
