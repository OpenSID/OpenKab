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
        url: `{{ url('api/v1/suplemen') }}`,
        method: 'get',
        data: function(row) {
            return {
                "page[size]": row.length,
                "page[number]": (row.start / row.length) + 1,
                "filter[search]": row.search.value,
                "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]
                    ?.name,
                "filter[sasaran]": $("#sasaran").val(),
                "filter[status]": $("#status").val(),
            };
        },
        dataSrc: function(json) {
            json.recordsTotal = json.meta.pagination.total
            json.recordsFiltered = json.meta.pagination.total

            return json.data
        },
    },
    columnDefs: [{
            targets: '_all',
            className: 'text-nowrap',
        },
        {
            targets: [0, 1, 2, 3],
            orderable: false,
            searchable: false,
        },
    ],
    columns: [{
            data: null,
        },
        {
            data: 'aksi',
            name: 'aksi',
            searchable: true,
            orderable: true
        },
        {
            data: 'nama',
            name: 'nama',
            searchable: true,
            orderable: true
        },
        {
            data: 'terdata_count',
            name: 'terdata_count',
            searchable: false,
            orderable: false,
            class: 'padat'
        },
        {
            data: 'sasaran',
            name: 'sasaran',
            searchable: true,
            orderable: true,
            class: 'padat'
        },
        {
            data: 'keterangan',
            name: 'keterangan',
            searchable: true,
            orderable: true
        },
    ],
    order: [
        [2, 'asc']
    ]
})

suplemen.on('draw.dt', function() {
    var PageInfo = $('#suplemen').DataTable().page.info();
    suplemen.column(0, {
        page: 'current'
    }).nodes().each(function(cell, i) {
        cell.innerHTML = i + 1 + PageInfo.start;
    });
});