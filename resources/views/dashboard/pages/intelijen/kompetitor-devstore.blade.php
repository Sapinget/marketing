@extends('layouts.dashboard', ['activeTab' => 'market_ext_devstore'])

@section('title', 'Devstore')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-market-intel')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.market-ext-devstore')
@endsection
