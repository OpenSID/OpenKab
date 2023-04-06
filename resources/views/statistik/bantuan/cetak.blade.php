<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>Data Statistik Bantuan</title>
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
                        <h3 class="judul">PEMERINTAH <br /> KABUPATEN BIMA</h3>
                    </td>
                </tr>
                <tr>
                    <td>
                        <hr class="garis">
                    </td>
                </tr>
                <tr>
                    <td class="text-center">
                        <h4 id="judul_cetak" class="judul"></h4>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>
                        <table class="border thick" id="tabel-bantuan">
                            <thead>
                                <tr class="border thick">
                                    <th>No</th>
                                    <th id="judul_sasaran" width="50%"></th>
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
                var id = `{{ request()->segment(count(request()->segments())) }}`

                $.ajax({
                    url: `{{ url('api/v1/statistik/bantuan/grafik') }}/?filter[id]=${id}`,
                    method: 'get',
                    success: function(json) {
                        var statistik = json.data[0].attributes.statistik
                        var nama_sasaran = json.data[0].attributes.nama_sasaran
                        var nama_bantuan = json.data[0].attributes.nama

                        $('#judul_sasaran').html('Sasaran ' + nama_sasaran);
                        $('#judul_cetak').html('DATA STATISTIK BANTUAN ' + nama_bantuan);

                        var no = 1;
                        statistik.forEach(function(item) {
                            var row = `<tr>
                                <td class="padat">${no}</td>
                                <td>${item.nama}</td>
                                <td class="text-right" width="10%">${item.jumlah}</td>
                                <td class="text-right" width="10%">${item.persentase_jumlah.toFixed(2) + '%'}</td>
                                <td class="text-right" width="10%">${item.laki_laki}</td>
                                <td class="text-right" width="10%">${item.persentase_laki_laki.toFixed(2) + '%'}</td>
                                <td class="text-right" width="10%">${item.perempuan}</td>
                                <td class="text-right" width="10%">${item.persentase_perempuan.toFixed(2) + '%'}</td>
                            </tr>`

                            $('#tabel-bantuan tbody').append(row)
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
