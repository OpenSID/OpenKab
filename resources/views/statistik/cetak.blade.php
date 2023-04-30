<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>Data Statistik</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex">
    <link rel="shortcut icon" href="{{ asset('/assets/img/opensid_logo.png') }}" />
    <link rel="stylesheet" href="{{ asset('/assets/print/print.css') }}" />
</head>

<body>

    <body>
        <table>
            <tbody>
                <tr>
                    <td class="padat">
                        <img class="logo" src="{{ asset('/assets/img/opensid_logo.png') }}" alt="Logo">
                        <h3 class="judul">PEMERINTAH <br /> KABUPATEN {{ config('app.namaKab') }}</h3>
                    </td>
                </tr>
                <tr>
                    <td>
                        <hr class="garis">
                    </td>
                </tr>
                <tr>
                    <td class="text-center">
                        <h4 class="judul" id="judul_halaman"></h4>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>
                        <table class="border thick" id="tabel-penduduk">
                            <thead>
                                <tr class="border thick">
                                    <th>No</th>
                                    <th id="judul_kolom_nama" width="50%"></th>
                                    <th colspan="2" class="padat">Jumlah</th>
                                    <th colspan="2" class="padat">Laki - laki</th>
                                    <th colspan="2" class="padat">Perempuan</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        <script src="{{ asset('/vendor/jquery/jquery.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                var kategori = `{{ $kategori }}`;
                var id = `{{ $id }}`;

                let url = `{{ url('api/v1/statistik') }}/${kategori}/`;
                let create_cetak = new URL(url);
                @foreach ($filter as $key => $value)
                    create_cetak.searchParams.append('filter[{{ $key }}]', '{{ $value }}');
                @endforeach
                create_cetak.searchParams.append('filter[id]', id);

                $.ajax({
                    url: `{{ url('api/v1/statistik/kategori-statistik') }}/?filter[detail]=${id}&filter[id]=${kategori}`,
                    method: 'get',
                    success: function(json) {
                        var data = json.data[0];
                        var judul_halaman = 'Data Statistik ' + data.judul_halaman
                        var judul_kolom_nama = data.judul_kolom_nama

                        $('#judul_halaman').html(judul_halaman)
                        $('#judul_kolom_nama').html(judul_kolom_nama)
                        document.title = judul_halaman
                    }
                })

                $.ajax({
                    url: create_cetak,
                    method: 'get',
                    success: function(json) {
                        var statistik = json.data.attributes
                        var no = 1;

                        json.data.forEach(function(item) {
                            var row = `<tr>
                                <td class="padat">${no}</td>
                                <td>${item.attributes.nama}</td>
                                <td class="text-right" width="10%">${item.attributes.jumlah}</td>
                                <td class="text-right" width="10%">${item.attributes.persentase_jumlah}</td>
                                <td class="text-right" width="10%">${item.attributes.laki_laki}</td>
                                <td class="text-right" width="10%">${item.attributes.persentase_laki_laki}</td>
                                <td class="text-right" width="10%">${item.attributes.perempuan}</td>
                                <td class="text-right" width="10%">${item.attributes.persentase_perempuan}</td>
                            </tr>`

                            $('#tabel-penduduk tbody').append(row)
                            no++;
                        })
                    }
                })

                $(document).ajaxStop(function() {
                    window.print();
                });

                window.onafterprint = function() {
                    window.close();
                }
            });
        </script>
    </body>

</html>
