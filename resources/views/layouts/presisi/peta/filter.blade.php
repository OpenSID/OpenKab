$('#filter_kabupaten').select2({
    placeholder: "Pilih Kabupaten"
});
$('#filter_kecamatan').select2({
    placeholder: "Pilih Kecamatan"
});
$('#filter_desa').select2({
    placeholder: "Pilih Desa"
});

GetListKabupaten();
GetListKecamatan();
GetListDesa();

function reloadDataTablesIfAjaxRunning() {
    setTimeout(() => {
        if ($.active > 0) {
            summaryPenduduk.ajax.reload();
        }
    }, 500); // Tunggu sedikit agar AJAX benar-benar berjalan
}

$('#bt_clear_filter').click(function() {
    GetListKabupaten();
});

$('#bt_filter').click(function() {
    $('#bt_clear_filter').show();
    updateWebsiteData(
        $("#filter_kabupaten").val(),
        $("#filter_kecamatan").val(),
        $("#filter_desa").val()
    );
    GetListCoordinates(
        $("#filter_kabupaten").val(),
        $("#filter_kecamatan").val(),
        $("#filter_desa").val()
    );
    GetSummary();
    reloadDataTablesIfAjaxRunning();
});

function updateWebsiteData(kabupaten = null, kecamatan = null, desa = null) {
    let url = "{{ url('api/v1/data-website') }}";
    if (kabupaten || kecamatan || desa) {
        url += `?filter[kode_kabupaten]=${kabupaten || ''}&filter[kode_kecamatan]=${kecamatan || ''}&filter[kode_desa]=${desa || ''}`;
    }
    $.get(url, {}, function(result) {
        let category = result.data.categoriesItems;
        let listDesa = result.data.listDesa;
        let listKecamatan = result.data.listKecamatan;

        for (let index in category) {
            $(`.kategori-item .jumlah-${index}-elm`).text(category[index]['value']);
        }

        let _optionKecamatan = [];
        let _optionDesa = [];
        for (let item in listKecamatan) {
            _optionKecamatan.push(`<option>${item}</option>`);
        }

        for (let item in listDesa) {
            _optionDesa.push(`<optgroup label='${item}'>`);
            for (let desa in listDesa[item]) {
                _optionDesa.push(`<option value='${desa}'>${listDesa[item][desa]}</option>`);
            }
            _optionDesa.push(`</optgroup>`);
        }

        $('select[name=search_kecamatan]').empty().append(_optionKecamatan.join('')).trigger("change");
        $('select[name=search_desa]').empty().append(_optionDesa.join('')).trigger("change");
    }, 'json');
}


    $('#filter_kabupaten').on("select2:select", function(e) {
        GetListKecamatan(this.value);
    });

    $('#filter_kecamatan').on("select2:select", function(e) {
        GetListDesa(this.value);
    });
    