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
                        <label for="${_item.attributes.key}">${_item.attributes.name}</label>`
                    switch(_item.attributes.type){
                        case 'textarea':
                        _tmp += `<textarea name="${_item.attributes.key}" class="form-control" required >${_item.attributes.value}</textarea>`
                            break;
                        case 'dropdown':
                            let _options = []
                            let _optionObj = _item.attributes.attribute
                            for(let i in _optionObj){
                                console.log(_optionObj[i].value, _item.attributes.value)
                                _options.push(`<option value="${_optionObj[i].value}" ${_optionObj[i].text == _item.attributes.value ? 'selected' : ''} >${_optionObj[i].text}</option>`)
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
            
            fetch(`{{ url('api/v1/pengaturan/settings') }}?sort=-key&filter[key][]=opendk_synchronize&filter[key][]=opendk_api_key`,
                {
                    method: 'GET',                    
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest', // Indicate that this is an AJAX request
                        'Content-Type': 'application/json' // Optional: specify content type
                    }
                })            
                .then(res => res.json())
                .then(response => {                    
                    if (response.data.length != 0) {                        
                        $('#pengaturan-form>.card-body>.col').html(buildInputForm(response.data))
                        $('#pengaturan-form textarea[name="opendk_api_key"]').attr('readonly', true)
                        $(`<button type="button" class="btn btn-success btn-sm mt-2" id="btn-token">&nbsp;<i class="fa fa-key"></i> Buat API Key</button>`).insertAfter('#pengaturan-form textarea[name="opendk_api_key"]')
                        $('#btn-token').on('click', function() {
                            Swal.fire({
                                title: 'Apakah anda yakin ingin membuat token baru?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Ya!',
                                cancelButtonText: 'Tidak!',
                                showLoaderOnConfirm: true,
                                preConfirm: () => {
                                    return fetch(`{{ url('api/v1/token') }}`)
                                        .then(response => {
                                            if (!response.ok) {
                                                throw new Error(response.statusText)
                                            }
                                            return response.json()
                                        })
                                        .catch(error => {
                                            Swal.showValidationMessage(
                                                `Request failed: ${error}`
                                            )
                                        })
                                },
                                allowOutsideClick: () => !Swal.isLoading()
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    Swal.fire(
                                        'Sukses!',
                                        'Token berhasil dibuat',
                                        'success'
                                    ).then(() => {
                                        $('#pengaturan-form textarea[name="opendk_api_key"]').val(result.value.access_token);
                                    });
                                }
                            });
                        });
                    }
                }).catch(err => {                    
                    Swal.fire(
                        'Error!',
                        err.toString(),
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
                            type: "PUT",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType: "json",
                            url: `{{ url('api/v1/pengaturan/settings', 1) }}`,
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
