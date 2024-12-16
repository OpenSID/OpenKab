<table>
    <tbody>
        <tr>
            <td>
                @if ($aksi != 'unduh')
                    <img class="logo" src="{{ asset('web/img/logo.png') }}" alt="logo-desa">
                @endif
                <h1 class="judul">
                    @if($nama_kabupaten)
                    PEMERINTAH  {!! strtoupper('Kabupaten' . ' ' . $nama_kabupaten . ' <br>Kecamatan ' . $nama_kecamatan . ' <br>Desa ' . $nama_desa) !!}
                    @else
                        @if(session('kabupaten.nama_kabupaten'))
                            PEMERINTAH 
                            {!! session('kabupaten.nama_kabupaten') ? 'KABUPATEN ' . strtoupper(session('kabupaten.nama_kabupaten')) : '' !!} 
                            {!! session('kecamatan.nama_kecamatan') ? '<br>KECAMATAN ' . strtoupper(session('kecamatan.nama_kecamatan')) : '' !!} 
                            {!! session('desa.nama_desa') ? '<br>DESA ' . strtoupper(session('desa.nama_desa')) : '' !!}

                        @else
                            {{ strtoupper($identitasAplikasi['nama_aplikasi']) }}
                        @endif
                    @endif
                    <h1>
            </td>
        </tr>
        <tr>
            <td>
                <hr class="garis">
            </td>
        </tr>
        <tr>
            <td class="text-center">
                <h4><u>Daftar Terdata Suplemen {{ ($suplemen['nama']) }}</u></h4>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Sasaran Suplemen : </strong>{{ $sasaran[$suplemen['sasaran']] }}<br>
                <strong>Keterangan : </strong>{{ $suplemen['keterangan'] }}
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <table class="border thick">
                    <thead>
                        <tr class="border thick">
                            <th class="padat">No</th>
                            <th>{{ $suplemen['sasaran'] == 1 ? 'No.' : 'NIK' }} KK</th>
                            <th>{{ $suplemen['sasaran'] == 1 ? 'NIK Penduduk' : 'No. KK' }}</th>
                            <th>{{ $suplemen['sasaran'] == 1 ? 'Nama Penduduk' : 'Kepala Keluarga' }}</th>
                            <th>Tempat Lahir</th>
                            <th>Tanggal Lahir</th>
                            <th>Jenis Kelamin</th>
                            <th>Alamat</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($terdata as $key => $item)
                            <tr>
                                <td class="padat">{{ $key + 1 }}</td>
                                <td class="textx">{{ $item['terdata_info'] }}</td>
                                <td class="textx">{{ $item['terdata_plus'] }}</td>
                                <td>{{ $item['terdata_nama'] }}</td>
                                <td>{{ $item['tempatlahir'] }}</td>
                                <td class="textx">{{ $item['tanggallahir'] }}</td>
                                <td>{{ App\Models\Enums\JenisKelaminEnum::getLabel($item['sex']) }}</td>
                                <td>{{ 'RT/RW ' . $item['rt'] . '/' . $item['rw'] . ' - ' . strtoupper($item['dusun']) }}</td>
                                <td>{{ $item['keterangan'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table><br><br>
            </td>
        </tr>
    </tbody>
</table>
