<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reporte de Calificaciones</title>
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
    h1 { font-size: 18px; margin: 0 0 10px; }
    .meta { margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 6px 8px; }
    th { background: #f0f0f0; }
  </style>
</head>
<body>
  <h1>Reporte de Calificaciones</h1>
  <div class="meta">
    <div><strong>Curso:</strong> {{ $curso }}</div>
    <div><strong>Asignatura:</strong> {{ $asignatura }}</div>
    <div><strong>Rango:</strong> {{ $desde ?: '-' }} a {{ $hasta ?: '-' }}</div>
  </div>
  <table>
    <thead>
      <tr>
        <th>Alumno</th>
        <th>Asignatura</th>
        <th>Actividad</th>
        <th>Calificación</th>
        <th>Abrev.</th>
        <th>Observación</th>
        <th>Fecha</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($resultados as $r)
        <tr>
          <td>{{ $r['alumno'] }}</td>
          <td>{{ $r['asignatura'] ?? '-' }}</td>
          <td>{{ $r['actividad'] }}</td>
          <td>{{ $r['calificacion'] }}</td>
          <td>{{ $r['abreviatura'] }}</td>
          <td>{{ $r['observacion'] }}</td>
          <td>{{ $r['fecha'] }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="7" style="text-align:center;">Sin resultados para los filtros seleccionados.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
