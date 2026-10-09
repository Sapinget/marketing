@extends('layouts.dashboard', ['activeTab' => 'meta_followers'])

@section('title', 'Followers IG')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-meta-followers')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.meta-followers')
@endsection
