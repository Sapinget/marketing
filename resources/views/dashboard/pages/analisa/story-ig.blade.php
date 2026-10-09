@extends('layouts.dashboard', ['activeTab' => 'meta_story'])

@section('title', 'Story IG')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-meta-ig')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.meta-story')
@endsection
