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
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: "json",
                url: `{{ url('api/v1/point/hapus') }}/${id}`,
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