@extends('layouts.adminlte_with_sidebar')
@section('title', 'Calificar actividad')
@section('sidebar')
  @include('livewire.docente.sidebar')
@endsection
@section('content')
  <livewire:docente-calificar-actividad />
@endsection
