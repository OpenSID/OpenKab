@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Tipe Lokasi')

@section('content_header')
    <h1>{{ $action }} Data Tipe Lokasi</h1>
@stop
@push('css')
   <style nonce="{{ csp_nonce() }}" >
        .hidden {
            /* display: none; */
        }
        .bs-glyphicons {
            padding-left: 0;
            padding-bottom: 1px;
            margin-bottom: 20px;
            list-style: none;
            overflow: hidden;
        }

        .bs-glyphicons .glyphicon {
            margin-top: 5px;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .bs-glyphicons .glyphicon-class {
            display: block;
            text-align: center;
            word-wrap: break-word;
            /* Help out IE10+ with class names */
        }

        .bs-glyphicons li {
            float: left;
            width: 25%;
            height: 115px;
            padding: 10px;
            margin: 0 -1px -1px 0;
            font-size: 12px;
            line-height: 1.4;
            text-align: center;
            border: 1px solid #ddd;
        }

        .bs-glyphicons li:hover,
        .bs-glyphicons li.active {
            background-color: #605ca8;
            color: #fff;
        }

        @media (min-width: 768px) {
            .bs-glyphicons li {
                width: 12.5%;
            }
        }

        .vertical-scrollbar {
            overflow-x: hidden;
            overflow-y: auto;
        }
    </style>
@endpush
@section('content')
    @include('partials.breadcrumbs')
    @if (Session::has('success'))
        <div class="alert alert-success">
            {{ Session::get('success') }}
        </div>
    @elseif (Session::has('error'))
        <div class="alert alert-danger">
            {{ Session::get('error') }}
        </div>
    @endif
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('point') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-circle-left"></i>&ensp;Kembali ke Data Tipe Lokasi
                    </a>
                </div>
                <form method="POST" class="form-horizontal" id="form">
                    @csrf
                    <input type="hidden" name="sumber" value="OpenKab">
                    <input type="hidden" name="parrent" value="{{$parrent}}">
                    <input type="hidden" name="tipe" value="{{$tipe}}">
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="nama" class="col-sm-2 control-label">Nama Jenis Lokasi</label>
                            <div class="col-sm-10">
                                <input
                                    id="nama"
                                    class="form-control input-sm nomor_sk required"
                                    maxlength="50"
                                    type="text"
                                    placeholder="Nama Jenis Lokasi"
                                    name="nama"
                                    required=""
                                    value="{{$point->nama ?? ''}}"
                                >
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="nomor" class="col-sm-2 control-label">Simbol</label>
                            <div class="col-sm-10">
                                @if (($point->simbol ?? '') != '')
                                    <img src="{{ asset('assets/img/gis/lokasi/point/'. $point->simbol) }}" />
                                @else
                                    <img src="{{ asset('assets/img/gis/lokasi/point/default.png') }}" />
                                @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="id_master" class="col-sm-2 control-label">Ganti Simbol</label>
                            <div class="col-sm-10">
                                <div class="vertical-scrollbar" style="max-height:300px;">
                                    <ul id="icons" class="bs-glyphicons">
                                        @foreach ($simbol as $data)
                                            <li @if (($point->simbol ?? '') == $data['simbol']) class="active" id="simbol_active" @endif onclick="li_active($(this).val());">
                                                <label>
                                                    <input type="radio" name="simbol" id="simbol" class="hidden" value="{{ $data['simbol'] }}" @checked(($point->simbol ?? '') == $data['simbol'])>
                                                    <img src="{{ asset('assets/img/gis/lokasi/point/'. $data['simbol']) }}">
                                                    <span class="glyphicon-class">{{ $data['simbol'] }}</span>
                                                </label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="reset" class="btn btn-danger btn-sm" onclick="reset_form();">
                            <i class="fa fa-times"></i> Batal
                        </button>

                        <button type="submit" class="btn btn-info btn-sm pull-right">
                            <i class="fa fa-check"></i> Simpan
                        </button>
                    </div>                    
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')

<script>
    function li_active() {
        $('li').click(function() {
            $('li.active').removeClass('active');
            $(this).addClass('active');
            $(this).children("input[type=radio]").click();
        });
    };

    function reset_form() {
        $('li.active').removeClass('active');
        $('#simbol_active').addClass('active');
        const form = document.getElementById('form');
        if (form) {
            form.reset();
        }
    };
</script>
@include('peta.point.form_js')
@endsection
