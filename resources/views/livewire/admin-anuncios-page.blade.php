@extends('layouts.adminlte_with_sidebar')
@section('title', 'Anuncios')
@section('sidebar')
  <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
    @include('livewire.grupo-opciones-paneles')
  </ul>
@endsection
@section('content')
  <section class="content">
    <div class="container-fluid">
      <livewire:admin-anuncios />
    </div>
  </section>
@endsection
