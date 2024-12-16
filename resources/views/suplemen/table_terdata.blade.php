<div class="table-responsive">
    <table class="table table-bordered table-hover" id="suplemen">
        <thead>
            <tr>
                <th class="padat">NO</th>
                <th>{{ $suplemen->sasaran == 1 ? 'NO.' : 'NIK' }} KK</th>
                <th>{{ $suplemen->sasaran == 1 ? 'NIK PENDUDUK' : 'NO. KK' }}</th>
                <th>{{ $suplemen->sasaran == 1 ? 'NAMA PENDUDUK' : 'KEPALA KELUARGA' }}</th>
                <th>TEMPAT LAHIR</th>
                <th>TANGGAL LAHIR</th>
                <th>JENIS KELAMIN</th>
                <th>ALAMAT</th>
                <th>KETERANGAN</th>
            </tr>
        </thead>
    </table>
</div>