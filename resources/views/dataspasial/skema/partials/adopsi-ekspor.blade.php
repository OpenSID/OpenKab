{{--
    Ekspor tabel status adopsi desa (KAB-03 / US4).

    Berkas berisi SELURUH hasil saringan - bukan hanya halaman aktif (FR-066) -
    memakai pola `LaporanDesaAktifController::exportExcel()` (HTML -> .xls).
    Ekspor tidak pernah mengirim permintaan terbitan (FR-047).
--}}
<table class="table table-bordered table-sm mb-0">
    <thead>
        <tr>
            <th>No</th>
            <th>Desa</th>
            <th>Kode Desa</th>
            <th>Versi Skema</th>
            <th>Status Adopsi</th>
            <th>Terakhir Ada Data</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($baris as $i => $b)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $b['nama_desa'] }}</td>
                <td>{{ $b['kode_desa'] }}</td>
                <td>{{ $b['nomor_versi_label'] }}</td>
                <td>{{ $b['status_label'] }}</td>
                <td>{{ $b['terakhir_ada_data_label'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="table table-borderless table-sm mt-2 mb-0">
    <tbody>
        <tr><th>Sektor</th><td>{{ $sektorKode }}</td></tr>
        <tr><th>Waktu pembuatan</th><td>{{ now()->translatedFormat('d M Y H:i') }}</td></tr>
        <tr>
            <th>Kriteria saringan</th>
            <td>
                status={{ filled($filter['status'] ?? null) ? $filter['status'] : 'semua' }},
                kata kunci={{ filled($filter['q'] ?? null) ? $filter['q'] : '-' }}
            </td>
        </tr>
        <tr>
            <th>Jumlah baris</th>
            <td>
                {{ count($baris) }} dari {{ $total }} desa
                @if (! empty($dipangkas))
                    &mdash; dipangkas pada batas ekspor; {{ $total - count($baris) }} desa tidak disertakan
                @endif
            </td>
        </tr>
        <tr>
            <th>Ringkasan</th>
            <td>
                total={{ $ringkasan['total_desa'] }},
                tertinggal={{ $ringkasan['tertinggal'] }},
                menunggu adopsi={{ $ringkasan['menunggu_adopsi'] }},
                berkonflik=-
            </td>
        </tr>
    </tbody>
</table>
