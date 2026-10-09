@extends('layouts.dashboard', ['activeTab' => 'meta_feed'])

@section('title', 'Feed Konten')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-meta-ig')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.meta-feed')
@endsection
