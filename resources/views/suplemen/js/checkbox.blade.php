// Select or deselect all checkboxes
$('#select-all').on('click', function() {
    var checkboxes = $('#suplemen').find('.select-checkbox');
    var isChecked = $(this).prop('checked');
    checkboxes.prop('checked', isChecked);
    toggleDeleteButton();
});

// Track selected checkboxes
$('#suplemen').on('change', '.select-checkbox', function() {
    toggleDeleteButton();
});

// Toggle delete button visibility
function toggleDeleteButton() {
    var selectedCheckboxes = $('#suplemen').find('.select-checkbox:checked');
    if (selectedCheckboxes.length > 0) {
        $('#multiple-delete').removeClass('disabled');
    } else {
        $('#multiple-delete').addClass('disabled');
    }
}