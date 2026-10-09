@extends('layouts.dashboard', ['activeTab' => 'budgeting'])

@section('title', 'Budgeting')

@section('dashboard-menu')
    @include('dashboard.partials.menus.budgeting')
@endsection
