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

<body onload="window.print()">
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
                    <h4 id="judul_cetak">DATA STATISTIK BANTUAN 'NAMA BANTUAN'</h4>
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
                                <th nowrap>No</th>
                                <th nowrap>HARI / TANGGAL </th>
                                <th nowrap>NAMA</th>
                                <th nowrap>TELEPON</th>
                                <th nowrap>INSTANSI</th>
                                <th nowrap>JENIS KELAMIN</th>
                                <th nowrap>ALAMAT</th>
                                <th nowrap>KEPERLUAN</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>
<script>
    ajax: {
            url: `{{ url('api/v1/statistik/bantuan') }}`,
            method: 'get',
            dataSrc: function(json) {
                json.statistik = json.data[0].attributes.sasaran
                json.recordsTotal = json.meta.pagination.total
                json.recordsFiltered = json.meta.pagination.total

                $('#judul-sasaran').html('Sasaran ' + json.data[0].attributes.nama_sasaran);

                data_grafik.push(json.data[0].attributes)
                tampilkan_grafik(data_grafik[0])

                return json.data[0].attributes.statistik
            },
        },
        columns: [{
            data: null,
        }, {
            data: "nama"
        }, {
            data: "jumlah",
            className: 'dt-body-right',
        }, {
            data: function(data) {
                return data.persentase_jumlah.toFixed(2) + '%';
            },
            className: 'dt-body-right',
        }, {
            data: function(data) {
                return data.laki_laki
            },
            className: 'dt-body-right',
        }, {
            data: function(data) {
                return data.persentase_laki_laki.toFixed(2) + '%';
            },
            className: 'dt-body-right',
        }, {
            data: function(data) {
                return data.perempuan
            },
            className: 'dt-body-right',
        }, {
            data: function(data) {
                return data.persentase_perempuan.toFixed(2) + '%';
            },
            className: 'dt-body-right',
        }]
    })
</script>

</html>
