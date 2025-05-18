<div class="row">
    <div class="col-lg-12">
        <div class="card card-outline card-primary">
            <form id="pengaturan-form">
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="col">

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
            function buildInputForm(data) {
                let form = [], _tmp, _item;
                for(let i in data) {
                    _item = data[i]
                    _tmp = `<div class="form-group">
                        <label for="${_item.attributes.key}">${_item.attributes.judul}</label>`
                    switch(_item.attributes.jenis){
                        case 'color':
                        _tmp += `<div class="input-group">
                            <input type="text" name="${_item.attributes.key}" class="form-control color" required value="${_item.attributes.value}" />
                                        <div class="input-group-append">
                                            <div class="input-group-text bg-danger">
                                                <i class="fas fa-lg fa-square"></i>
                                            </div>
                                        </div>
                                    </div>`
                            break;
                        case 'option':
                            let _options = []
                            let _optionObj = JSON.parse(_item.attributes.option)
                            for(let i in _optionObj){
                                _options.push(`<option value="${i}" ${i == _item.attributes.value ? 'selected' : ''} >${_optionObj[i]}</option>`)
                            }

                            _tmp += `<select name="${_item.attributes.key}" class="form-control">${_options.join('')}</select>`
                            break;
                        default:
                            _tmp += `<input type="text" name="${_item.attributes.key}" class="form-control" required value="${_item.attributes.value}" />`
                    }
                    _tmp += `</div>`
                    form.push(_tmp)
                }

                return form.join('');
            }

            const header = @include('layouts.components.header_bearer_api_gabungan')

            fetch(`{{ route('api.pengaturan_aplikasi') }}`, {
                    headers: header,
                })
                .then(res => res.json())
                .then(response => {
                    if (response.data.length != 0) {
                        $('#pengaturan-form>.card-body>.col').html(buildInputForm(response.data))
                    }
                }).then(() => {
                    $('.color').colorpicker()
                }).catch(err => {
                    Swal.fire(
                        'Error!',
                        err,
                        'error'
                    )
                }); // Catch errors;

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
                                'Accept': 'application/json',
                                'Authorization': 'Bearer {{ $settingAplikasi->get('database_gabungan_api_key') }}',
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
