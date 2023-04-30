<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>Cetak | @yield('title')</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex">
    <link rel="shortcut icon" href="{{ asset('/assets/img/opensid_logo.png') }}" />
    <link rel="stylesheet" href="{{ asset('/assets/print/print.css') }}" />
    @stack('css')
</head>

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

    <script src="{{ asset('/vendor/jquery/jquery.min.js') }}"></script>
    <script src="https://unpkg.com/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    @stack('scripts')
    <script>
        $(document).ready(function() {

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
