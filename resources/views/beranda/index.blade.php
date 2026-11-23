@extends('layouts.index')

@section('title', 'Beranda')

@section('content_header')
    
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')
@endsection
