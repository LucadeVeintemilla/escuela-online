@extends('layouts.adminlte_with_sidebar')
@section('title', 'Mis Estudiantes')
@section('sidebar')
  @include('livewire.docente.sidebar')
@endsection
@section('content')
  <livewire:docente-estudiantes />
  <script>
    document.addEventListener('livewire:init', () => {
    });
    window.addEventListener('docente-estudiantes:curso-seleccionado', (e) => {
    });
  </script>
@endsection
