@extends('layouts.dashboard', ['activeTab' => 'editor_performance'])

@section('title', 'Editor Performance')

@section('dashboard-menu')
    @include('dashboard.partials.menus.editor-performance')
@endsection
