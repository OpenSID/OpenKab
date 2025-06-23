@extends('layouts.index')

@section('title', 'Biodata Penduduk')

@section('content_header')
    <h1>Biodata Penduduk</h1>
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
                    <h3 id="nik" class="card-title"></h3>
                    <br>
                    <p id="terdaftar-pada" class="text-sm font-italic"></p>
                    <div class="table-responsive">
                        <div class="table-responsive">
                        </div>
                        <table class="table table-bordered table-striped table-hover">
                            <tbody>
                                <tr class="text-center">
                                    <td id="foto" colspan="3"></td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered table-striped table-hover">
                            <tbody>
                                <tr>
                                    <td>Status Dasar</td>
                                    <td>:</td>
                                    <td id="status-dasar"></td>
                                </tr>
                                <tr>
                                    <td width="300">Nama</td>
                                    <td width="1">:</td>
                                    <td id="nama"></td>
                                </tr>
                                <tr>
                                    <td>Status Kepemilikan Identitas</td>
                                    <td>:</td>
                                    <td>
                                        <table class="table table-bordered table-striped table-hover detail">
                                            <tbody>
                                                <tr>
                                                    <th>Wajib Identitas</th>
                                                    <th>Identitas-EL</th>
                                                    <th>Status Rekam</th>
                                                    <th>Tag ID Card</th>
                                                </tr>
                                                <tr>
                                                    <td id="wajib-identitas"></td>
                                                    <td id="identitas-el"></td>
                                                    <td id="status-rekam"></td>
                                                    <td id="tag-id-card"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Nomor Kartu Keluarga</td>
                                    <td>:</td>
                                    <td id="no-kk"></td>
                                </tr>
                                <tr>
                                    <td>Nomor KK Sebelumnya</td>
                                    <td>:</td>
                                    <td id="no-kk-sebelumnya"></td>
                                </tr>
                                <tr>
                                    <td>Hubungan Dalam Keluarga</td>
                                    <td>:</td>
                                    <td id="penduduk-hubungan"></td>
                                </tr>
                                <tr>
                                    <td>Jenis Kelamin</td>
                                    <td>:</td>
                                    <td id="jk"></td>
                                </tr>
                                <tr>
                                    <td>Agama</td>
                                    <td>:</td>
                                    <td id="agama"></td>
                                </tr>
                                <tr>
                                    <td>Status Penduduk</td>
                                    <td>:</td>
                                    <td id="penduduk-status"></td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>DATA KELAHIRAN</strong></th>
                                </tr>
                                <tr>
                                    <td>Akta Kelahiran</td>
                                    <td>:</td>
                                    <td id="akta-lahir"></td>
                                </tr>
                                <tr>
                                    <td>Tempat / Tanggal Lahir</td>
                                    <td>:</td>
                                    <td id="ttl"></td>
                                </tr>
                                <tr>
                                    <td>Tempat Dilahirkan</td>
                                    <td>:</td>
                                    <td id="tempat-lahir"></td>
                                </tr>
                                <tr>
                                    <td>Jenis Kelahiran</td>
                                    <td>:</td>
                                    <td id="jenis-lahir"></td>
                                </tr>
                                <tr>
                                    <td>Kelahiran Anak Ke</td>
                                    <td>:</td>
                                    <td id="lahir-ke"></td>
                                </tr>
                                <tr>
                                    <td>Penolong Kelahiran</td>
                                    <td>:</td>
                                    <td id="penolong-lahir"></td>
                                </tr>
                                <tr>
                                    <td>Berat Lahir</td>
                                    <td>:</td>
                                    <td id="berat-lahir"></td>
                                </tr>
                                <tr>
                                    <td>Panjang Lahir</td>
                                    <td>:</td>
                                    <td id="panjang-lahir"></td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>PENDIDIKAN DAN PEKERJAAN</strong></th>
                                </tr>
                                <tr>
                                    <td>Pendidikan dalam KK</td>
                                    <td>:</td>
                                    <td id="pendidikan-kk"></td>
                                </tr>
                                <tr>
                                    <td>Pendidikan sedang ditempuh</td>
                                    <td>:</td>
                                    <td id="pendidikan"></td>
                                </tr>
                                <tr>
                                    <td>Pekerjaan</td>
                                    <td>:</td>
                                    <td id="pekerjaan"></td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>DATA KEWARGANEGARAAN</strong></th>
                                </tr>
                                <tr>
                                    <td>Suku/Etnis</td>
                                    <td>:</td>
                                    <td id="suku"></td>
                                </tr>
                                <tr>
                                    <td>Warga Negara</td>
                                    <td>:</td>
                                    <td id="wna"></td>
                                </tr>
                                <tr>
                                    <td>Nomor Paspor</td>
                                    <td>:</td>
                                    <td id="no-passpor"></td>
                                </tr>
                                <tr>
                                    <td>Tanggal Berakhir Paspor</td>
                                    <td>:</td>
                                    <td id="tgl-passpor">-</td>
                                </tr>
                                <tr>
                                    <td>Nomor KITAS/KITAP</td>
                                    <td>:</td>
                                    <td id="no-kitas"></td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>ORANG TUA</strong></th>
                                </tr>
                                <tr>
                                    <td>NIK Ayah</td>
                                    <td>:</td>
                                    <td id="nik-ayah"></td>
                                </tr>
                                <tr>
                                    <td>Nama Ayah</td>
                                    <td>:</td>
                                    <td id="nama-ayah"></td>
                                </tr>
                                <tr>
                                    <td>NIK Ibu</td>
                                    <td>:</td>
                                    <td id="nik-ibu"></td>
                                </tr>
                                <tr>
                                    <td>Nama Ibu</td>
                                    <td>:</td>
                                    <td id="nama-ibu"></td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>ALAMAT</strong></th>
                                </tr>
                                <tr>
                                    <td>Alamat</td>
                                    <td>:</td>
                                    <td id="alamat">DONUJHY</td>
                                </tr>
                                <tr>
                                    <td>Dusun</td>
                                    <td>:</td>
                                    <td id="dusun"></td>
                                </tr>
                                <tr>
                                    <td>RT/ RW</td>
                                    <td>:</td>
                                    <td id="rt-rw"></td>
                                </tr>
                                <tr>
                                    <td>Alamat Sebelumnya</td>
                                    <td>:</td>
                                    <td id="alm-sebelum">JIRRR</td>
                                </tr>
                                <tr>
                                    <td>Nomor Telepon</td>
                                    <td>:</td>
                                    <td id="telepon"></td>
                                </tr>
                                <tr>
                                    <td>Alamat Email</td>
                                    <td>:</td>
                                    <td id="email"></td>
                                </tr>
                                <tr>
                                    <td>Telegram</td>
                                    <td>:</td>
                                    <td id="telegram"></td>
                                </tr>
                                <tr>
                                    <td>Cara Hubung Warga</td>
                                    <td>:</td>
                                    <td id="cara-hubung"></td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>STATUS KAWIN</strong></th>
                                </tr>
                                <tr>
                                    <td>Status Kawin</td>
                                    <td>:</td>
                                    <td id="status-kawin"></td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>DATA KESEHATAN</strong></th>
                                </tr>
                                <tr>
                                    <td>Golongan Darah</td>
                                    <td>:</td>
                                    <td id="gol-darah">A</td>
                                </tr>
                                <tr>
                                    <td>Cacat</td>
                                    <td>:</td>
                                    <td id="cacat"></td>
                                </tr>
                                <tr>
                                    <td>Sakit Menahun</td>
                                    <td>:</td>
                                    <td id="sakit"></td>
                                </tr>
                                <tr>
                                    <td>Nama/Nomor Asuransi Kesehatan</td>
                                    <td>:</td>
                                    <td id="asuransi"></td>
                                </tr>
                                <tr>
                                    <td>Nomor BPJS Ketenagakerjaan</td>
                                    <td>:</td>
                                    <td id="bpjs"></td>
                                </tr>

                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>DATA LAINNYA</strong></th>
                                </tr>
                                <tr>
                                    <td>Bahasa</td>
                                    <td>:</td>
                                    <td id="bahasa"></td>
                                </tr>
                                <tr>
                                    <td>Keterangan</td>
                                    <td>:</td>
                                    <td id="ket"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <tbody>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>PROGRAM BANTUAN</strong></th>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="table-responsive">
                                            <table
                                                class="table table-bordered dataTable table-striped table-hover tabel-daftar"
                                                id="table-bantuan">
                                                <thead class="bg-gray disabled color-palette">
                                                    <tr>
                                                        <th class="padat">No</th>
                                                        <th>Waktu / Tanggal</th>
                                                        <th>Nama Program</th>
                                                        <th>Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>DOKUMEN / KELENGKAPAN PENDUDUK</strong>
                                    </th>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="table-responsive">
                                            <table
                                                class="table table-bordered dataTable table-striped table-hover tabel-daftar"
                                                id="table-dokumen">
                                                <thead class="bg-gray disabled color-palette">
                                                    <tr>
                                                        <th>No</th>
                                                        {{-- <th>Aksi</th> --}}
                                                        <th>Nama Dokumen</th>
                                                        <th>Tanggal Upload</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script nonce="{{ csp_nonce() }}"  >
    document.addEventListener("DOMContentLoaded", function(event) {
        const header = @include('layouts.components.header_bearer_api_gabungan');
        $.ajax({                
                url: `{{ config('app.databaseGabunganUrl').'/api/v1/penduduk' }}?filter[id]={{ $penduduk->id }}`,
                headers: header,
                method: 'get',
            })
            .then(function(response) {
                var data = response.data[0]?.attributes

                let hrefTag = data.urlFoto ? 'src=' + data
                                .urlFoto : `src="{{ asset('assets/img/avatar.png') }}"`;

                $('#nik').text(`Biodata Penduduk (NIK : ${data.nik})`)
                $('#foto').html(`<img class="penduduk" ${hrefTag} alt="Foto Penduduk">`)
                $('#status-dasar').html(`<strong>${data.penduduk_status_dasar?.nama}</strong>`)
                $('#nama').text(data.nama)
                $('#terdaftar-pada').html(`Terdaftar pada: <i class="fa fa-clock-o"></i>${data.created_at}`)
                $('#wajib-identitas').text(data.wajibKTP)
                $('#identitas-el').text(data.elKTP)
                $('#status-rekam').text(data.status_rekam_ktp?.nama)
                $('#tag-id-card').text(data.tag_id_card)
                $('#no-kk').text(data.keluarga?.no_kk)
                $('#no-kk-sebelumnya').text(data.no_kk_sebelumnya)
                $('#penduduk-hubungan').text(data.penduduk_hubungan?.nama)
                $('#jk').text(data.jenis_kelamin?.nama)
                $('#agama').text(data.agama?.nama)
                $('#penduduk-status').text(data.penduduk_status?.nama)
                $('#akta-lahir').text(data.akta_lahir)
                $('#ttl').text(`${data.tempatlahir} / ${data.tanggallahir}`)
                $('#tempat-lahir').text(data.namaTempatDilahirkan)
                $('#jenis-lahir').text(data.namaTJenisKelahiran)
                $('#lahir-ke').text(data.kelahiran_anak_ke)
                $('#penolong-lahir').text(data.namaPenolongKelahiran)
                $('#berat-lahir').text(`${data.berat_lahir ?? ''} gram`)
                $('#panjang-lahir').text(`${data.panjang_lahir ?? ''} cm`)
                $('#pendidikan-kk').text(data.pendidikan_k_k?.nama)
                $('#pendidikan').text(data.pendidikan?.nama)
                $('#pekerjaan').text(data.pekerjaan?.nama)
                $('#suku').text(data.suku)
                $('#wna').text(data.warga_negara?.nama)
                $('#no-passpor').text(data.dokumen_pasport)
                $('#tgl-passpor').text(data.tanggal_akhir_paspor)
                $('#no-kitas').text(data.dokumen_kitas)
                $('#nik-ayah').text(data.ayah_nik)
                $('#nama-ayah').text(data.nama_ayah)
                $('#nik-ibu').text(data.ibu_nik)
                $('#nama-ibu').text(data.nama_ibu)
                $('#alamat').text(data.keluarga?.alamat)
                $('#dusun').text(data.cluster_desa?.dusun)
                $('#rt-rw').text(`${data.cluster_desa?.rt} / ${data.cluster_desa?.rw}`)
                $('#alm-sebelum').text(data.alamat_sebelumnya)
                $('#telepon').text(data.telepon)
                $('#email').text(data.email)
                $('#telegram').text(data.telegram)
                $('#cara-hubung').text(data.hubung_warga)
                $('#status-kawin').text(data.statusPerkawinan)
                $('#gol-darah').text(data.golongan_darah?.nama)
                $('#cacat').text(data.cacat?.nama)
                $('#sakit').text(data.namaSakitMenahun)
                $('#asuransi').text(data.namaAsuransi)
                $('#bpjs').text(data.bpjs_ketenagakerjaan)
                $('#bahasa').text(data.bahasa?.nama)
                $('#ket').text(data.ket)
            })

        var bantuan = $('#table-bantuan').DataTable({
            processing: true,
            serverSide: false,
            autoWidth: false,
            ordering: false,
            searching: false,
            paging: false,
            info: false,
            ajax: {
                url: `{{ config('app.databaseGabunganUrl').'/api/v1/bantuan/peserta' }}?filter[peserta]={{ $penduduk->nik }}`,
                headers: header,
                method: 'get',
            },
            columns: [{
                    data: null,
                },
                {
                    data: function(data) {
                        return `${data.attributes.program.sdate} - ${data.attributes.program.edate}`
                    }
                },
                {
                    data: "attributes.program.nama"
                },
                {
                    data: "attributes.program.ndesc"
                }
            ]
        })

        bantuan.on('draw.dt', function() {
            var PageInfo = $('#table-bantuan').DataTable().page.info();
            bantuan.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        var dokumen = $('#table-dokumen').DataTable({
            processing: true,
            serverSide: false,
            autoWidth: false,
            ordering: false,
            searching: false,
            paging: false,
            info: false,
            ajax: {
                url: `{{ config('app.databaseGabunganUrl').'/api/v1/dokumen' }}?filter[id_pend]={{ $penduduk->id }}`,
                headers: header,
                method: 'get',
            },
            columns: [{
                    data: null,
                },
                // {
                //     data: "attributes.satuan"
                // },
                {
                    data: "attributes.nama"
                },
                {
                    data: "attributes.tgl_upload"
                }
            ]
        })

        dokumen.on('draw.dt', function() {
            var PageInfo = $('#table-dokumen').DataTable().page.info();
            dokumen.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });
    });
    </script>
@endsection
