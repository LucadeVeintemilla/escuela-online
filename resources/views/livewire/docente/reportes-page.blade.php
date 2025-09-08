@extends('layouts.adminlte_with_sidebar')
@section('title', 'Reportes')
@section('sidebar')
  @include('livewire.docente.sidebar')
@endsection
@section('content')
  <livewire:docente-reportes />
@endsection
