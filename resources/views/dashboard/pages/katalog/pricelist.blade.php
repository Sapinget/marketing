@extends('layouts.dashboard', ['activeTab' => 'pricelist_katalog'])

@section('title', 'Katalog Android (Pricelist)')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-catalog')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.pricelist-katalog')
@endsection
