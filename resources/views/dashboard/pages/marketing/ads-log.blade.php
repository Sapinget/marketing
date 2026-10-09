@extends('layouts.dashboard', ['activeTab' => 'ads_log'])

@section('title', 'Ads Log')

@section('dashboard-menu')
    @include('dashboard.partials.menus.ads-log')
@endsection
