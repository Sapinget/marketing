@extends('layouts.dashboard', ['activeTab' => 'template_background'])

@section('title', 'Template Background')

@push('menu-scripts')
    @include('dashboard.partials.shell.menu-scripts-catalog')
@endpush

@section('dashboard-menu')
    @include('dashboard.partials.menus.template-background')
@endsection
