@extends('layouts.dashboard', ['activeTab' => 'market_audit_harga'])

@section('title', 'Audit Harga')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-market-intel')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.market-audit-harga')
@endsection
