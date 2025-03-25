$(document).on('click', 'button.hapus', function () {
    var id = $(this).data('id')
    var that = $(this);
    var url = new URL(`{{ config('app.databaseGabunganUrl') }}/api/v1/suplemen/hapus/${id}`);
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
                headers: header,
                dataType: "json",
                url: url,
                headers: header,
                data: {
                    id: id
                },
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