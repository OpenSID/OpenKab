@extends('layouts.index')

@section('title', 'Beranda')

@section('content_header')
    <h1>Beranda</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')
@endsection
