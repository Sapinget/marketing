@extends('layouts.dashboard', ['activeTab' => 'asset_vendor_inventory'])

@section('title', 'Asset Vendor & Inventory')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-asset-vendor')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.asset-vendor-inventory')
@endsection
