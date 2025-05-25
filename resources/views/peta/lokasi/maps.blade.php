@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Peta')

@section('content_header')
    <h1>Peta</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('plan') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-circle-left"></i>&ensp;Kembali ke Data Lokasi
                    </a>
                </div>
                {{-- @dd(url('show/plan/ajax_lokasi_maps/'.$parent.'/'.$id)) --}}
                <iframe src="{{url('show/plan/ajax_lokasi_maps/'.$parent.'/'.$id)}}" width="100%" height="600px" frameborder="0"></iframe>
            </div>
        </div>
    </div>

@endsection
