@push('css')
    <style type="text/css">
        .italic {
            font-style: italic;
        }

        td a {
            color: rgb(6, 0, 193);
        }

        td a:hover {
            color: rgb(6, 0, 193);
            text-decoration: underline;
        }
    </style>
@endpush
<div class="table-responsive">
    <table id="tfhover" class="table table-bordered table-hover tftable lap-bulanan">
        <thead class="bg-gray">
            <tr>
                <th rowspan="3" width='2%' class="text-center">No</th>
                <th rowspan="3" colspan="2" width='30%' class="text-center">Perincian</th>
                <th colspan="7" width='45%' class="text-center">Penduduk</th>
                <th colspan="3" rowspan="2" width='23%'class="text-center">Keluarga (KK)</th>
            </tr>
            <tr>
                <th colspan="2" class="text-center">WNI</th>
                <th colspan="2" class="text-center">WNA</th>
                <th colspan="3" class="text-center">Jumlah</th>
            </tr>
            <tr>
                <th class="text-center">L</th>
                <th class="text-center">P</th>
                <th class="text-center">L</th>
                <th class="text-center">P</th>
                <th class="text-center">L</th>
                <th class="text-center">P</th>
                <th class="text-center">L+P</th>
                <th class="text-center">L</th>
                <th class="text-center">P</th>
                <th class="text-center">L+P</th>
            </tr>
            <tr>
                <th class="text-center italic">1</th>
                <th class="text-center italic" colspan="2">2</th>
                <th class="text-center italic">3</th>
                <th class="text-center italic">4</th>
                <th class="text-center italic">5</th>
                <th class="text-center italic">6</th>
                <th class="text-center italic">7</th>
                <th class="text-center italic">8</th>
                <th class="text-center italic">9</th>
                <th class="text-center italic">10</th>
                <th class="text-center italic">11</th>
                <th class="text-center italic">12</th>
            </tr>
        </thead>
        <tbody>
            @if($dataPenduduk)
            <tr>
                <td class="no_urut">1</td>
                <td colspan="2">Penduduk/Keluarga awal bulan ini</td>
                <td class="bilangan">
                {!! $penduduk_awal['WNI_L']
                    ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'awal', 'tipe' => 'wni_l']) . '">' . $penduduk_awal['WNI_L'] . '</a>'
                    : '-' 
                !!}
                </td>
                <td class="bilangan">{!! $penduduk_awal['WNI_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'awal', 'tipe' => 'wni_p']) . '">' . $penduduk_awal['WNI_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_awal['WNA_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'awal', 'tipe' => 'wna_l']) . '">' . $penduduk_awal['WNA_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_awal['WNA_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'awal', 'tipe' => 'wna_p']) . '">' . $penduduk_awal['WNA_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_awal['WNI_L'] + $penduduk_awal['WNA_L'] != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'awal', 'tipe' => 'jml_l']) . '">' . ($penduduk_awal['WNI_L'] + $penduduk_awal['WNA_L']) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_awal['WNI_P'] + $penduduk_awal['WNA_P'] != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'awal', 'tipe' => 'jml_p']) . '">' . ($penduduk_awal['WNI_P'] + $penduduk_awal['WNA_P']) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_awal['WNI_L'] + $penduduk_awal['WNA_L'] + ($penduduk_awal['WNI_P'] + $penduduk_awal['WNA_P']) != 0
                    ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'awal', 'tipe' => 'jml']) . '">' . ($penduduk_awal['WNI_L'] + $penduduk_awal['WNA_L'] + ($penduduk_awal['WNI_P'] + $penduduk_awal['WNA_P'])) . '</a>'
                    : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_awal['KK_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'awal', 'tipe' => 'kk_l']) . '">' . $penduduk_awal['KK_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_awal['KK_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'awal', 'tipe' => 'kk_p']) . '">' . $penduduk_awal['KK_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_awal['KK'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'awal', 'tipe' => 'kk']) . '">' . $penduduk_awal['KK'] . '</a>' : '-' !!}</td>
            </tr>
            <tr>
                <td class="no_urut">2</td>
                <td colspan="2">Kelahiran/Keluarga baru bulan ini</td>
                <td class="bilangan">{!! $kelahiran['WNI_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'lahir', 'tipe' => 'wni_l']) . '">' . $kelahiran['WNI_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kelahiran['WNI_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'lahir', 'tipe' => 'wni_p']) . '">' . $kelahiran['WNI_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kelahiran['WNA_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'lahir', 'tipe' => 'wna_l']) . '">' . $kelahiran['WNA_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kelahiran['WNA_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'lahir', 'tipe' => 'wna_p']) . '">' . $kelahiran['WNA_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kelahiran['WNI_L'] + $kelahiran['WNA_L'] != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'lahir', 'tipe' => 'jml_l']) . '">' . ($kelahiran['WNI_L'] + $kelahiran['WNA_L']) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kelahiran['WNI_P'] + $kelahiran['WNA_P'] != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'lahir', 'tipe' => 'jml_p']) . '">' . ($kelahiran['WNI_P'] + $kelahiran['WNA_P']) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kelahiran['WNI_L'] + $kelahiran['WNA_L'] + ($kelahiran['WNI_P'] + $kelahiran['WNA_P']) != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'lahir', 'tipe' => 'jml']) . '">' . ($kelahiran['WNI_L'] + $kelahiran['WNA_L'] + ($kelahiran['WNI_P'] + $kelahiran['WNA_P'])) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kelahiran['KK_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'lahir', 'tipe' => 'kk_l']) . '">' . $kelahiran['KK_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kelahiran['KK_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'lahir', 'tipe' => 'kk_p']) . '">' . $kelahiran['KK_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kelahiran['KK'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'lahir', 'tipe' => 'kk']) . '">' . $kelahiran['KK'] . '</a>' : '-' !!}</td>
            </tr>
            <tr>
                <td class="no_urut">3</td>
                <td colspan="2">Kematian bulan ini</td>
                <td class="bilangan">{!! $kematian['WNI_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'mati', 'tipe' => 'wni_l']) . '">' . $kematian['WNI_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kematian['WNI_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'mati', 'tipe' => 'wni_p']) . '">' . $kematian['WNI_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kematian['WNA_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'mati', 'tipe' => 'wna_l']) . '">' . $kematian['WNA_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kematian['WNA_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'mati', 'tipe' => 'wna_p']) . '">' . $kematian['WNA_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kematian['WNI_L'] + $kematian['WNA_L'] != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'mati', 'tipe' => 'jml_l']) . '">' . ($kematian['WNI_L'] + $kematian['WNA_L']) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kematian['WNI_P'] + $kematian['WNA_P'] != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'mati', 'tipe' => 'jml_p']) . '">' . ($kematian['WNI_P'] + $kematian['WNA_P']) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kematian['WNI_L'] + $kematian['WNA_L'] + ($kematian['WNI_P'] + $kematian['WNA_P']) != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'mati', 'tipe' => 'jml']) . '">' . ($kematian['WNI_L'] + $kematian['WNA_L'] + ($kematian['WNI_P'] + $kematian['WNA_P'])) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kematian['KK_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'mati', 'tipe' => 'kk_;']) . '">' . $kematian['KK_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kematian['KK_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'mati', 'tipe' => 'kk_p']) . '">' . $kematian['KK_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $kematian['KK'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'mati', 'tipe' => 'kk']) . '">' . $kematian['KK'] . '</a>' : '-' !!}</td>
            </tr>
            <tr>
                <td class="no_urut">4</td>
                <td colspan="2">Pendatang bulan ini</td>
                <td class="bilangan">{!! $pendatang['WNI_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'datang', 'tipe' => 'wni_l']) . '">' . $pendatang['WNI_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pendatang['WNI_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'datang', 'tipe' => 'wni_p']) . '">' . $pendatang['WNI_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pendatang['WNA_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'datang', 'tipe' => 'wna_l']) . '">' . $pendatang['WNA_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pendatang['WNA_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'datang', 'tipe' => 'wna_p']) . '">' . $pendatang['WNA_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pendatang['WNI_L'] + $pendatang['WNA_L'] != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'datang', 'tipe' => 'jml_l']) . '">' . ($pendatang['WNI_L'] + $pendatang['WNA_L']) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pendatang['WNI_P'] + $pendatang['WNA_P'] != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'datang', 'tipe' => 'jml_p']) . '">' . ($pendatang['WNI_P'] + $pendatang['WNA_P']) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pendatang['WNI_L'] + $pendatang['WNA_L'] + ($pendatang['WNI_P'] + $pendatang['WNA_P']) != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'datang', 'tipe' => 'jml']) . '">' . ($pendatang['WNI_L'] + $pendatang['WNA_L'] + ($pendatang['WNI_P'] + $pendatang['WNA_P'])) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pendatang['KK_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'datang', 'tipe' => 'kk_l']) . '">' . $pendatang['KK_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pendatang['KK_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'datang', 'tipe' => 'kk_p']) . '">' . $pendatang['KK_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pendatang['KK'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'datang', 'tipe' => 'kk']) . '">' . $pendatang['KK'] . '</a>' : '-' !!}</td>
            </tr>
            <tr>
                <td class="no_urut">5</td>
                <td colspan="2">Pindah/Keluarga pergi bulan ini</td>
                <td class="bilangan">{!! $pindah['WNI_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'pindah', 'tipe' => 'wni_l']) . '">' . $pindah['WNI_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pindah['WNI_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'pindah', 'tipe' => 'wni_p']) . '">' . $pindah['WNI_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pindah['WNA_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'pindah', 'tipe' => 'wna_l']) . '">' . $pindah['WNA_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pindah['WNA_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'pindah', 'tipe' => 'wna_p']) . '">' . $pindah['WNA_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pindah['WNI_L'] + $pindah['WNA_L'] != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'pindah', 'tipe' => 'jml']) . '">' . ($pindah['WNI_L'] + $pindah['WNA_L']) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pindah['WNI_P'] + $pindah['WNA_P'] != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'pindah', 'tipe' => 'jml_l']) . '">' . ($pindah['WNI_P'] + $pindah['WNA_P']) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pindah['WNI_L'] + $pindah['WNA_L'] + ($pindah['WNI_P'] + $pindah['WNA_P']) != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'pindah', 'tipe' => 'jml_p']) . '">' . ($pindah['WNI_L'] + $pindah['WNA_L'] + ($pindah['WNI_P'] + $pindah['WNA_P'])) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pindah['KK_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'pindah', 'tipe' => 'kk_l']) . '">' . $pindah['KK_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pindah['KK_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'pindah', 'tipe' => 'kk_p']) . '">' . $pindah['KK_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $pindah['KK'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'pindah', 'tipe' => 'kk']) . '">' . $pindah['KK'] . '</a>' : '-' !!}</td>
            </tr>
            <tr>
                <td class="no_urut">6</td>
                <td colspan="2">Penduduk hilang bulan ini</td>
                <td class="bilangan">{!! $hilang['WNI_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'hilang', 'tipe' => 'wni_l']) . '">' . $hilang['WNI_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $hilang['WNI_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'hilang', 'tipe' => 'wni_p']) . '">' . $hilang['WNI_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $hilang['WNA_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'hilang', 'tipe' => 'wna_l']) . '">' . $hilang['WNA_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $hilang['WNA_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'hilang', 'tipe' => 'wna_p']) . '">' . $hilang['WNA_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $hilang['WNI_L'] + $hilang['WNA_L'] != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'hilang', 'tipe' => 'jml']) . '">' . ($hilang['WNI_L'] + $hilang['WNA_L']) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $hilang['WNI_P'] + $hilang['WNA_P'] != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'hilang', 'tipe' => 'jml_l']) . '">' . ($hilang['WNI_P'] + $hilang['WNA_P']) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $hilang['WNI_L'] + $hilang['WNA_L'] + ($hilang['WNI_P'] + $hilang['WNA_P']) != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'hilang', 'tipe' => 'jml_p']) . '">' . ($hilang['WNI_L'] + $hilang['WNA_L'] + ($hilang['WNI_P'] + $hilang['WNA_P'])) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $hilang['KK_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'hilang', 'tipe' => 'kk_l']) . '">' . $hilang['KK_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $hilang['KK_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'hilang', 'tipe' => 'kk_p']) . '">' . $hilang['KK_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $hilang['KK'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'hilang', 'tipe' => 'kk']) . '">' . $hilang['KK'] . '</a>' : '-' !!}</td>
            </tr>
            <tr>
                <td class="no_urut">7</td>
                <td colspan="2">Penduduk/Keluarga akhir bulan ini</td>
                <td class="bilangan">{!! $penduduk_akhir['WNI_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'akhir', 'tipe' => 'wni_l']) . '">' . $penduduk_akhir['WNI_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_akhir['WNI_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'akhir', 'tipe' => 'wni_p']) . '">' . $penduduk_akhir['WNI_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_akhir['WNA_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'akhir', 'tipe' => 'wna_l']) . '">' . $penduduk_akhir['WNA_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_akhir['WNA_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'akhir', 'tipe' => 'wna_p']) . '">' . $penduduk_akhir['WNA_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_akhir['WNI_L'] + $penduduk_akhir['WNA_L'] != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'akhir', 'tipe' => 'jml']) . '">' . ($penduduk_akhir['WNI_L'] + $penduduk_akhir['WNA_L']) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_akhir['WNI_P'] + $penduduk_akhir['WNA_P'] != 0 ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'akhir', 'tipe' => 'jml_l']) . '">' . ($penduduk_akhir['WNI_P'] + $penduduk_akhir['WNA_P']) . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_akhir['WNI_L'] + $penduduk_akhir['WNA_L'] + ($penduduk_akhir['WNI_P'] + $penduduk_akhir['WNA_P']) != 0
                    ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'akhir', 'tipe' => 'jml_p']) . '">' . ($penduduk_akhir['WNI_L'] + $penduduk_akhir['WNA_L'] + ($penduduk_akhir['WNI_P'] + $penduduk_akhir['WNA_P'])) . '</a>'
                    : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_akhir['KK_L'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'akhir', 'tipe' => 'kk_l']) . '">' . $penduduk_akhir['KK_L'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_akhir['KK_P'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'akhir', 'tipe' => 'kk_p']) . '">' . $penduduk_akhir['KK_P'] . '</a>' : '-' !!}</td>
                <td class="bilangan">{!! $penduduk_akhir['KK'] ? '<a href="' . route('laporan-bulanan.detail-penduduk', ['rincian' => 'akhir', 'tipe' => 'kk']) . '">' . $penduduk_akhir['KK'] . '</a>' : '-' !!}</td>
            </tr>
            @else
            <tr>
                <td colspan="13" align="center">DATA TIDAK DI TEMUKAND</td>
            </tr>
            @endif
        </tbody>

    </table>
</div>
