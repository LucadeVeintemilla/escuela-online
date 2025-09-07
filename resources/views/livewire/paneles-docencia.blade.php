@extends('layouts.adminlte')
@section('title', 'Docencia')
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card card-default">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"> Docencia </h3>
                <div class="card-tools">
                    <a href="{{ route('docente.cursos') }}" class="btn btn-sm btn-primary mr-2">Mis Cursos</a>
                    <a href="{{ route('docente.calificar') }}" class="btn btn-sm btn-outline-primary">Calificar</a>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted">Selecciona una opción para gestionar tus cursos, actividades y calificaciones.</p>
            </div>
        </div>
    </div>
</section>
@endsection
