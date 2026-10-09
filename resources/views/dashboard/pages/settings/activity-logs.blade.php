@extends('layouts.dashboard', ['activeTab' => 'activity_logs'])

@section('title', 'Activity Logs')

@section('dashboard-menu')
    @include('dashboard.partials.menus.activity-logs')
@endsection
