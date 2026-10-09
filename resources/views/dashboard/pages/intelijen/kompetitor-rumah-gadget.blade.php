@extends('layouts.dashboard', ['activeTab' => 'market_ext_rumahgadget'])

@section('title', 'Rumah Gadget Bali')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-market-intel')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.market-ext-rumahgadget')
@endsection
