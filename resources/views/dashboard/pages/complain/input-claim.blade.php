@extends('layouts.dashboard', ['activeTab' => 'input_claim'])

@section('title', 'Input Claim')

@section('dashboard-menu')
    @include('dashboard.partials.menus.input-claim')
@endsection
