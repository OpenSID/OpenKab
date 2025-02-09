$('#filter').on('click', function(e) {
    point.draw();
});

$(document).on('click', '#reset', function(e) {
    e.preventDefault();
    $('#status').val('').change();

    point.ajax.reload();
});