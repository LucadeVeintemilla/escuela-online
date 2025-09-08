@extends('layouts.adminlte_with_sidebar')
@section('title', 'Mi Grado y Sección')
@section('sidebar')
  @include('livewire.alumno.sidebar')
@endsection
@section('content')
  <livewire:alumno-mi-aula />
@endsection
