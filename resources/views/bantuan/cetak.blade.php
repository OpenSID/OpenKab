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
    <script src="//unpkg.com/alpinejs" defer></script>
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
                        <div x-data="{
                            data: {},
                            async retrievePosts() {
                                let url = '{{ url('api/v1/bantuan/cetak') }}';
                                let create_url = new URL(url);
                                @foreach ($filter as $key => $value)
                                create_url.searchParams.append('filter[{{ $key }}]', '{{ $value }}');
                                @endforeach
                                const response = await (await fetch(create_url.href)).json();
                                this.data = response.data
                                await $nextTick();
                                window.print();
                            }
                        }" x-init="retrievePosts">
                            <table class="border thick" id="tabel-penduduk">
                                <thead>
                                    <tr class="border thick">
                                        <th style="width: 10px" class="text-center"> No</th>
                                        <th class="padat">Nama Program</th>
                                        <th class="padat">Asal Dana</th>
                                        <th class="padat">Jumlah Peserta</th>
                                        <th class="padat">Masa Berlaku</th>
                                        <th class="padat">Sasaran</th>
                                        <th class="padat">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(value, index) in data">
                                        <tr>
                                            <td x-text="index+1"></td>
                                            <td x-text="value.attributes.nama"></td>
                                            <td x-text="value.attributes.asaldana"></td>
                                            <td x-text="value.attributes.jumlah_peserta"></td>
                                            <td x-text="value.attributes.sdate + ' s.d. ' + value.attributes.edate"></td>
                                            <td x-text="value.attributes.nama_sasaran"></td>
                                            <td x-text="value.attributes.nama_status"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <script>
            window.onafterprint = function() {
                window.close();
            }
        </script>
    </body>

</html>
