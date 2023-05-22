@extends('layouts.cetak.index')

@section('title', 'Data Statistik')

@section('content')
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
                    url: `{{ url('api/v1/statistik/kategori-statistik') }}?filter[detail]=${id}&filter[id]=${kategori}`,
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
