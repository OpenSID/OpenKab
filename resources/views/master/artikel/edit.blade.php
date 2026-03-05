@extends('layouts.index')

@section('title', 'Edit Artikel')

@section('content_header')
    <h1>Edit Artikel</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row" x-data="artikel()" x-init="init()">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('master-data-artikel.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-circle-left"></i>&ensp;Kembali ke Daftar Artikel
                    </a>
                </div>
                <div class="card-body">
                    <form id="artikel-form" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Kolom Kiri: Editor -->
                            <div class="col-md-9">
                                <!-- Judul -->
                                <div class="form-group">
                                    <label for="judul">Judul Artikel <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="judul" name="judul"
                                        x-model="dataArtikel.judul" placeholder="Masukkan judul artikel" required>
                                </div>

                                <!-- Isi Artikel -->
                                <div class="form-group">
                                    <label for="isi">Isi Artikel <span class="text-danger">*</span></label>
                                    <textarea class="form-control editor" id="isi" name="isi" rows="10" required></textarea>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Sidebar -->
                            <div class="col-md-3">
                                <!-- Upload Gambar Utama -->
                                <div class="form-group">
                                    <div class="card card-widget">
                                        <div class="widget-user-header text-center">
                                            <img id="imageResult"
                                                :src="dataArtikel.gambar || '{{ asset('assets/img/no-image.png') }}'"
                                                alt="Gambar Utama" width="200px">
                                        </div>
                                        <div class="card-footer">
                                            <div class="input-group mb-3 px-2 py-2 bg-white shadow-sm">
                                                <input id="upload_gambar" name="upload_gambar" type="file"
                                                    accept="image/*" class="form-control border-0 fade"
                                                    @change="handleFileUpload($event, 'gambar')">
                                                <div class="input-group-append col-12">
                                                    <label for="upload_gambar"
                                                        class="btn col-12 btn-primary m-0 rounded-pill">
                                                        <i class="fa fa-cloud-upload mr-2 text-muted"></i>
                                                        <small class="text-uppercase text-white font-weight-bold">Gambar
                                                            Utama</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kategori -->
                                <div class="form-group">
                                    <label for="id_kategori">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="id_kategori" name="id_kategori" required>
                                        <option value="">-- Pilih Kategori --</option>
                                    </select>
                                </div>

                                <!-- Status -->
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="enabled" name="enabled"
                                            x-model="dataArtikel.enabled" value="1">
                                        <label class="custom-control-label" for="enabled">Aktif</label>
                                    </div>
                                </div>

                                <!-- Tanggal Upload -->
                                <div class="form-group">
                                    <label for="tgl_upload">Tanggal Upload</label>
                                    <input type="date" class="form-control" id="tgl_upload" name="tgl_upload"
                                        x-model="dataArtikel.tgl_upload">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="window.location='{{ route('master-data-artikel.index') }}'">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" x-on:click="simpan()">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
   <style nonce="{{ csp_nonce() }}" >
        .widget-user-header {
            padding: 20px;
            background: #f4f6f9;
        }

        .widget-user-header img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }

        #upload_gambar {
            opacity: 0;
            position: absolute;
            z-index: -1;
        }
    </style>
@stop

@section('js')
    @include('partials.asset_tinymce')
    <script nonce="{{ csp_nonce() }}">
        const header = @include('layouts.components.header_bearer_api_gabungan');
        const artikelId = {{ $id }};

        function artikel() {
            return {
                dataArtikel: {
                    id: artikelId,
                    judul: '',
                    isi: '',
                    id_kategori: '',
                    enabled: true,
                    tgl_upload: '',
                    headline: '0',
                    gambar: ''
                },
                editor: null,
                fileData: {
                    gambar: null
                },

                init() {
                    // Load kategori first, then load data
                    this.loadKategori().then(() => {
                        this.loadData();
                    });
                },

                handleFileUpload(event, field) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Store file for later upload
                    this.fileData[field] = file;

                    // Preview image
                    if (file.type.startsWith('image/') && field === 'gambar') {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            document.getElementById('imageResult').src = e.target.result;
                            this.dataArtikel.gambar = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                async uploadFile(file, field) {
                    const formData = new FormData();
                    formData.append('file', file);

                    try {
                        const response = await fetch('{{ route('artikel.upload_gambar') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (response.ok && result.success && result.url) {
                            // Ensure the URL is absolute
                            const baseUrl = '{{ url('/') }}';
                            const fullUrl = result.url.startsWith('http') ? result.url : `${baseUrl}${result.url}`;
                            return fullUrl;
                        }

                        throw new Error(result.message || 'Upload gagal');
                    } catch (error) {
                        console.error('Error uploading file:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Upload Gagal',
                            text: error.message
                        });
                        return null;
                    }
                },

                loadKategori() {
                    const self = this; // Store reference to this
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: `{{ config('app.databaseGabunganUrl') . '/api/v1/kategori' }}`,
                            headers: header,
                            data: {
                                "page[size]": 1000,
                                "page[number]": 1
                            },
                            success: function(response) {
                                const select = $('#id_kategori');
                                select.empty();
                                select.append('<option value="">-- Pilih Kategori --</option>');

                                if (response.data) {
                                    response.data.forEach(function(item) {
                                        select.append(
                                            `<option value="${item.id}">${item.attributes.kategori}</option>`
                                        );
                                    });
                                }

                                select.select2({
                                    theme: 'bootstrap4',
                                    placeholder: '-- Pilih Kategori --'
                                });

                                // Update Alpine.js model when Select2 changes
                                select.on('change', function() {
                                    self.dataArtikel.id_kategori = $(this).val();
                                    console.log('Kategori changed to:', self.dataArtikel
                                        .id_kategori);
                                });

                                resolve();
                            },
                            error: function(xhr) {
                                console.error('Error loading kategori:', xhr);
                                reject(xhr);
                            }
                        });
                    });
                },
                loadData() {
                    $.ajax({
                        url: `{{ config('app.databaseGabunganUrl') . '/api/v1/artikel/tampil' }}`,
                        headers: header,
                        data: {
                            id: artikelId
                        },
                        success: (response) => {
                            if (response.data) {
                                const data = response.data;
                                this.dataArtikel = {
                                    id: artikelId,
                                    judul: data.judul || '',
                                    isi: data.isi || '',
                                    id_kategori: data.id_kategori || '',
                                    enabled: data.enabled == 1,
                                    tgl_upload: data.tgl_upload ? data.tgl_upload.split(' ')[0] : '',
                                    headline: '0',
                                    gambar: data.gambar || ''
                                };

                                // Set kategori di select2
                                $('#id_kategori').val(this.dataArtikel.id_kategori).trigger('change');

                                // Set content di TinyMCE
                                setTimeout(() => {
                                    if (typeof tinymce !== 'undefined' && tinymce.get('isi')) {
                                        tinymce.get('isi').setContent(this.dataArtikel.isi || '');
                                    }
                                }, 1000);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'Gagal memuat data artikel', 'error');
                            console.error('Error loading artikel:', xhr);
                        }
                    });
                },

                async simpan() {
                    // Get content from TinyMCE
                    let isi = '';
                    if (typeof tinymce !== 'undefined' && tinymce.get('isi')) {
                        isi = tinymce.get('isi').getContent();
                    }

                    // Get kategori value from Select2
                    const kategoriValue = $('#id_kategori').val();
                    if (kategoriValue) {
                        this.dataArtikel.id_kategori = kategoriValue;
                    }

                    // Validasi
                    if (!this.dataArtikel.judul) {
                        Swal.fire('Error!', 'Judul artikel harus diisi', 'error');
                        return;
                    }
                    if (!isi) {
                        Swal.fire('Error!', 'Isi artikel harus diisi', 'error');
                        return;
                    }
                    if (!this.dataArtikel.id_kategori) {
                        Swal.fire('Error!', 'Kategori harus dipilih', 'error');
                        return;
                    }

                    Swal.fire({
                        title: 'Sedang Menyimpan',
                        text: 'Mohon tunggu...',
                        didOpen: () => {
                            Swal.showLoading()
                        },
                        allowOutsideClick: false
                    });

                    // Upload files if any
                    for (const field in this.fileData) {
                        if (this.fileData[field]) {
                            const url = await this.uploadFile(this.fileData[field], field);
                            if (url) {
                                this.dataArtikel[field] = url;
                            }
                        }
                    }

                    // Konversi checkbox ke string
                    const postData = {
                        ...this.dataArtikel,
                        isi: isi,
                        enabled: this.dataArtikel.enabled ? '1' : '0',
                        headline: '0' // Always 0
                    };

                    $.ajax({
                        type: "PUT",
                        headers: header,
                        dataType: "json",
                        url: `{{ config('app.databaseGabunganUrl') . '/api/v1/artikel/perbarui' }}/${artikelId}`,
                        data: JSON.stringify(postData),
                        contentType: 'application/json',
                        success: function(response) {
                            if (response.success == true) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Artikel berhasil diperbarui',
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 1500,
                                });
                                setTimeout(() => {
                                    window.location.href =
                                        '{{ route('master-data-artikel.index') }}?clear_cache=' +
                                        artikelId;
                                }, 1500);
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message,
                                    icon: 'error',
                                    showConfirmButton: true,
                                    allowOutsideClick: false
                                });
                            }
                        },
                        error: function(xhr, textStatus, errorThrown) {
                            Swal.fire({
                                title: 'Error!',
                                text: xhr.responseJSON?.message || errorThrown,
                                icon: 'error',
                                showConfirmButton: true,
                                allowOutsideClick: false
                            });
                        }
                    });
                }
            }
        }
    </script>
@endsection
