<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>Cetak | @yield('title')</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="{{ asset('/assets/print/print.css') }}" />
    <p x-data="identitas()" x-init="retrieveData()">
        <link rel="shortcut icon" href="dataIdentitas.logo ? '{{ asset('storage/img') }}/' + dataIdentitas.logo :
                            '{{ asset('assets/img/opensid_logo.png') }}'" />
    </p>
    @stack('css')
    @vite(['resources/js/app.js'])
</head>

<body>
    <table>
        <tbody>
            <tr>
                <td class="padat" x-data="identitas()" x-init="retrieveData()">
                    <img class="img-identitas logo"
                         :src="dataIdentitas.logo ? '{{ asset('storage/img') }}/' + dataIdentitas.logo :
                            '{{ asset('assets/img/opensid_logo.png') }}'"
                         alt="logo-Aplikasi">
                    <h3 class="judul">PEMERINTAH <br /> KABUPATEN <span x-text="dataIdentitas.nama_kabupaten"></span></h3>
                </td>
            </tr>
            <tr>
                <td>
                    <hr class="garis">
                </td>
            </tr>
            <tr>
                <td class="text-center">
                    <h4 class="judul" id="judul_halaman">@yield('title')</h4>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                    @yield('content')
                </td>
            </tr>
        </tbody>
    </table>

    @stack('scripts')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {

            $(document).ajaxStop(function() {
                window.print();
            });

            window.onafterprint = function() {
                window.close();
            }
        });

        function identitas() {
            return {
                id: 1,
                edit: '',
                dataIdentitas: {},
                retrieveData() {
                    fetch('{{ url('api/v1/identitas') }}')
                        .then(res => res.json())
                        .then(response => {
                            this.dataIdentitas = response.data.attributes;
                            this.id = response.data.id
                        });
                },
            }
        }
    </script>
</body>

</html>
