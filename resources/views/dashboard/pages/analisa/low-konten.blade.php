@extends('layouts.dashboard', ['activeTab' => 'low_content_platform'])

@section('title', 'Low Konten')

@section('dashboard-menu')
    @include('dashboard.partials.menus.low-content')
@endsection
