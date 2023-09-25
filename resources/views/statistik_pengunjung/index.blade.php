@extends('layouts.index')

@section('title', 'Data Statistik Pengunjung')

@section('content_header')
    <h1>Data Statistik Pengunjung</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-4">
                {!! $chartDeviceVisitor->container() !!}
            </div>
            <div class="col-8">
                {!! $chartVisitorDaily->container() !!}
            </div>
            <div class="col-12">
                {!! $chartVisitorPost->container() !!}
            </div>
        </div>
    </div>
@endsection

@section('js')
    @apexchartsScripts
    {!! $chartDeviceVisitor->script() !!}
    {!! $chartVisitorDaily->script() !!}
    {!! $chartVisitorPost->script() !!}
@endsection
