@extends('layouts.dashboard', ['activeTab' => 'profile'])

@section('title', 'Profile')

@section('dashboard-menu')
    @include('dashboard.partials.menus.profile')
@endsection
