@extends('layouts.dashboard', ['activeTab' => 'service'])

@section('title', 'Service')

@section('dashboard-menu')
    @include('dashboard.partials.menus.service')
@endsection
