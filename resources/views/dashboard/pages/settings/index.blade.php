@extends('layouts.dashboard', ['activeTab' => 'settings'])

@section('title', 'Settings')

@section('dashboard-menu')
    @include('dashboard.partials.menus.settings')
@endsection
