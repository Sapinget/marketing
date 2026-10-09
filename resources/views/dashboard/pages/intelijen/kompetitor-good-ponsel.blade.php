@extends('layouts.dashboard', ['activeTab' => 'market_ext_goodponsel'])

@section('title', 'Good Ponsel')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-market-intel')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.market-ext-goodponsel')
@endsection
