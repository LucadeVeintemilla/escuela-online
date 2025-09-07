@extends('layouts.adminlte_with_sidebar')
@section('title', 'Mis Cursos')
@section('sidebar')
  @include('livewire.docente.sidebar')
@endsection
@section('content')
  <livewire:docente-mis-cursos />
@endsection
