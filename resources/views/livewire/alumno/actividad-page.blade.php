@extends('layouts.adminlte_with_sidebar')
@section('title', 'Actividad')
@section('sidebar')
  @include('livewire.alumno.sidebar')
@endsection
@section('content')
  <livewire:alumno-actividad-recursos />
@endsection
