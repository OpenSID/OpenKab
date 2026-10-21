@extends('layouts.index')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Bantuan Pengisian DataSpasial</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <strong>Alur:</strong> 1) Unduh Templat (pilih sektor dan kecamatan/semua kecamatan) 2) Unggah XLSX 3) Pratinjau & Simpan
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('dataspasial.impor.template') }}" method="get" target="_blank" id="form-template">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>Sektor</label>
                                <select id="sektor" name="sektor" class="form-control">
                                    <option value="">Pilih Sektor</option>
                                    @if(isset($sektors) && method_exists($sektors, 'data'))
                                        @foreach($sektors->data as $s)
                                            @php $attr = $s['attributes'] ?? $s; @endphp
                                            <option value="{{ $attr['kode'] ?? ($attr['id'] ?? ($s['id'] ?? '')) }}">{{ $attr['nama'] ?? ($attr['kode'] ?? '-') }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Kecamatan</label>
                                <select id="kecamatan" name="kecamatan" class="form-control">
                                    <option value="">Semua Kecamatan</option>
                                    @php
                                        $kecData = [];
                                        if(isset($kecamatans)){
                                            if(is_object($kecamatans) && method_exists($kecamatans, 'data')){
                                                $kecData = $kecamatans->data ?? [];
                                            }elseif(is_object($kecamatans) && method_exists($kecamatans, 'toArray')){
                                                $kecData = $kecamatans->toArray();
                                            }elseif(is_array($kecamatans)){
                                                $kecData = isset($kecamatans['data']) ? $kecamatans['data'] : $kecamatans;
                                            }
                                        }
                                    @endphp
                                    @foreach($kecData as $k)
                                        @php
                                            if(is_object($k)){ $attr = (array)$k; }else{ $attr = $k['attributes'] ?? $k; }
                                            $kode = $attr['kode_kecamatan'] ?? ($attr['kode'] ?? ($attr['id'] ?? ''));
                                            $nama = $attr['nama_kecamatan'] ?? ($attr['nama'] ?? ($kode ?: '-'));
                                        @endphp
                                        <option value="{{ $kode }}">{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Periode</label>
                                <input type="month" name="periode" id="periode" class="form-control" value="{{ date('Y-m') }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-download"></i> Unduh Templat</button>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Unggah File (XLSX)</label>
                            <form id="form-upload" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="sektor" id="upl_sektor">
                                <div class="input-group">
                                    <input type="file" class="form-control" name="file" id="file" accept=".xlsx" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-success" type="submit">Unggah</button>
                                    </div>
                                </div>
                                <small class="text-muted">Maks 10MB. Parsing dilakukan di OpenKab.</small>
                            </form>
                        </div>
                    </div>

                    <div class="row" id="preview-section" style="display:none;">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Pratinjau Impor</h5>
                                </div>
                                <div class="card-body">
                                    <div id="preview-summary" class="mb-2"></div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm" id="preview-table">
                                            <thead></thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <div class="mt-2 d-flex align-items-center">
                                        <input type="hidden" id="impor_id">
                                        <button class="btn btn-warning mr-2" id="btn-pratinjau">Kirim ke Pratinjau (API)</button>
                                        <a href="#" id="btn-unduh-galat" class="btn btn-secondary mr-2" style="display:none;">Unduh Laporan Galat</a>
                                        <button class="btn btn-primary" id="btn-simpan" disabled>Simpan</button>
                                    </div>
                                    <div id="save-result" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script nonce="{{ csp_nonce() }}">
document.addEventListener("DOMContentLoaded", function(event) {
    var formTemplate = document.getElementById('form-template');
    if(formTemplate){
        formTemplate.addEventListener('submit', function(e){
            var sektor = document.getElementById('sektor') ? document.getElementById('sektor').value : '';
            if(!sektor){
                e.preventDefault();
                if(typeof Swal !== 'undefined'){ Swal.fire({icon:'warning', text:'Pilih sektor terlebih dahulu'}); } else { alert('Pilih sektor terlebih dahulu'); }
                return false;
            }
        });
    }

    $('#form-upload').on('submit', function(e){
        e.preventDefault();
        var sektor = document.getElementById('sektor') ? document.getElementById('sektor').value : '';
        document.getElementById('upl_sektor').value = sektor;
        if(!sektor){
            if(typeof Swal !== 'undefined'){ Swal.fire({icon:'warning', text:'Pilih sektor terlebih dahulu'}); } else { alert('Pilih sektor terlebih dahulu'); }
            return;
        }
        var formData = new FormData(this);
        formData.set('sektor', sektor);
        $.ajax({
            url: '{{ route("dataspasial.impor.upload") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
                        success: function(res){
                            if(res.success){
                                $('#preview-section').show();
                                renderParsed(res.rows || [], res.rows_count || 0, res.invalid_count || 0);
                                var msg = 'File terparse: ' + res.rows_count + ' baris';
                                if(res.invalid_count){ msg += ' (' + res.invalid_count + ' kode desa tidak valid)'; }
                                if(typeof Swal !== 'undefined'){ Swal.fire({icon: res.invalid_count ? 'warning' : 'success', text: msg}); } else { alert(msg); }
                            }else{
                                if(typeof Swal !== 'undefined'){ Swal.fire({icon:'error', text: res.message || 'Gagal upload'}); } else { alert(res.message || 'Gagal upload'); }
                            }
                        },
            error: function(xhr){
                var msg = xhr.responseJSON?.message || 'Gagal upload';
                if(typeof Swal !== 'undefined'){ Swal.fire({icon:'error', text: msg}); } else { alert(msg); }
            }
        });
    });

    function renderParsed(rows, total, invalid){
        var table = document.getElementById('preview-table');
        var thead = table.querySelector('thead');
        var tbody = table.querySelector('tbody');
        thead.innerHTML = '';
        tbody.innerHTML = '';

        if(!rows.length){
            $('#preview-summary').html('Tidak ada baris data.');
            return;
        }

        // Bangun header dinamis dari key baris pertama (kecuali _kode_valid)
        var keys = Object.keys(rows[0]).filter(function(k){ return k !== '_kode_valid'; });
        var headerRow = document.createElement('tr');
        keys.forEach(function(k){
            var th = document.createElement('th');
            th.textContent = k;
            headerRow.appendChild(th);
        });
        var thStatus = document.createElement('th');
        thStatus.textContent = 'Status';
        headerRow.appendChild(thStatus);
        thead.appendChild(headerRow);

        rows.forEach(function(r){
            var kode = r.kode_desa || '';
            var valid = r._kode_valid !== false && kode !== '';
            var tr = document.createElement('tr');
            if(!valid){ tr.className = 'table-danger'; }
            keys.forEach(function(k){
                var td = document.createElement('td');
                td.textContent = (r[k] === null || r[k] === undefined || r[k] === '') ? '-' : r[k];
                tr.appendChild(td);
            });
            var tdStatus = document.createElement('td');
            tdStatus.innerHTML = valid
                ? '<span class="badge badge-success">Valid</span>'
                : '<span class="badge badge-danger">Kode Desa tidak valid</span>';
            tr.appendChild(tdStatus);
            tbody.appendChild(tr);
        });

        $('#preview-summary').html('Total baris: ' + total + ' | Tidak valid: ' + invalid);
        $('#btn-simpan').prop('disabled', true);
        $('#btn-unduh-galat').hide();
    }

    function renderPreviewTable(rows){
        var table = document.getElementById('preview-table');
        var thead = table.querySelector('thead');
        var tbody = table.querySelector('tbody');
        thead.innerHTML = '';
        tbody.innerHTML = '';

        if(!rows.length){
            $('#preview-summary').append(' <small>Tidak ada baris dilaporkan.</small>');
            return;
        }

        var baseColumns = ['nomor_baris', 'kode_desa', 'nama_desa', 'kode_fasilitas', 'nama_fasilitas', 'periode'];

        // Kumpulkan kolom nilai (isian) dinamis dari seluruh baris
        var nilaiKeys = [];
        rows.forEach(function(r){
            if(r.nilai && typeof r.nilai === 'object'){
                Object.keys(r.nilai).forEach(function(k){
                    if(nilaiKeys.indexOf(k) === -1 && k.indexOf('_') !== 0){ nilaiKeys.push(k); }
                });
            }
        });

        var columns = baseColumns.concat(nilaiKeys).concat(['status', 'pesan']);
        var headerRow = document.createElement('tr');
        columns.forEach(function(c){
            var th = document.createElement('th');
            th.textContent = c;
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);

        rows.forEach(function(r){
            var tr = document.createElement('tr');
            var status = (r.status || '').toLowerCase();
            if(status.indexOf('galat') !== -1){ tr.className = 'table-danger'; }
            else if(status.indexOf('menimpa') !== -1){ tr.className = 'table-warning'; }
            else if(status.indexOf('siap') !== -1){ tr.className = 'table-success'; }

            columns.forEach(function(c){
                var td = document.createElement('td');
                var v;
                if(nilaiKeys.indexOf(c) !== -1){
                    v = (r.nilai && r.nilai[c] !== undefined) ? r.nilai[c] : '';
                } else {
                    v = r[c];
                }
                if(v === null || v === undefined || v === ''){ v = '-'; }
                if(c === 'status'){
                    var badge = 'badge-secondary';
                    if(status.indexOf('galat') !== -1){ badge = 'badge-danger'; }
                    else if(status.indexOf('menimpa') !== -1){ badge = 'badge-warning'; }
                    else if(status.indexOf('siap') !== -1){ badge = 'badge-success'; }
                    td.innerHTML = '<span class="badge ' + badge + '">' + v + '</span>';
                } else {
                    td.textContent = v;
                }
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
    }

    $('#btn-pratinjau').on('click', function(){
        var sektor = document.getElementById('sektor') ? document.getElementById('sektor').value : '';
        document.getElementById('upl_sektor').value = sektor;
        var $btn = $(this);
        $btn.prop('disabled', true).text('Memproses...');
        $('#save-result').html('');
        $.ajax({
            url: '{{ route("dataspasial.impor.preview") }}',
            type: 'POST',
            data: {_token: '{{ csrf_token() }}'},
            success: function(res){
                if(res.success){
                    var p = res.preview || {};
                    $('#impor_id').val(p.impor_id || p.id || '');
                    var ringkasan = p.ringkasan || {};
                    var summary = 'Total: ' + (ringkasan.total_baris ?? '-')
                        + ' | Siap: ' + (ringkasan.baris_siap ?? '-')
                        + ' | Menimpa: ' + (ringkasan.baris_menimpa ?? '-')
                        + ' | Konflik: ' + (ringkasan.baris_konflik ?? '-')
                        + ' | Galat: ' + (ringkasan.baris_galat ?? '-');
                    var bisaSimpan = !!ringkasan.bisa_disimpan;
                    var alertClass = bisaSimpan ? 'alert-success' : 'alert-warning';
                    $('#preview-summary').html('<div class="alert ' + alertClass + ' mb-0">Pratinjau berhasil. ' + summary + (bisaSimpan ? '' : ' (belum bisa disimpan)') + '</div>');

                    renderPreviewTable(p.baris || []);

                    $('#btn-simpan').prop('disabled', !bisaSimpan || !$('#impor_id').val());
                    if(p.laporan_galat_url || p.error_report_url){
                        $('#btn-unduh-galat').attr('href', p.laporan_galat_url || p.error_report_url).show();
                    } else {
                        // Sediakan unduhan laporan galat lokal dari data baris berstatus galat
                        var errRows = (p.baris || []).filter(function(r){ return (r.status||'').toLowerCase().indexOf('galat') !== -1 || r.pesan; });
                        if(errRows.length){
                            var csv = 'nomor_baris;kode_desa;nama_desa;kode_fasilitas;nama_fasilitas;periode;status;pesan\n';
                            errRows.forEach(function(r){
                                csv += [r.nomor_baris, r.kode_desa, r.nama_desa, r.kode_fasilitas, r.nama_fasilitas, r.periode, r.status, (r.pesan||'').replace(/;/g,',')].join(';') + '\n';
                            });
                            var blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
                            $('#btn-unduh-galat').attr('href', URL.createObjectURL(blob)).attr('download', 'laporan_galat.csv').show();
                        }
                    }
                    if(typeof Swal !== 'undefined'){ Swal.fire({icon: bisaSimpan ? 'success' : 'warning', text:'Pratinjau berhasil. ' + summary}); }
                }else{
                    var msg = res.message || 'Gagal pratinjau';
                    $('#preview-summary').html('<div class="alert alert-danger mb-0">Gagal pratinjau: ' + msg + '</div>');
                    $('#btn-simpan').prop('disabled', true);
                    if(typeof Swal !== 'undefined'){ Swal.fire({icon:'error', text: msg}); } else { alert(msg); }
                }
            },
            error: function(xhr){
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal pratinjau';
                $('#preview-summary').html('<div class="alert alert-danger mb-0">Gagal pratinjau: ' + msg + '</div>');
                $('#btn-simpan').prop('disabled', true);
                if(typeof Swal !== 'undefined'){ Swal.fire({icon:'error', text: msg}); } else { alert(msg); }
            },
            complete: function(){
                $btn.prop('disabled', false).text('Kirim ke Pratinjau (API)');
            }
        });
    });

    $('#btn-simpan').on('click', function(){
        var id = $('#impor_id').val();
        if(!id){ if(typeof Swal !== 'undefined'){ Swal.fire({icon:'warning', text:'Tidak ada impor_id'}); } else { alert('Tidak ada impor_id'); } return; }
        if(typeof Swal !== 'undefined'){
            Swal.fire({
                title: 'Simpan data impor ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then((result)=>{
                if(result.isConfirmed){
                    doSimpan(id);
                }
            });
        }else{
            if(!confirm('Simpan data impor ini?')) return;
            doSimpan(id);
        }
    });

    function doSimpan(id){
        $.ajax({
            url: '{{ url("dataspasial/impor/simpan") }}/' + id,
            type: 'POST',
            data: {_token: '{{ csrf_token() }}'},
            success: function(res){
                if(res.success){
                    $('#save-result').html('<div class="alert alert-success">Impor berhasil disimpan.</div>');
                    $('#btn-simpan').prop('disabled', true);
                    if(typeof Swal !== 'undefined'){
                        Swal.fire({icon:'success', text:'Impor berhasil disimpan.'}).then(function(){
                            window.location.reload();
                        });
                    } else {
                        alert('Impor berhasil disimpan.');
                        window.location.reload();
                    }
                }else{
                    var msg = res.message || 'Gagal menyimpan';
                    if(typeof Swal !== 'undefined'){ Swal.fire({icon:'error', text: msg}); } else { alert(msg); }
                }
            },
            error: function(xhr){
                var msg = xhr.responseJSON?.message || 'Gagal menyimpan';
                if(typeof Swal !== 'undefined'){ Swal.fire({icon:'error', text: msg}); } else { alert(msg); }
            }
        });
    }
});
</script>
@endpush
