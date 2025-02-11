$(document).on('click', 'button.hapus', function () {
    var id = $(this).data('id')
    var that = $(this);
    Swal.fire({
        title: 'Hapus',
        text: "Apakah anda yakin menghapus data ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menyimpan',
                didOpen: () => {
                    Swal.showLoading()
                },
            })
            $.ajax({
                type: 'DELETE',
                dataType: "json",
                url: `${urlSuplemenHapus}/${id}`,
                data: {
                    id: id
                },
                headers: header,
                success: function (response) {

                    if (response.success == true) {
                        Swal.fire(
                            'Hapus!',
                            'Data berhasil dihapus',
                            'success'
                        )
                        that.parent().parent().remove();
                    } else {
                        Swal.fire(
                            'Error!',
                            response.message,
                            'error'
                        )
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    Swal.fire(
                        'Error!',
                        thrownError,
                        'error'
                    )

                }
            });
        }
    })
});