// Perform multiple delete with SweetAlert
$('#multiple-delete').on('click', function(e) {
    e.preventDefault();
    var selectedIds = [];
    $('#suplemen').find('.select-checkbox:checked').each(function() {
        selectedIds.push($(this).data('id'));
    });
    var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/suplemen/terdata/hapus' }}");
    if (selectedIds.length > 0) {
        // SweetAlert confirmation for delete action
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: "Apakah Anda yakin ingin menghapus data yang terpilih?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading while performing the deletion
                Swal.fire({
                    title: 'Menyimpan',
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                url: url,
                headers: header,
                method: 'POST',
                contentType: "application/json",
                data: JSON.stringify({
                    _token: '{{ csrf_token() }}',
                    ids: selectedIds,
                }),
                success: function(response) {
                    console.log("Response:", response);
                    if (response.success) {
                        Swal.fire(
                            'Hapus!',
                            'Data berhasil dihapus',
                            'success'
                        );
                        suplemen.ajax.reload();
                    } else {
                        Swal.fire(
                            'Gagal!',
                            'Gagal menghapus data',
                            'error'
                        );
                    }
                },
                error: function(xhr) {
                    Swal.fire(
                        'Error!',
                        'Terjadi kesalahan, coba lagi nanti',
                        'error'
                    );
                }
            });

            }
        });
    }
});