<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Escuela Online')</title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="/admin-lte/plugins/fontawesome-free/css/all.min.css">
  <!-- daterange picker -->
  <link rel="stylesheet" href="/admin-lte/plugins/daterangepicker/daterangepicker.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="/admin-lte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Bootstrap Color Picker -->
  <link rel="stylesheet" href="/admin-lte/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="/admin-lte/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="/admin-lte/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="/admin-lte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- Bootstrap4 Duallistbox -->
  <link rel="stylesheet" href="/admin-lte/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
  <!-- BS Stepper -->
  <link rel="stylesheet" href="/admin-lte/plugins/bs-stepper/css/bs-stepper.min.css">
  <!-- dropzonejs -->
  <link rel="stylesheet" href="/admin-lte/plugins/dropzone/min/dropzone.min.css">
  <!-- Bootstrap4 Icons -->
  <link rel="stylesheet" href="/bootstrap/icons/bootstrap-icons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="/admin-lte/dist/css/adminlte.min.css">

  @livewireStyles
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="/" class="nav-link">Inicio</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      @auth
        <li class="nav-item">
          <span class="nav-link">{{ auth()->user()->name ?? 'Usuario' }}</span>
        </li>
      @endauth
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/" class="brand-link">
      <img src="/admin-lte/dist/img/AdminLTELogo.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Escuela Online</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        @yield('sidebar')
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Main content -->
    @yield('content')
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer text-sm">
    <div class="float-right d-none d-sm-block">
      <b>Versión</b> 1.0
    </div>
    <strong>&copy; {{ date('Y') }} Escuela Online.</strong> Todos los derechos reservados.
  </footer>
</div>

<!-- Scripts -->
<script src="/admin-lte/plugins/jquery/jquery.min.js"></script>
<script src="/admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/admin-lte/plugins/select2/js/select2.full.min.js"></script>
<script src="/admin-lte/plugins/moment/moment.min.js"></script>
<script src="/admin-lte/plugins/inputmask/jquery.inputmask.min.js"></script>
<script src="/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>
<script src="/admin-lte/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
<script src="/admin-lte/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="/admin-lte/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script src="/admin-lte/plugins/bs-stepper/js/bs-stepper.min.js"></script>
<script src="/admin-lte/plugins/dropzone/min/dropzone.min.js"></script>
<script src="/admin-lte/dist/js/adminlte.min.js"></script>

@livewireScripts
</body>
</html>
