@extends('layouts.dashboard', ['activeTab' => 'market_eksternal'])

@section('title', 'Semua Kompetitor')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-market-intel')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.market-eksternal')
@endsection
