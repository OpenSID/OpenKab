<!DOCTYPE html>
<html>

<head>
    <title>Cetak Laporan Bulanan</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="{{ asset('css/report.css') }}" rel="stylesheet" type="text/css">
</head>
<style type="text/css">
    .underline {
        text-decoration: underline;
    }

    td.judul {
        font-size: 14pt;
        font-weight: bold;
    }

    td.judul2 {
        font-size: 12pt;
        font-weight: bold;
    }

    td.text-bold {
        font-weight: bold;
    }

    table.tftable td.no-border {
        border: 0px;
        border-style: hidden;
    }

    table.tftable td.no-border-kecuali-kiri {
        border-top-style: hidden;
        border-bottom-style: hidden;
        border-right-style: hidden;
    }

    table.tftable td.no-border-kecuali-atas {
        border-left-style: hidden;
        border-bottom-style: hidden;
        border-right-style: hidden;
    }

    table.tftable td.no-border-kecuali-bawah {
        border-left-style: hidden;
        border-top-style: hidden;
        border-right-style: hidden;
    }

    table.tftable {
        margin-top: 5px;
        font-size: 12px;
        color: {{ $warna_font ?? '' }};
        width: 100%;
        border-width: 1px;
        border-style: solid;
        border-color: {{ $warna_border ?? '' }};
        border-collapse: collapse;
    }

    table.tftable.lap-bulanan {
        border-width: 3px;
    }

    table.tftable tr.thick {
        border-width: 3px;
        border-style: solid;
    }

    table.tftable th.thick {
        border-width: 3px;
    }

    table.tftable th.thick-kiri {
        border-left: 3px solid {{ $warna_border ?? '' }};
    }

    table.tftable td.thick-kanan {
        border-right: 3px solid {{ $warna_border ?? '' }};
    }

    table.tftable td.angka {
        text-align: right;
    }

    table.tftable th {
        background-color: lightgray;
        padding: 3px;
        border: 1px solid {{ $warna_border ?? '' }};
        text-align: center;
    }

    /*table.tftable tr {background-color:#ffffff;}*/
    table.tftable td {
        padding: 8px;
        border: 1px solid {{ $warna_border ?? '' }};
    }
</style>

<body>
    <div id="container">
        <!-- Print Body -->
        <div id="body">
            <table>
                <tr>
                    <td colspan="11" class='text-bold'>PEMERINTAH KABUPATEN/KOTA</td>
                    <td colspan="2" class="text-bold"><span
                            style="float: right; border: solid 1px black; font-size: 12pt; text-align: center; padding: 5px 20px;">LAMPIRAN
                            A-9</span></td>
                </tr>
                <tr>
                    <td colspan="2" class="text"><span
                            style="border-bottom: 2px solid;">{{ strtoupper($identitasAplikasi['nama_kabupaten']) }}</span>
                    </td>
                    <td colspan="11">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                    <td colspan="10" class="judul" style="padding-bottom: 10px;"><span
                            style="border-bottom: 2px solid;">LAPORAN BULANAN
                            {{ strtoupper(config('app.sebutanDesa')) }}</span>
                    </td>
                <tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                    <td colspan="3" class="text-bold">Kabupaten</td>
                    <td colspan="7">: {{ strtoupper($identitasAplikasi['nama_kabupaten']) }}</td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                    <td colspan="3" class="text-bold">Laporan Bulan</td>
                    <td colspan="7">: {{ bulan($bulan) }} {{ $tahun }}</td>
                </tr>
            </table>
            <br>
            @include('laporan-bulanan.table_bulanan')
            <table class="tftable">
                <tr>
                    <td colspan="13" class="no-border">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="8" class="judul2 no-border-kecuali-bawah" style="padding-bottom: 10px;">
                        <span style="border-bottom: 2px solid;">PERINCIAN PINDAH</span>
                    </td>
                    <td colspan="5" class="no-border">&nbsp;</td>
                </tr>
                <tr>
                    <th rowspan="2" width='2%' class="text-center">NO</th>
                    <th rowspan="2" width='20%' class="text-center">KETERANGAN</th>
                    <th colspan="3" class="text-center">PENDUDUK</th>
                    <th colspan="3" class="text-center">KELUARGA (KK)</th>
                    <td rowspan="7" colspan="2" width="30%" class="no-border-kecuali-kiri">&nbsp;</td>
                    <td></td>
                    {{-- <td rowspan="2" colspan="3" class="no-border" style="vertical-align: top;">
                        {{ ucwords($sebutan_wilayah) }} {{ $profil->nama_kecamatan }}, {{ \Carbon\Carbon::createFromFormat('Y m d', $tanggal)->locale('id')->translatedFormat('d F Y') }}<br>
                        {{ str_ireplace($sebutan_wilayah, '', $pamong_ttd['pamong_jabatan']) . ' ' . ucwords($sebutan_wilayah) . ' ' . $profil->nama_kecamatan }}
                    </td> --}}
                </tr>
                <tr>
                    <th class="text-center">L</th>
                    <th class="text-center">P</th>
                    <th class="text-center">L+P</th>
                    <th class="text-center">L</th>
                    <th class="text-center">P</th>
                    <th class="text-center">L+P</th>
                </tr>
                <tr>
                    <td class="no_urut">1</td>
                    <td>Pindah keluar {{ config('app.sebutanDesa') }}</td>
                    <td class="bilangan">{{ $rincian_pindah['DESA_L'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['DESA_P'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['DESA_L'] + $rincian_pindah['DESA_P'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['DESA_KK_L'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['DESA_KK_P'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['DESA_KK_L'] + $rincian_pindah['DESA_KK_P'] ?? '-' }}</td>
                    <td colspan="3" class="no-border">&nbsp;</td>
                </tr>
                <tr>
                    <td class="no_urut">2</td>
                    <td>Pindah keluar Kecamatan</td>
                    <td class="bilangan">{{ $rincian_pindah['KEC_L'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['KEC_P'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['KEC_L'] + $rincian_pindah['KEC_P'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['KEC_KK_L'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['KEC_KK_P'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['KEC_KK_L'] + $rincian_pindah['KEC_KK_P'] ?? '-' }}</td>
                    <td colspan="3" class="no-border">&nbsp;</td>
                </tr>
                <tr>
                    <td class="no_urut">3</td>
                    <td>Pindah keluar Kabupaten/Kota</td>
                    <td class="bilangan">{{ $rincian_pindah['KAB_L'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['KAB_P'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['KAB_L'] + $rincian_pindah['KAB_P'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['KAB_KK_L'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['KAB_KK_P'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['KAB_KK_L'] + $rincian_pindah['KAB_KK_P'] ?? '-' }}</td>
                    <td colspan="3" class="no-border">&nbsp;</td>
                </tr>
                <tr>
                    <td class="no_urut">4</td>
                    <td>Pindah keluar Provinsi</td>
                    <td class="bilangan">{{ $rincian_pindah['PROV_L'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['PROV_P'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['PROV_L'] + $rincian_pindah['PROV_P'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['PROV_KK_L'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['PROV_KK_P'] ?? '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['PROV_KK_L'] + $rincian_pindah['PROV_KK_P'] ?? '-' }}</td>
                    <td></td>
                    {{-- <td rowspan="2" colspan="3" class="no-border" style="vertical-align: top;">
                        ( {{ $pamong_ttd['pamong_nama'] }} )<br>
                        NIP/{{ setting('sebutan_nip_desa') }} {{ $pamong_ttd['pamong_nip'] ?? $pamong_ttd['pamong_niap'] }}
                    </td> --}}
                </tr>
                <tr>
                    <td colspan="2" class="text-center text-bold">JUMLAH:</td>
                    <td class="bilangan">{{ $rincian_pindah['TOTAL_L'], '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['TOTAL_P'], '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['TOTAL_L'] + $rincian_pindah['TOTAL_P'], '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['TOTAL_KK_L'], '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['TOTAL_KK_P'], '-' }}</td>
                    <td class="bilangan">{{ $rincian_pindah['TOTAL_KK_L'] + $rincian_pindah['TOTAL_KK_P'], '-' }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
