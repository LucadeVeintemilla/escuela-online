<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Escuela Online') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- AdminLTE & Plugins CSS -->
    <link rel="stylesheet" href="/admin-lte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="/admin-lte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="/admin-lte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="/bootstrap/icons/bootstrap-icons.min.css">

    <!-- App assets (Tailwind/Alpine) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
  </head>
  <body class="hold-transition login-page" style="min-height: 100vh;">
    <div class="login-box">
      <div class="login-logo">
        <a href="/" class="text-decoration-none d-block">
          <img src="/images/Logoescuela.png" alt="Institucion Educativa 15005" style="width: 150px; max-width: 100%; height: auto;" class="mb-2">
          <div class="font-weight-bold" style="font-size: 1.25rem;">
            Institucion Educativa 15005
          </div>
        </a>
      </div>

      <div class="card shadow-sm">
        <div class="card-body login-card-body">
          {{ $slot }}
        </div>
      </div>

      <p class="mt-3 text-center text-muted small">&copy; {{ date('Y') }} Escuela Online</p>
    </div>

    <!-- Scripts -->
    <script src="/admin-lte/plugins/jquery/jquery.min.js"></script>
    <script src="/admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/admin-lte/dist/js/adminlte.min.js"></script>

    @livewireScripts
  </body>
</html>
