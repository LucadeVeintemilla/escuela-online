<section class="content">
  <div class="container-fluid">
    <div class="card card-default">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Mi Grado y Sección</h3>
      </div>
      <div class="card-body">
        @if ($mensaje)
          <div class="alert alert-warning">{{ $mensaje }}</div>
        @else
          <div class="row">
            <div class="col-md-6">
              <div class="callout callout-info">
                <h5>Grado</h5>
                <p class="mb-0">{{ $aula['grado'] ?? '-' }}</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="callout callout-success">
                <h5>Sección</h5>
                <p class="mb-0">{{ $aula['seccion'] ?? '-' }}</p>
              </div>
            </div>
          </div>

          <div class="mt-3">
            <h5>Asignaturas de mi grado</h5>
            @if (empty($asignaturas))
              <div class="alert alert-secondary mb-0">No hay asignaturas configuradas para tu grado.</div>
            @else
              <ul class="list-group mb-0">
                @foreach ($asignaturas as $as)
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>{{ $as['nombre'] }}</span>
                  </li>
                @endforeach
              </ul>
            @endif
          </div>

          <hr />
          <h5 class="mt-3">Mis calificaciones</h5>
          @if (empty($notas))
            <div class="alert alert-info">Aún no tienes calificaciones registradas.</div>
          @else
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Actividad</th>
                    <th style="width:140px">Calificación</th>
                    <th>Observación</th>
                    <th style="width:160px">Fecha</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($notas as $n)
                    <tr>
                      <td>{{ $n['titulo'] }}</td>
                      <td>{{ $n['calificacion'] }} ({{ $n['abreviatura'] }})</td>
                      <td>{{ $n['observacion'] }}</td>
                      <td>{{ $n['fecha'] }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        @endif
      </div>
    </div>
  </div>
</section>
