@extends('layouts.adminlte_with_sidebar')
@section('title', 'Mis Actividades')
@section('sidebar')
  @include('livewire.alumno.sidebar')
@endsection
@section('content')
  <livewire:alumno-actividades />
@endsection
