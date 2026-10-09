@extends('layouts.dashboard', ['activeTab' => 'market_pasar'])

@section('title', 'Pasar')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-market-intel')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.market-pasar')
@endsection
