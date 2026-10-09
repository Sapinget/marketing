@extends('layouts.dashboard', ['activeTab' => 'img_repo'])

@section('title', 'Repository Gambar')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-img-repo')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.img-repo')
@endsection
