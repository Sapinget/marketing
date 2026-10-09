@extends('layouts.dashboard', ['activeTab' => 'analytics'])

@section('title', 'Analytics')

@section('dashboard-menu')
    @include('dashboard.partials.menus.analytics')
@endsection
