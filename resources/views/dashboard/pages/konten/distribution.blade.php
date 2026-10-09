@extends('layouts.dashboard', ['activeTab' => 'distribution'])

@section('title', 'Distribution')

@section('dashboard-menu')
    @include('dashboard.partials.menus.distribution')
@endsection
