@extends('layouts.cetak.index')

@section('title', 'Data Statistik')

@section('content')
    @include('partials.breadcrumbs')
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
@stop

@push('scripts')
    <script nonce="{{ csp_nonce() }}"  >
        document.addEventListener("DOMContentLoaded", function(event) {
            var kategori = `{{ $kategori }}`;
            var id = `{{ $id }}`;

            const header = @include('layouts.components.header_bearer_api_gabungan');

            var baseUrl = {!! json_encode(config('app.databaseGabunganUrl')) !!} + "/api/v1";

            var url = new URL(`${baseUrl}/statistik/${kategori}`);


                let create_cetak = new URL(url);
                @foreach ($filter as $key => $value)
                    create_cetak.searchParams.append('filter[{{ $key }}]', '{{ $value }}');
                @endforeach
                create_cetak.searchParams.append('filter[id]', id);

                var urlKategoriStatistik = new URL(`${baseUrl}/statistik/kategori-statistik`);

                urlKategoriStatistik.searchParams.set('filter[detail]', id);
                urlKategoriStatistik.searchParams.set('filter[id]', kategori);

                $.ajax({
                    url: urlKategoriStatistik.href,
                    headers: header,
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
                    headers: header,
                    method: 'get',
                    success: function(json) {
                        var statistik = json.data.attributes
                        var no = 1;
                    json.data.forEach(function(item) {
                        var row = `<tr>
                                // jangan tampilkan nomor 3 data terakhir
                                <td class="text-center">${(no <= json.data.length - 3) ? no : ''}</td>
                                <td>${item.attributes.nama}</td>
                                <td class="text-right" width="10%">${item.attributes.jumlah}</td>
                                <td class="text-right" width="10%">${item.attributes.persentase_jumlah}</td>
                                <td class="text-right" width="10%">${item.attributes.laki_laki}</td>
                                <td class="text-right" width="10%">${item.attributes.persentase_laki_laki}</td>
                                <td class="text-right" width="10%">${item.attributes.perempuan}</td>
                                <td class="text-right" width="10%">${item.attributes.persentase_perempuan}</td>
                            </tr>`

                        $('#tabel-penduduk tbody').append(row)
                        no++
                    })
                }
            })
        });
    </script>
@endpush
