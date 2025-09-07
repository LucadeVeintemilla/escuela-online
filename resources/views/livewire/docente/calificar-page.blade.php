@extends('layouts.adminlte_with_sidebar')
@section('title', 'Calificar alumnos')
@section('sidebar')
  @include('livewire.docente.sidebar')
@endsection
@section('content')
    <livewire:docente-calificar />
@endsection
