@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Suplemen')

@section('content_header')
    <h1>Data Suplemen</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @if (Session::has('success'))
        <div class="alert alert-success">
            {{ Session::get('success') }}
        </div>
    @elseif (Session::has('error'))
        <div class="alert alert-danger">
            {{ Session::get('error') }}
        </div>
    @endif
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('suplemen') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-circle-left"></i>&ensp;Kembali ke Daftar Data Suplemen
                    </a>
                </div>
                <form method="POST" class="form-horizontal" id="formSuplemen">
                    @csrf
                    <input type="hidden" name="sumber" value="OpenKab">
                    <input type="hidden" name="form_isian" id="form_isian">
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="sasaran">Sasaran Data</label>
                            <div class="col-sm-9">
                                <select class="form-control form-control-sm required" required name="sasaran">
                                    <option value="">Pilih Sasaran</option>
                                    @foreach ($list_sasaran as $key => $sasaran)
                                        @if (in_array($key, ['1', '2']))
                                            <option value="{{ $key }}">{{ $sasaran }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="nama">Nama Data Suplemen</label>
                            <div class="col-sm-9">
                                <input class="form-control form-control-sm required" maxlength="100" placeholder="Nama Data Suplemen" type="text" name="nama" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="keterangan">Keterangan</label>
                            <div class="col-sm-9">
                                <textarea name="keterangan" class="form-control form-control-sm" maxlength="300" placeholder="Keterangan" rows="3" style="resize:none;"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="status">Status</label>
                            <div class="col-sm-9">
                                <select class="form-control form-control-sm required" required name="status">
                                    <option value="">Pilih Status</option>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            @include('suplemen.kode_isian')

                        </div>


                    </div>
                    <div class="card-footer">
                        <button type="reset" class="btn btn-danger btn-sm" onclick="resetForm();">
                            <i class="fa fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-info btn-sm pull-right">
                            <i class="fa fa-check"></i> Simpan
                        </button>
                    </div>                    
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    function resetForm() {
        const form = document.getElementById('formSuplemen');
        if (form) {
            form.reset();
        }
    }

    document.getElementById('formSuplemen').addEventListener('submit', async function (e) {
        const rows = document.querySelectorAll('#dragable-form-utama tr.duplikasi');
        let formDatas = [];

        // Mengumpulkan data dari setiap row
        rows.forEach(function(row) {
            const tipe = row.querySelector('select.pilih_tipe').value;
            const namaKode = row.querySelector('input[name="nama_kode[]"]').value;
            const labelKode = row.querySelector('input[name="label_kode[]"]').value;
            const deskripsiKode = row.querySelector('input[name="deskripsi_kode[]"]').value;
            const required = row.querySelector('input.isian-required').checked ? 1 : 0;
            const kolom = row.querySelector('select[name="kolom[]"]').value;
            const atribut = row.querySelector('textarea[name="atribut_kode[]"]').value;
            const pilihanKode = row.querySelector('textarea[name="pilihan_kode[]"]').value;
            const referensiKode = row.querySelector('select[name="referensi_kode[]"]').value;

            // Menyimpan data dalam array
            formDatas.push({
                tipe: tipe,
                nama_kode: namaKode,
                label_kode: labelKode,
                deskripsi_kode: deskripsiKode,
                required: required,
                kolom: kolom,
                atribut: atribut,
                pilihan_kode: pilihanKode,
                referensi_kode: referensiKode
            });
        });

        // Menyimpan data ke dalam input hidden
        document.getElementById('form_isian').value = JSON.stringify(formDatas);

        e.preventDefault();

        const formData = new FormData(this);
        const jsonData = Object.fromEntries(formData.entries());

        try {
            const response = await fetch("{{ url('api/v1/suplemen') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    // Pastikan Bearer Token disesuaikan jika diperlukan
                    'Authorization': 'Bearer {{ session('api_token') ?? '' }}'
                },
                body: JSON.stringify(jsonData)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // alert('Data berhasil disimpan!');
                window.location.href = "{{ route('suplemen') }}";
            } else {
                // alert('Gagal menyimpan data: ' + (data.message || 'Terjadi kesalahan.'));
            }
        } catch (error) {
            console.error('Error:', error);
            // alert('Terjadi kesalahan saat menyimpan data.');
        }
    });

</script>
@endsection
