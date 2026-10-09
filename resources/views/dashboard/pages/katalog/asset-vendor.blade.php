@extends('layouts.dashboard', ['activeTab' => 'asset_vendor_inventory'])

@section('title', 'Asset Vendor & Inventory')

@section('dashboard-menu')
    @include('dashboard.partials.menus.asset-vendor-inventory')
@endsection
