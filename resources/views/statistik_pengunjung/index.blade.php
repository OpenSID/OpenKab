@extends('layouts.index')

@section('title', 'Data Statistik Pengunjung')

@section('content_header')
    <h1>Data Statistik Pengunjung</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-4">
                <div data-testid="chart-device-visitor">{!! $chartDeviceVisitor->container() !!}</div>
            </div>
            <div class="col-8">
                <div data-testid="chart-visitor-daily">{!! $chartVisitorDaily->container() !!}</div>
            </div>
            <div class="col-12">
                <div data-testid="chart-visitor-post">{!! $chartVisitorPost->container() !!}</div>
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
