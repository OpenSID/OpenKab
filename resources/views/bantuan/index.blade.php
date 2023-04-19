@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Bantuan')

@section('content_header')
    <h1>Data Bantuan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-auto">
                            <button id="cetak" type="button" class="btn btn-primary btn-block btn-sm" data-url=""><i
                                    class="fa fa-print"></i>
                                Cetak</button>
                        </div>

                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <select class="form-control" id="sasaran">

                            </select>

                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="tahun">

                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">

                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="bantuan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Aksi</th>
                                    <th>Nama Program</th>
                                    <th>Asal Dana</th>
                                    <th>Jumlah Peseerta</th>
                                    <th>Masa Berlaku</th>
                                    <th>Sasaran</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        var bantuan = $('#bantuan').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            searchPanes: {
                viewTotal: false,
                columns: [0]
            },
            ajax: {
                url: `{{ url('api/v1/bantuan') }}`,
                method: 'get',
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[search]": row.search.value,
                        "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]
                            ?.name,
                        "filter[sasaran]": $("#sasaran").val(),
                        "filter[tahun]": $("#tahun").val(),
                    };
                },
                dataSrc: function(json) {
                    json.recordsTotal = json.meta.pagination.total
                    json.recordsFiltered = json.meta.pagination.total

                    return json.data
                },
            },

            columns: [{
                    data: null,
                },
                {
                    data: function(data) {
                        return `<a href="{{ url('bantuan') }}/${data.id}">
                                    <button class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Detail</button>
                                </a>`
                    },
                },
                {
                    data: "attributes.nama",
                    name: "nama"
                },
                {
                    data: "attributes.asaldana",
                    name: "asaldana"
                },
                {
                    data: "attributes.jumlah_peserta",
                    name: "jumlah_peserta",
                    className: 'text-center'
                },
                {
                    data: function(data) {
                        return data.attributes.sdate + ' - ' + data.attributes.edate
                    },
                },
                {
                    data: "attributes.nama_sasaran",
                    name: "nama_sasaran",
                },
                {
                    data: function(data) {
                        return data.attributes.status == 1 ? 'Aktif' : 'Tidak Aktif'
                    },
                },
            ],
            order: [
                [2, 'asc']
            ]
        })

        bantuan.on('draw.dt', function() {
            var PageInfo = $('#bantuan').DataTable().page.info();
            bantuan.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        $('#sasaran').select2({
            minimumResultsForSearch: -1,
            allowClear: true,
            theme: "bootstrap",
            ajax: {
                url: '{{ url('api/v1/bantuan') }}/sasaran/',
                dataType: 'json',
                processResults: function(data) {
                    return {
                        results: data.data
                    };
                }
            },
            placeholder: "Sasaran",
        });

        $('#tahun').select2({
            minimumResultsForSearch: -1,
            allowClear: true,
            theme: "bootstrap",
            ajax: {
                url: '{{ url('api/v1/bantuan') }}/tahun/',
                dataType: 'json',
                processResults: function(data) {
                    if (data.data.tahun_awal == null) {
                        return null
                    };
                    const element = new Array();

                    for (let index = data.data.tahun_awal; index <= data.data.tahun_akhir; index++) {
                        element.push({
                            id: index,
                            text: index
                        });
                    }

                    return {
                        results: element
                    };
                }
            },
            placeholder: "Pilih Tahun",
        });


        $('#sasaran').on('change', function(e) {
            bantuan.draw();
        });

        $('#tahun').on('change', function(e) {
            bantuan.draw();
        });

        $('#cetak').on('click', function() {
            let url = new URL("{{ url('bantuan/cetak') }}");
            url.searchParams.append("sasaran", $("#sasaran").val() ?? '');
            url.searchParams.append("tahun", $("#tahun").val() ?? '');
            url.searchParams.append("search", $('input[aria-controls="bantuan"]').val() ?? '');
            console.log(url.href)
            window.open(url.href, '_blank');
        });
    </script>
@endsection
