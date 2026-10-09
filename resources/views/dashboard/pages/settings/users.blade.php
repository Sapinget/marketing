@extends('layouts.dashboard', ['activeTab' => 'auth_users'])

@section('title', 'Manajemen User')

@section('dashboard-menu')
    @include('dashboard.partials.menus.auth-users')
@endsection
