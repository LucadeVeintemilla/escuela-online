@extends('layouts.adminlte_with_sidebar')
@section('title', 'Docencia')
@section('sidebar')
  @include('livewire.docente.sidebar')
@endsection
@section('content')
<section class="content pt-3">
  <div class="container-fluid">
    <div class="card card-default">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Docencia</h3>
        <div class="card-tools">
          <a href="{{ route('docente.cursos') }}" class="btn btn-sm btn-primary mr-2">Mis Cursos</a>
          <a href="{{ route('docente.calificar') }}" class="btn btn-sm btn-outline-primary">Calificar</a>
        </div>
      </div>
      <div class="card-body">
        <p class="text-muted mb-0">Usa el menú lateral (hamburguesa) para navegar entre secciones del docente.</p>
      </div>
    </div>
  </div>
</section>
@endsection
