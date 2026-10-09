@extends('layouts.dashboard', ['activeTab' => 'market_intelijen_harga'])

@section('title', 'Intelijen Harga')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-market-intel')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.market-intelijen-harga')
@endsection
