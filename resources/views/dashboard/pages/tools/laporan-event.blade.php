@extends('layouts.dashboard', ['activeTab' => 'laporan_event'])

@section('title', 'Laporan Event')

@section('dashboard-menu')
    @include('dashboard.partials.menus.laporan-event')
@endsection
