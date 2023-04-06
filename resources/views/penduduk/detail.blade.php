@extends('layouts.index')

@section('title', 'Biodata Penduduk')

@section('content_header')
    <h1>Biodata Penduduk</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="float-left">
                        <div class="btn-group">
                            <a href="{{ url('penduduk') }}" class="btn btn-sm btn-block btn-secondary"><i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h3 class="card-title">Biodata Penduduk (NIK : 0150505200107066)</h3>
                    <br>
                    <p class="text-sm font-italic">
                        Terdaftar pada:
                        <i class="fa fa-clock-o"></i>12 Maret 2023 23:25:16 <i class="fa fa-user"></i> Ketut Asmaul
                    </p>
                    <p class="text-sm font-italic">
                        Terakhir diubah:
                        <i class="fa fa-clock-o"></i>27 Maret 2023 16:21:40 <i class="fa fa-user"></i> Ketut Asmaul
                    </p>
                    <div class="table-responsive">
                        <div class="table-responsive">
                        </div>
                        <table class="table table-bordered table-striped table-hover">
                            <tbody>
                                <tr class="text-center">
                                    <td colspan="3">
                                        <img class="penduduk"
                                            src="https://berputar.opendesa.id/assets/images/pengguna/kuser.png"
                                            alt="Foto Penduduk">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered table-striped table-hover">
                            <tbody>
                                <tr>
                                    <td>Status Dasar</td>
                                    <td>:</td>
                                    <td><span class=""><strong>HIDUP</strong></span></td>
                                </tr>
                                <tr>
                                    <td width="300">Nama</td>
                                    <td width="1">:</td>
                                    <td>ARIF SOFIYAN</td>
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
                                                    <td>BELUM</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Nomor Kartu Keluarga</td>
                                    <td>:</td>
                                    <td>
                                        7894566656565666 </td>
                                </tr>
                                <tr>
                                    <td>Nomor KK Sebelumnya</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Hubungan Dalam Keluarga</td>
                                    <td>:</td>
                                    <td>KEPALA KELUARGA</td>
                                </tr>
                                <tr>
                                    <td>Jenis Kelamin</td>
                                    <td>:</td>
                                    <td>LAKI-LAKI</td>
                                </tr>
                                <tr>
                                    <td>Agama</td>
                                    <td>:</td>
                                    <td>ISLAM</td>
                                </tr>
                                <tr>
                                    <td>Status Penduduk</td>
                                    <td>:</td>
                                    <td>TETAP</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>DATA KELAHIRAN</strong></th>
                                </tr>
                                <tr>
                                    <td>Akta Kelahiran</td>
                                    <td>:</td>
                                    <td>1234355466</td>
                                </tr>
                                <tr>
                                    <td>Tempat / Tanggal Lahir</td>
                                    <td>:</td>
                                    <td>ASD / 02-03-2023</td>
                                </tr>
                                <tr>
                                    <td>Tempat Dilahirkan</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Jenis Kelahiran</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Kelahiran Anak Ke</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Penolong Kelahiran</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Berat Lahir</td>
                                    <td>:</td>
                                    <td> Gram</td>
                                </tr>
                                <tr>
                                    <td>Panjang Lahir</td>
                                    <td>:</td>
                                    <td> cm</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>PENDIDIKAN DAN PEKERJAAN</strong></th>
                                </tr>
                                <tr>
                                    <td>Pendidikan dalam KK</td>
                                    <td>:</td>
                                    <td>TIDAK / BELUM SEKOLAH</td>
                                </tr>
                                <tr>
                                    <td>Pendidikan sedang ditempuh</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Pekerjaan</td>
                                    <td>:</td>
                                    <td>MENGURUS RUMAH TANGGA</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>DATA KEWARGANEGARAAN</strong></th>
                                </tr>
                                <tr>
                                    <td>Suku/Etnis</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Warga Negara</td>
                                    <td>:</td>
                                    <td>WNA</td>
                                </tr>
                                <tr>
                                    <td>Nomor Paspor</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Tanggal Berakhir Paspor</td>
                                    <td>:</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>Nomor KITAS/KITAP</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>ORANG TUA</strong></th>
                                </tr>
                                <tr>
                                    <td>NIK Ayah</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Nama Ayah</td>
                                    <td>:</td>
                                    <td>ASDASD</td>
                                </tr>
                                <tr>
                                    <td>NIK Ibu</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Nama Ibu</td>
                                    <td>:</td>
                                    <td>ASDASDAS</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>ALAMAT</strong></th>
                                </tr>
                                <tr>
                                    <td>Alamat</td>
                                    <td>:</td>
                                    <td>DONUJHY</td>
                                </tr>
                                <tr>
                                    <td>Dusun</td>
                                    <td>:</td>
                                    <td>IMANUNGGAL LORI</td>
                                </tr>
                                <tr>
                                    <td>RT/ RW</td>
                                    <td>:</td>
                                    <td>01 / -</td>
                                </tr>
                                <tr>
                                    <td>Alamat Sebelumnya</td>
                                    <td>:</td>
                                    <td>JIRRR</td>
                                </tr>
                                <tr>
                                    <td>Nomor Telepon</td>
                                    <td>:</td>
                                    <td>082373040360</td>
                                </tr>
                                <tr>
                                    <td>Alamat Email</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Telegram</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Cara Hubung Warga</td>
                                    <td>:</td>
                                    <td>Email</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>STATUS KAWIN</strong></th>
                                </tr>
                                <tr>
                                    <td>Status Kawin</td>
                                    <td>:</td>
                                    <td>BELUM KAWIN</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>DATA KESEHATAN</strong></th>
                                </tr>
                                <tr>
                                    <td>Golongan Darah</td>
                                    <td>:</td>
                                    <td>A</td>
                                </tr>
                                <tr>
                                    <td>Cacat</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Sakit Menahun</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Nama/Nomor Asuransi Kesehatan</td>
                                    <td>:</td>
                                    <td> / </td>
                                </tr>
                                <tr>
                                    <td>Nomor BPJS Ketenagakerjaan</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>

                                <tr>
                                    <th colspan="3" class="subtitle_head"><strong>DATA LAINNYA</strong></th>
                                </tr>
                                <tr>
                                    <td>Bahasa</td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Keterangan</td>
                                    <td>:</td>
                                    <td></td>
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
                                                class="table table-bordered dataTable table-striped table-hover tabel-daftar">
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
                                                class="table table-bordered dataTable table-striped table-hover tabel-daftar">
                                                <thead class="bg-gray disabled color-palette">
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Aksi</th>
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
