<!DOCTYPE html>
<html>

<head>
    <title>RINCIAN LAPORAN PERKEMBANGAN {{ $title }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="{{ asset('css/report.css') }}" rel="stylesheet" type="text/css">
</head>

<body>
    <div id="container">
        <!-- Print Body -->
        <div id="body">
            <h5 class="text-center"><strong>RINCIAN LAPORAN PERKEMBANGAN {{ $title }}</strong></h5>
            <br>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Tempat Lahir</th>
                        <th>Tanggal Lahir</th>
                        <th>Nama Ayah</th>
                        <th>Nama Ibu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($main as $key => $data)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $data['nama'] }}</td>
                            <td style="mso-number-format:'\@';">{{ $data['nik'] }}</td>
                            <td>{{ $data['tempatlahir'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($data['tanggallahir'])->format('Y-m-d') }}</td>
                            <td>{{ $data['nama_ayah'] }}</td>
                            <td>{{ $data['nama_ibu'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
