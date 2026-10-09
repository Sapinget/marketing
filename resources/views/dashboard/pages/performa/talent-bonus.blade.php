@extends('layouts.dashboard', ['activeTab' => 'talent_bonus'])

@section('title', 'Talent Bonus')

@section('dashboard-menu')
    @include('dashboard.partials.menus.talent-bonus')
@endsection
