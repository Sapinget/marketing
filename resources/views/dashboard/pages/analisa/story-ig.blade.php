@extends('layouts.dashboard', ['activeTab' => 'meta_story'])

@section('title', 'Story IG')

@section('dashboard-menu')
    @include('dashboard.partials.menus.meta-story')
@endsection
