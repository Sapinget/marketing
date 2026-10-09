@extends('layouts.dashboard', ['activeTab' => 'orderan_online'])

@section('title', 'Order Online')

@section('dashboard-menu')
    @include('dashboard.partials.menus.order-online')
@endsection
