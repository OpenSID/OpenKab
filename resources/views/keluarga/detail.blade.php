@extends('layouts.index')

@push('css')
    <style nonce="{{ csp_nonce() }}" >
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

@section('title', 'Biodata Penduduk')

@section('content_header')
    <h1>Biodata Keluarga</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="float-left">
                        <div class="btn-group">
                            <a href="{{ url('penduduk') }}" class="btn btn-sm btn-block btn-secondary"><i
                                    class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div x-data="{
                        data: {},

                        async retrievePosts() {
                            const response = await (await fetch('{{ route('api.keluarga.detail', ['no' => $no_kk]) }}')).json();
                            this.data = response.data[0].attributes
                        }
                    }" x-init="retrievePosts">
                        <div class="row">
                            <div class="col-12">
                                <h3 class="text-center"><strong>SALINAN KARTU KELUARGA</strong></h3>
                            </div>
                            <div class="col-12 mb-5">
                                <h5 class="text-center"><strong>No. <span x-text="data.no_kk"></span></strong></h5>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-sm-8">
                                <div class="row">
                                    <label class="col-sm-3 control-label">ALAMAT</label>
                                    <div class="col-sm-8">
                                        <p class="text-muted">: <span x-text="data.alamat_plus_dusun"></span></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 control-label">RT/RW</label>
                                    <div class="col-sm-9">
                                        <p class="text-muted">: <span x-text="data.rt + '/' + data.rw"></span></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 control-label">DESA / KELURAHAN</label>
                                    <div class="col-sm-9">
                                        <p class="text-muted">: <span x-text="data.desa"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 control-label">KECAMATAN</label>
                                    <div class="col-sm-9">
                                        <p class="text-muted">: <span x-text="data.kecamatan"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="row">
                                    <label class="col-sm-5 control-label">KABUPATEN</label>
                                    <div class="col-sm-7">
                                        <p class="text-muted">: <span x-text="data.kabupaten"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-5 control-label">KODE POS</label>
                                    <div class="col-sm-7">
                                        <p class="text-muted">: <span x-text="data.kode_pos"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-5 control-label">PROVINSI</label>
                                    <div class="col-sm-7">
                                        <p class="text-muted">: <span x-text="data.provinsi"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-5 control-label">JUMLAH ANGGOTA</label>
                                    <div class="col-sm-7">
                                        <p class="text-muted">: <span x-text="data.anggota?.length"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover ">
                                        <thead class="bg-gray disabled color-palette">
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th class="text-center">Nama Lengkap</th>
                                                <th class="text-center">NIK</th>
                                                <th class="text-center">Jenis Kelamin</th>
                                                <th class="text-center">Tempat Lahir</th>
                                                <th class="text-center">Tanggal Lahir</th>
                                                <th class="text-center">Agama</th>
                                                <th class="text-center">Pendidikan</th>
                                                <th class="text-center">Jenis Pekerjaan</th>
                                                <th class="text-center">Golongan Darah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(value, index) in data.anggota">
                                                <tr>
                                                    <td class="text-center"><span x-text="index+1"></span></td>
                                                    <td><span x-text="value.nama"></span></td>
                                                    <td><span x-text="value.nik"></td>
                                                    <td><span x-text="value.jenis_kelamin.nama"></td>
                                                    <td><span x-text="value.tempatlahir"></td>
                                                    <td><span
                                                            x-text="new Date(value.tanggallahir).toLocaleDateString('id-id')">
                                                    </td>
                                                    <td><span x-text="value.agama.nama"></td>
                                                    <td><span x-text="value.pendidikan_k_k.nama"></td>
                                                    <td><span x-text="value.pekerjaan.nama"></td>
                                                    <td><span x-text="value.golongan_darah.nama"></td>
                                                </tr>
                                            </template>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover ">
                                        <thead class="bg-gray disabled color-palette">
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th class="text-center">Status Perkawinan</th>
                                                <th class="text-center">Tanggal Perkawinan</th>
                                                <th class="text-center">Status Hubungan Dalam Keluarga</th>
                                                <th class="text-center">Kewarganegaraan</th>
                                                <th class="text-center">No. Paspor</th>
                                                <th class="text-center">No. KITAS / KITAP</th>
                                                <th class="text-center">Nama Ayah</th>
                                                <th class="text-center">Nama Ibu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(value, index) in data.anggota">
                                                <tr>
                                                    <td class="text-center"><span x-text="index+1"></span></td>
                                                    <td><span x-text="value.statusPerkawinan"></span></td>
                                                    <td class="text-center"><span
                                                            x-text="(value.tanggalperkawinan)? new Date(value.tanggalperkawinan).toLocaleDateString('id-id') : ''"></span>
                                                    </td>
                                                    <td><span x-text="value.penduduk_hubungan.nama"></span></td>
                                                    <td><span x-text="value.warga_negara.nama"></td>
                                                    <td><span x-text="value.dokumen_pasport"></td>
                                                    <td><span x-text="value.dokumen_kitas"></td>
                                                    <td><span x-text="value.nama_ayah"></td>
                                                    <td><span x-text="value.nama_ibu"></td>
                                                </tr>
                                            </template>

                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')

@endsection
