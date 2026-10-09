@extends('layouts.dashboard', ['activeTab' => 'story'])

@section('title', 'Jadwal Story')

@section('dashboard-menu')
    @include('dashboard.partials.menus.story')
@endsection
