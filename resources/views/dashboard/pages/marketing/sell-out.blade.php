@extends('layouts.dashboard', ['activeTab' => 'sell_out'])

@section('title', 'Sell Out Target')

@section('dashboard-menu')
    @include('dashboard.partials.menus.sell-out')
@endsection
