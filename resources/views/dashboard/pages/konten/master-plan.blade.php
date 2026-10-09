@extends('layouts.dashboard', ['activeTab' => 'master'])

@section('title', 'Master Plan')

@section('dashboard-menu')
    @include('dashboard.partials.menus.master-plan')
@endsection
