@extends('layouts.adminlte_with_sidebar')
@section('title', 'Inicio')
@section('sidebar')
  @include('livewire.docente.sidebar')
@endsection
@section('content')
  <livewire:docente-inicio />
@endsection
