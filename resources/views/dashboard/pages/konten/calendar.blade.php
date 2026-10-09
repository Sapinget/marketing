@extends('layouts.dashboard', ['activeTab' => 'calendar'])

@section('title', 'Kalender')

@section('dashboard-menu')
    @include('dashboard.partials.menus.calendar')
@endsection
