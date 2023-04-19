<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>Data Bantuan</title>
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
                        <h3 class="judul">PEMERINTAH <br /> KOTA {{ config('app.namaKab') }}</h3>
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
                let url = new URL("{{ url('api/v1/bantuan') }}");
                @foreach ($filter as $key => $value)
                    url.searchParams.append("filter[{{ $key  }}]", '{{ $value }}');
                @endforeach




                console.log(url);

                $.ajax({
                    url: url,
                    method: 'get',
                    success: function(json) {

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
