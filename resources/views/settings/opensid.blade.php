<div class="row">
    <div class="col-lg-12">
        <div class="card card-outline card-primary">
            <form id="pengaturan-form">
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="col">
                        @forelse ($listPengaturan as $item)
                        <!-- Name Field -->
                        <div class="form-group">
                            {!! Form::label($item->key, $item->judul) !!}
                            @switch($item->jenis)
                                @case('option')
                                    {!! Form::select($item->key, collect(json_decode($item->option)), $item->value, ['class' => 'form-control', 'required']) !!}
                                    @break
                                @case('color')
                                    <div class="input-group">
                                        {!! Form::text($item->key, $item->value, ['class' => 'form-control color', 'required']) !!}
                                        <div class="input-group-append">
                                            <div class="input-group-text bg-danger">
                                                <i class="fas fa-lg fa-square"></i>
                                            </div>
                                        </div>
                                    </div>
                                    @break
                                @default
                                    {!! Form::text($item->key, null, ['class' => 'form-control', 'required']) !!}
                            @endswitch
                        </div>
                        @empty
                        @endforelse
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    @if($canedit)
                    <button type="button" id="reset" class="btn btn-danger btn-sm"><i class="fas fa-times"></i>&nbsp; Batal</button>
                    <button id="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i>&nbsp;
                        Simpan</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function (event) {
            $('.color').colorpicker()

            $(document).on('click', 'button#submit', function(e) {
                e.preventDefault();
                formData = $('#pengaturan-form').serialize();

                Swal.fire({
                    title: 'Ubah',
                    text: "Apakah anda yakin mengubah data ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Simpan'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menyimpan',
                            didOpen: () => {
                                Swal.showLoading()
                            },
                        })
                        $.ajax({
                            type: "POST",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType: "json",
                            url: `{{ url('api/v1/pengaturan/update') }}`,
                            data: formData,
                            success: function(response) {
                                if (response.success == true) {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: 'Data berhasil diubah',
                                        icon: 'success',
                                        showConfirmButton: true,
                                        timer: 1500
                                    })
                                    window.location.reload()
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        response.message,
                                        'error'
                                    )
                                }
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                Swal.fire(
                                    'Error!',
                                    xhr.responseJSON.message,
                                    'error'
                                )
                            }
                        });
                    }
                })
            });
        });

    </script>
@endpush
