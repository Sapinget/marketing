@extends('layouts.dashboard', ['activeTab' => 'meta_followers'])

@section('title', 'Followers IG')

@section('dashboard-menu')
    @include('dashboard.partials.menus.meta-followers')
@endsection
