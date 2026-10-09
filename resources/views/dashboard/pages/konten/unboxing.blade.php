@extends('layouts.dashboard', ['activeTab' => 'unboxing'])

@section('title', 'Unboxing')

@section('dashboard-menu')
    @include('dashboard.partials.menus.unboxing')
@endsection
