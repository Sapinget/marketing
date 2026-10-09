@extends('layouts.dashboard', ['activeTab' => 'apple_katalog'])

@section('title', 'Katalog Apple')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-catalog')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.apple-katalog')
@endsection
