$('#point').change(function() {
    let _label = $(this).find('option:selected').text()
    $('#subpoint').val('')
    $('#subpoint').find('optgroup').prop('disabled', 1)
    if ($(this).val()) {
        $('#subpoint').closest('div').show()
        $('#subpoint').find(`optgroup[label="${_label}"]`).prop('disabled', 0)
    } else {
        $('#subpoint').closest('div').hide()
    }
    $('#subpoint').select2()
})

$('#subpoint').closest('div').hide()

$('#subpoint, #point, #status').change(function() {
    point.draw()
})