@extends('layouts.adminlte_with_sidebar')
@section('title', 'Inicio')
@section('sidebar')
  @include('livewire.alumno.sidebar')
@endsection
@section('content')
  <livewire:alumno-inicio />
@endsection
