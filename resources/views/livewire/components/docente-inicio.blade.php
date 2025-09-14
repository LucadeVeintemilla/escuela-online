<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card card-default">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Inicio</h3>
          </div>
          <div class="card-body">
            <h5 class="mb-3">Noticias</h5>
            @if (empty($anuncios))
              <div class="alert alert-info">No hay anuncios disponibles.</div>
            @else
              <ul class="list-group">
                @foreach ($anuncios as $a)
                  <li class="list-group-item">
                    <div class="d-flex justify-content-between">
                      <strong>{{ $a['titulo'] }}</strong>
                      <small class="text-muted">{{ $a['fecha'] }}</small>
                    </div>
                    <div>{{ $a['descripcion'] }}</div>
                    @if ($a['inicio'] || $a['fin'])
                      <small class="text-muted">Vigencia: {{ $a['inicio'] ?? '—' }} - {{ $a['fin'] ?? '—' }}</small>
                    @endif
                  </li>
                @endforeach
              </ul>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
