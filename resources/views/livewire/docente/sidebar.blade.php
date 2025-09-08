@php $rol = optional(auth()->user()->role)->rol ?? null; @endphp
@if($rol === 'Docente')
<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
  <li class="nav-item">
    <a href="{{ route('docente.dashboard') }}" class="nav-link {{ request()->routeIs('docente.dashboard') ? 'active' : '' }}">
      <i class="nav-icon fas fa-home"></i>
      <p>Inicio</p>
    </a>
  </li>
  <li class="nav-item">
    <a href="{{ route('docente.cursos') }}" class="nav-link {{ request()->routeIs('docente.cursos') ? 'active' : '' }}">
      <i class="nav-icon fas fa-chalkboard-teacher"></i>
      <p>Mis Cursos</p>
    </a>
  </li>
  <li class="nav-item">
    <a href="{{ route('docente.reportes') }}" class="nav-link {{ request()->routeIs('docente.reportes') ? 'active' : '' }}">
      <i class="nav-icon fas fa-chart-bar"></i>
      <p>Reportes</p>
    </a>
  </li>
  {{-- En el futuro: listado general de actividades --}}
  {{-- <li class="nav-item">
    <a href="{{ route('docente.actividades') }}" class="nav-link {{ request()->routeIs('docente.actividades') ? 'active' : '' }}">
      <i class="nav-icon fas fa-clipboard-check"></i>
      <p>Actividades</p>
    </a>
  </li> --}}
</ul>
@endif
