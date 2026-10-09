@extends('layouts.dashboard', ['activeTab' => 'ideation'])

@section('title', 'Ideation')

@section('dashboard-menu')
    @include('dashboard.partials.menus.ideation')
@endsection
