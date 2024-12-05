$('#filter').on('click', function(e) {
    suplemen.draw();
});

$(document).on('click', '#reset', function(e) {
    e.preventDefault();
    $('#sasaran').val('').change();
    $('#status').val('').change();

    suplemen.ajax.reload();
});