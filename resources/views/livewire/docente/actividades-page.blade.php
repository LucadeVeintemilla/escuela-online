@extends('layouts.adminlte_with_sidebar')
@section('title', 'Actividades del curso')
@section('sidebar')
  @include('livewire.docente.sidebar')
@endsection
@section('content')
  <livewire:docente-actividades />
@endsection
