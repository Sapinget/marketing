@extends('layouts.dashboard', ['activeTab' => 'bonus_report'])

@section('title', 'Bonus Report')

@section('dashboard-menu')
    @include('dashboard.partials.menus.bonus-report')
@endsection
