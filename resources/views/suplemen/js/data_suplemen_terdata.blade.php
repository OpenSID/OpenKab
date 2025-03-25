var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/suplemen/terdata/'.$suplemen->sasaran.'/'.$suplemen->id }}");
var suplemen = $('#suplemen').DataTable({
    processing: true,
    serverSide: true,
    autoWidth: false,
    ordering: true,
    searchPanes: {
        viewTotal: false,
        columns: [0]
    },
    ajax: {
        url: url,
        headers: header,
        method: 'get',
        data: function(row) {
            return {
                "page[size]": row.length,
                "page[number]": (row.start / row.length) + 1,
                "filter[search]": row.search.value,
                "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]?.name,
                "filter[tweb_penduduk.sex]": $("#sex").val(),
                "filter[tweb_wil_clusterdesa.dusun]": $("#dusun").val(),
                "filter[tweb_wil_clusterdesa.rw]": $("#rw").val(),
                "filter[tweb_wil_clusterdesa.rt]": $("#rt").val(),
            };
        },
        dataSrc: function(json) {
            json.recordsTotal = json.meta.pagination.total;
            json.recordsFiltered = json.meta.pagination.total;
            return json.data;
        },
    },
    columnDefs: [{
        targets: '_all',
        className: 'text-nowrap',
    }, {
        targets: [0, 1, 2, 3, 4, 5],
        orderable: false,
        searchable: false,
    }],
    columns: [
        {
            data: null,
        },
        {
            data: null,
            render: function(data, type, row) {
                return `<input type="checkbox" class="select-checkbox" data-id="${row.id}">`;
            },
        },
        {
            data: null,
            orderable: false,
            className: 'text-center',
            render: function(data, type, row) {
                // Cek apakah data_form_isian ada
                if (row.data_form_isian && row.data_form_isian.trim() !== '') {
                    return `<button class="btn btn-success btn-sm btn-details" data-id="${row.id}" data-json='${row.data_form_isian}'>Selengkapnya</button>`;
                } else {
                    return '';  // Jika tidak ada data, tidak tampilkan tombol
                }
            }
        },
        {
            data: 'terdata_info',
            name: 'terdata_info',
            orderable: false,
            class: 'padat'
        },
        {
            data: 'terdata_plus',
            name: 'terdata_plus',
            orderable: false,
            class: 'padat'
        },
        {
            data: 'terdata_nama',
            name: 'terdata_nama',
            orderable: false,
            class: 'padat'
        },
        {
            data: 'tempatlahir',
            name: 'tempatlahir',
            orderable: false,
            class: 'padat'
        },
        {
            data: 'tanggallahir',
            name: 'tanggallahir',
            orderable: false,
            class: 'padat'
        },
        {
            data: 'sex',
            name: 'sex',
            orderable: false,
            class: 'padat'
        },
        {
            data: 'alamat',
            name: 'alamat',
            orderable: false,
            class: 'padat'
        },
        {
            data: 'keterangan',
            name: 'keterangan',
            orderable: true,
            class: 'padat'
        },
    ],
    order: [
        [10, 'asc']
    ]
});

suplemen.on('draw.dt', function() {
    var PageInfo = $('#suplemen').DataTable().page.info();
    suplemen.column(0, {
        page: 'current'
    }).nodes().each(function(cell, i) {
        cell.innerHTML = i + 1 + PageInfo.start;
    });
});

$('#sex, #dusun, #rw, #rt').change(function() {
    suplemen.draw();
});
