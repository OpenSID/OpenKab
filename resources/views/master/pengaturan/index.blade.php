@extends('layouts.index')

@push('css')
    <style>
        /* ubah semua ukuran text yang ada dalam card-body */
        .card-body {
            font-size: 14px;
        }

        /* ubah semua ukuran h3 yang ada dalam card-body */
        .card-body h3 {
            font-size: 24px;
        }

        .card-body h5 {
            font-size: 18px;
        }

        th {
            vertical-align: middle !important;
        }
    </style>
@endpush

@section('title', 'Pengaturan Aplikasi')

@section('content_header')
    <h1>Pengaturan Aplikasi</h1>
@stop

@section('content')
    @include('partials.flash_message')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <form action="{{ url('api/v1/pengaturan/update') }}" method="POST">
                @csrf
                <!-- /.card-header -->
                    <div class="card-body">
                        <div class="col">
                            <div x-data="{
                        data: {},

                        async retrievePosts() {
                            const response = await (await fetch('{{ route('api.pengaturan_aplikasi', ['filter[key][]' => 'warna_tema']) }}')).json();
                            this.data = response.data[0].attributes
                        }
                    }" x-init="retrievePosts">
                                <div class="mb-4">
                                    <label x-text="data.judul"></label>
                                    <x-adminlte-input-color name="color" x-bind:name="data.key" x-bind:data-color="data.value" x-model="data.value" data-format='hex'
                                                            data-horizontal=true>
                                        <x-slot name="appendSlot">
                                            <div class="input-group-text">
                                                <i class="fas fa-lg fa-square"></i>
                                            </div>
                                        </x-slot>
                                    </x-adminlte-input-color>
                                    @error('warna_tema')
                                    <div class="text-danger">{{ $message }}</div>
                                    <style>
                                        #color{
                                            border-color: red;
                                        }
                                    </style>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i>&nbsp;
                            Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')

@endsection
