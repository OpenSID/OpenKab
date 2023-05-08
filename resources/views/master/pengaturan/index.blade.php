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
                                let url = '{{ route('api.pengaturan_aplikasi', ['filter[key][]' => 'warna_tema']) }}';
                                let create_url = new URL(url);
                                const response = await (await fetch(create_url.href)).json();
                                this.data = response.data
                            }
                        }" x-init="retrievePosts">
                                    <template x-for="(value, index) in data">
                                        <div class="mb-4">
                                            <label x-text="value.attributes.judul"></label>
                                                <x-adminlte-input-color name="color" x-bind:name="value.attributes.key" x-bind:data-color="value.attributes.value" x-model="value.attributes.value" data-color="" data-format='hex'
                                                                        data-horizontal=true>
                                                    <x-slot name="appendSlot">
                                                        <div class="input-group-text">
                                                            <i class="fas fa-lg fa-square"></i>
                                                        </div>
                                                    </x-slot>
                                                </x-adminlte-input-color>

{{--                                                <x-adminlte-input name="data" x-bind:name="value.attributes.key" x-model="value.attributes.value">--}}
{{--                                                </x-adminlte-input>--}}
                                        </div>
                                    </template>
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
