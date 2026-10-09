@extends('layouts.dashboard', ['activeTab' => 'tiktok_template'])

@section('title', 'Template TikTok')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-tiktok')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.tiktok-template')
@endsection
