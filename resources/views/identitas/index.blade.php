@extends('layouts.index')

@section('title', 'Identitas')

@section('content_header')
    <div class="row" x-data="identitas()" x-init="retrieveData()">
        <h1 class="text-identitas"><span x-text="dataIdentitas.nama_aplikasi"></span> </h1>
    </div>
@stop

@section('content')
    @include('partials.breadcrumbs')

    <div class="row" x-data="identitas()" x-init="retrieveData()">

        <div class="col-sm-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    @if ($canedit)
                        <a x-bind:href="'{{ url('pengaturan/identitas') }}/' + id + '/edit'">
                            <button type="button" class="btn btn-primary btn-sm"><i class="far fa-edit"></i> Ubah
                                Identitas</button>
                        </a>
                    @endif
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <img class="img-identitas img-responsive"
                                :src="dataIdentitas.logo ? '{{ asset('storage/img') }}/' + dataIdentitas.logo :
                                    '{{ asset('assets/img/opensid_logo.png') }}'"
                                alt="logo-Aplikasi">
                            <h3 class="text-identitas"><span x-text="dataIdentitas.nama_aplikasi"></span> </h3>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover tabel-rincian">
                                <tbody>
                                    <tr class="table-primary">
                                        <th colspan="3" class="subtitle_head">
                                            <strong x-text="dataIdentitas.sebutan_kab"></strong>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td width="300">Nama <span x-text="dataIdentitas.sebutan_kab"></span></td>
                                        <td width="1">:</td>
                                        <td x-text="dataIdentitas.nama_kabupaten"></td>
                                    </tr>
                                    <tr>
                                        <td>Kode <span x-text="dataIdentitas.sebutan_kab"></span></td>
                                        <td>:</td>
                                        <td x-text="dataIdentitas.kode_kabupaten"></td>
                                    </tr>
                                    <tr>
                                        <td>Sebutan <span x-text="dataIdentitas.sebutan_desa"></span></td>
                                        <td>:</td>
                                        <td x-text="dataIdentitas.sebutan_desa"></td>
                                    </tr>

                                    <tr>
                                        <td width="300">Nama Provinsi</span></td>
                                        <td width="1">:</td>
                                        <td x-text="dataIdentitas.nama_provinsi"></td>
                                    </tr>
                                    <tr>
                                        <td width="300">Kode Provinsi</span></td>
                                        <td width="1">:</td>
                                        <td x-text="dataIdentitas.kode_provinsi"></td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <script nonce="{{ csp_nonce() }}">
        const header = @include('layouts.components.header_bearer_api_gabungan');

        function identitas() {
            return {
                id: 1,
                edit: '',
                dataIdentitas: {},
                retrieveData() {
                    fetch('{{ url('api/v1/identitas') }}', {
                            // headers: header,
                        })
                        .then(res => res.json())
                        .then(response => {
                            this.dataIdentitas = response.data.attributes;
                            this.id = response.data.id
                        });
                },
            }
        }
    </script>
@endsection
