<div id="delete-modal" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content bg-danger">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Anda yakin akan menghapus data berikut?</p>
            </div>
            <div class="modal-footer">
                <form id="destroy" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-outline-light btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm">&nbsp; Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="module">
    $(document).ready(function() {
        $(document).on('click', '#deleteModal', function(e) {
            var url = $(this).attr('data-href');
            $('#destroy').attr('action', url );
            $('#delete-modal').modal('show');
            e.preventDefault();
        });
    });
</script>