@extends('layouts.dashboard', ['activeTab' => 'harga_kompetitor'])

@section('title', 'Harga & Kompetitor')

@section('dashboard-menu')
    @include('dashboard.partials.menus.harga-kompetitor')
@endsection
