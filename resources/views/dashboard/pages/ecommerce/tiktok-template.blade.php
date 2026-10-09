@extends('layouts.dashboard', ['activeTab' => 'tiktok_template'])

@section('title', 'Template TikTok')

@section('dashboard-menu')
    @include('dashboard.partials.menus.tiktok-template')
@endsection
