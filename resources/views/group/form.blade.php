@extends('layouts.index')

@section('title', 'Tambah Grup')

@section('content_header')
    <h1>Managemen Grup</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="mb-2">
        <a href="{{ url('pengaturan/groups') }}" class="btn btn-primary btn-sm"><i
                class="fas fa-arrow-circle-left"></i></i>&ensp;Kembali ke Daftar Grup</a>
    </div>
    <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                @if(!$isAdmin)
                <li class='nav-item' data-href='#akses'><a class='nav-link active' data-toggle='tab' href='#akses' role='tab' aria-controls='akses'
                    aria-selected='false'>Pengaturan Grup</a>
                </li>
                @endif
                <li class='nav-item' data-href='#menu'><a class='nav-link {{ $isAdmin ? 'active' : ''}}' data-toggle='tab' href='#menu' role='tab' aria-controls='menu'
                    aria-selected='false'>Pengaturan Urutan Menu</a>
                </li>    
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">

            @if(!$isAdmin)
            <div class="tab-pane fade show active" id="akses">
                @include('group.akses')
            </div>
            @endif
            <div class="tab-pane fade {{ $isAdmin ? 'show active' : ''}}" id="menu">                                    
                @includeWhen(isset($id), 'group.menu') 
            </div>
            </div>
        </div>
        <!-- /.card -->
    </div>
       

@endsection

@include('partials.reset_form')
@include('partials.asset_datepicker')
