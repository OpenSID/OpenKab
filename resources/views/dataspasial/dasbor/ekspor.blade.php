<?php header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="dasbor-dataspasial-'.date('Ymd-His').'.xls"'); ?>
<table border="1" cellspacing="0" cellpadding="4">
    <thead><tr><th>Indikator</th><th>Satuan</th><th>Ukuran</th><th>Nilai</th><th>Tersedia</th></tr></thead>
    <tbody>
        @foreach (($rows ?? []) as $r)
            <tr><td>{{ $r['label'] ?? $r['kode'] ?? '-' }}</td><td>{{ $r['satuan'] ?? '-' }}</td><td>{{ $r['ukuran'] ?? '-' }}</td><td>{{ $r['nilai'] ?? '-' }}</td><td>{{ $r['tersedia'] ? 'Ya' : 'Tidak' }}</td></tr>
        @endforeach
    </tbody>
</table>
