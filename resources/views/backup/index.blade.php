@extends('layouts.index')

@section('title', 'Backup Database')

@section('content_header')
    <h1>Backup Database</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('common.alerts')

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Kelola Backup Database</h3>
                        <div class="card-tools">
                            @if ($canwrite)
                                <button type="button" id="btnBackup" class="btn btn-primary btn-sm">
                                    <i class="fas fa-database mr-1"></i> Backup Sekarang
                                </button>
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#uploadModal">
                                    <i class="fas fa-upload mr-1"></i> Upload & Restore
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="backupTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama File</th>
                                        <th>Ukuran</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($backups as $i => $backup)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $backup['filename'] }}</td>
                                            <td>{{ number_format($backup['size'] / 1024, 2) }} KB</td>
                                            <td>{{ \Carbon\Carbon::createFromTimestamp($backup['last_modified'])->isoFormat('DD-MM-Y HH:mm') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('backup.show', $backup['filename']) }}"
                                                       class="btn btn-info btn-sm"
                                                       title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    @if ($canedit)
                                                        <button type="button"
                                                                class="btn btn-warning btn-sm btn-restore"
                                                                data-filename="{{ $backup['filename'] }}"
                                                                title="Restore">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    @endif
                                                    @if ($candelete)
                                                        <button type="button"
                                                                class="btn btn-danger btn-sm btn-delete"
                                                                data-filename="{{ $backup['filename'] }}"
                                                                title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Belum ada backup.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload & Restore Backup</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Perhatian!</strong>
                            <p class="mb-0 mt-2">Semua data saat ini akan diganti dengan data dari file backup yang diupload. Tindakan ini tidak dapat dibatalkan.</p>
                        </div>
                        <div class="form-group">
                            <label for="file">Pilih File Backup (.zip)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file" name="file" accept=".zip" required>
                                <label class="custom-file-label" for="file">Pilih file...</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning" id="btnUploadRestore">
                            <i class="fas fa-undo mr-1"></i> Upload & Restore
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function() {
            $('#btnBackup').click(function() {
                const btn = $(this);
                const originalText = btn.html();

                Swal.fire({
                    title: 'Buat Backup?',
                    text: 'Proses backup database akan berjalan. Lanjutkan?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-play mr-2"></i>Ya, Backup',
                    cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true)
                           .html('<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...');

                        $.ajax({
                            url: '{{ route('backup.store') }}',
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                const resp = xhr.responseJSON;
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: resp?.message || 'Terjadi kesalahan',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            },
                            complete: function() {
                                btn.prop('disabled', false).html(originalText);
                            }
                        });
                    }
                });
            });

            $('.btn-restore').click(function() {
                const filename = $(this).data('filename');
                const btn = $(this);
                const originalText = btn.html();

                Swal.fire({
                    title: 'Restore Database?',
                    html: `
                        <div class="text-left">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Perhatian!</strong>
                                <p class="mb-0 mt-2">Semua data saat ini akan diganti dengan data dari backup <strong>${filename}</strong>. Tindakan ini tidak dapat dibatalkan.</p>
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-undo mr-2"></i>Ya, Restore',
                    cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true)
                           .html('<i class="fas fa-spinner fa-spin mr-2"></i>Merestore...');

                        $.ajax({
                            url: '{{ route('backup.update', ['filename' => '__FILE__']) }}'.replace('__FILE__', filename),
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                const resp = xhr.responseJSON;
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: resp?.message || 'Terjadi kesalahan',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            },
                            complete: function() {
                                btn.prop('disabled', false).html(originalText);
                            }
                        });
                    }
                });
            });

            $('.btn-delete').click(function() {
                const filename = $(this).data('filename');
                const btn = $(this);
                const originalText = btn.html();

                Swal.fire({
                    title: 'Hapus Backup?',
                    text: `File ${filename} akan dihapus permanen.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash mr-2"></i>Ya, Hapus',
                    cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true);

                        $.ajax({
                            url: '{{ route('backup.destroy', ['filename' => '__FILE__']) }}'.replace('__FILE__', filename),
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                const resp = xhr.responseJSON;
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: resp?.message || 'Terjadi kesalahan',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            },
                            complete: function() {
                                btn.prop('disabled', false).html(originalText);
                            }
                        });
                    }
                });

            $('#uploadForm').submit(function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const btn = $('#btnUploadRestore');
                const originalText = btn.html();

                Swal.fire({
                    title: 'Restore dari Upload?',
                    html: `
                        <div class="text-left">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Perhatian!</strong>
                                <p class="mb-0 mt-2">Semua data akan diganti dengan data dari file yang diupload. Tindakan ini tidak dapat dibatalkan.</p>
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-undo mr-2"></i>Ya, Restore',
                    cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true)
                           .html('<i class="fas fa-spinner fa-spin mr-2"></i>Merestore...');

                        $.ajax({
                            url: '{{ route('backup.upload') }}',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                $('#uploadModal').modal('hide');
                                $('#uploadForm')[0].reset();
                                $('.custom-file-label').text('Pilih file...');
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                const resp = xhr.responseJSON;
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: resp?.message || 'Terjadi kesalahan',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            },
                            complete: function() {
                                btn.prop('disabled', false).html(originalText);
                            }
                        });
                    }
                });
            });

            $('.custom-file-input').change(function() {
                const fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').text(fileName);
            });
        });
    </script>
@stop
