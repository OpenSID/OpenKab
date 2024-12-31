$('#suplemen').on('click', '.btn-details', function() {
    var tr = $(this).closest('tr');
    var row = suplemen.row(tr);

    // Ambil data JSON dari atribut data-json
    var jsonData;
    try {
        jsonData = JSON.parse($(this).attr('data-json'));
    } catch (error) {
        console.error("Invalid JSON format", error);
        return;
    }

    var button = $(this);

    @include('suplemen.js.button_validation')

});