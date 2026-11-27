@extends('layouts.index')

@section('title', 'Cek Domain API')

@section('content_header')
    <h1>Cek Domain API Database Gabungan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-globe mr-1"></i>
                        Domain Validation Check
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Klik tombol di bawah untuk mengecek domain apa yang terbaca oleh API Database Gabungan.
                        Ini sangat membantu troubleshooting ketika dropdown Kabupaten/Kecamatan kosong atau error "Domain tidak diizinkan".
                    </p>

                    <button type="button" id="btnCheckDomain" class="btn btn-primary btn-lg">
                        <i class="fas fa-search mr-1"></i>
                        Cek Domain Sekarang
                    </button>

                    <div id="loading" class="mt-3" style="display: none;">
                        <i class="fas fa-spinner fa-spin mr-1"></i>
                        Memeriksa domain...
                    </div>

                    <div id="result" class="mt-4" style="display: none;">
                        {{-- Result will be populated by JavaScript --}}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-4">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-1"></i>
                        Informasi
                    </h3>
                </div>
                <div class="card-body">
                    <h5><strong>Apa ini?</strong></h5>
                    <p class="text-muted text-sm">
                        Tool ini mengecek domain apa yang terbaca oleh API Database Gabungan saat request masuk.
                    </p>

                    <h5><strong>Kapan digunakan?</strong></h5>
                    <ul class="text-muted text-sm">
                        <li>Dropdown Kabupaten/Kecamatan kosong</li>
                        <li>Error "Domain tidak diizinkan"</li>
                        <li>Request API gagal tanpa penjelasan</li>
                    </ul>

                    <h5><strong>Bagaimana cara kerjanya?</strong></h5>
                    <ol class="text-muted text-sm">
                        <li>Klik tombol "Cek Domain Sekarang"</li>
                        <li>Sistem akan memanggil API dengan token Anda</li>
                        <li>API mendeteksi domain dari request headers</li>
                        <li>Hasil ditampilkan di sini</li>
                    </ol>

                    <hr>

                    <h5><strong>Interpretasi Hasil:</strong></h5>
                    <ul class="text-muted text-sm">
                        <li><span class="text-success"><i class="fas fa-check-circle"></i> Diizinkan</span> — Domain sudah benar</li>
                        <li><span class="text-danger"><i class="fas fa-times-circle"></i> Ditolak</span> — Domain perlu ditambahkan</li>
                        <li><span class="text-info"><i class="fas fa-server"></i> Server-side</span> — Request dari backend</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style nonce="{{ csp_nonce() }}">
        .result-card {
            border-left: 4px solid #007bff;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .result-card.success {
            border-left-color: #28a745;
        }
        .result-card.danger {
            border-left-color: #dc3545;
        }
        .result-card.warning {
            border-left-color: #ffc107;
        }
        .result-item {
            margin-bottom: 10px;
        }
        .result-label {
            font-weight: bold;
            color: #495057;
            display: inline-block;
            min-width: 150px;
        }
        .result-value {
            color: #212529;
        }
        .badge-status {
            font-size: 0.9em;
            padding: 5px 10px;
        }
        .headers-table {
            font-size: 0.9em;
        }
        .headers-table td {
            padding: 5px 10px;
        }
        .recommendation-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
        }
    </style>
@stop

@section('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function (event) {
            $('#btnCheckDomain').click(function() {
                var btn = $(this);
                btn.prop('disabled', true);
                $('#loading').show();
                $('#result').hide();

                $.ajax({
                    url: '{{ route("domain-check.check") }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#result').html(renderResult(response)).show();
                    },
                    error: function(xhr) {
                        var message = 'Terjadi kesalahan';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        $('#result').html(renderError(message)).show();
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                        $('#loading').hide();
                    }
                });
            });

            function renderResult(data) {
                var statusClass = data.is_allowed ? 'success' : 'danger';
                var statusText = data.is_allowed ? '✅ Diizinkan' : '❌ Ditolak';
                var statusBadge = data.is_allowed ? 'badge-success' : 'badge-danger';
                var serverSideBadge = data.is_server_side 
                    ? '<span class="badge badge-info badge-status">Server-side</span>' 
                    : '<span class="badge badge-warning badge-status">Browser</span>';

                var html = '<div class="result-card ' + statusClass + '">';
                html += '<h4 class="mb-3">Hasil Pengecekan Domain</h4>';
                
                // Domain info
                html += '<div class="result-item">';
                html += '<span class="result-label">Domain Terdeteksi:</span>';
                html += '<span class="result-value"><strong>' + escapeHtml(data.detected_domain) + '</strong></span>';
                html += '</div>';
                
                html += '<div class="result-item">';
                html += '<span class="result-label">Sumber Deteksi:</span>';
                html += '<span class="result-value">' + escapeHtml(data.detection_source) + '</span>';
                html += '</div>';
                
                html += '<div class="result-item">';
                html += '<span class="result-label">Tipe Request:</span>';
                html += '<span class="result-value">' + serverSideBadge + '</span>';
                html += '</div>';
                
                html += '<div class="result-item">';
                html += '<span class="result-label">Status:</span>';
                html += '<span class="badge ' + statusBadge + ' badge-status">' + statusText + '</span>';
                html += '</div>';
                
                // Headers
                html += '<hr>';
                html += '<h5>Detail Headers</h5>';
                html += '<table class="table table-sm headers-table">';
                html += '<tbody>';
                for (var key in data.headers) {
                    if (data.headers.hasOwnProperty(key)) {
                        html += '<tr>';
                        html += '<td><strong>' + escapeHtml(key) + '</strong></td>';
                        html += '<td>' + escapeHtml(data.headers[key]) + '</td>';
                        html += '</tr>';
                    }
                }
                html += '</tbody></table>';
                
                // User info
                html += '<hr>';
                html += '<h5>Informasi User</h5>';
                html += '<div class="result-item">';
                html += '<span class="result-label">User ID:</span>';
                html += '<span class="result-value">' + data.user.id + '</span>';
                html += '</div>';
                html += '<div class="result-item">';
                html += '<span class="result-label">Nama:</span>';
                html += '<span class="result-value">' + escapeHtml(data.user.name) + '</span>';
                html += '</div>';
                html += '<div class="result-item">';
                html += '<span class="result-label">Allowed Domains:</span>';
                html += '<span class="result-value">' + escapeHtml(data.user.allowed_domains.join(', ')) + '</span>';
                html += '</div>';
                html += '<div class="result-item">';
                html += '<span class="result-label">Wildcard:</span>';
                html += '<span class="result-value">' + (data.user.is_wildcard ? 'Ya' : 'Tidak') + '</span>';
                html += '</div>';
                
                // Recommendation
                html += '<hr>';
                html += '<h5>Rekomendasi</h5>';
                html += '<div class="recommendation-box">';
                html += '<i class="fas fa-lightbulb mr-1 text-warning"></i>';
                html += escapeHtml(data.recommendation);
                html += '</div>';
                
                html += '</div>';
                
                return html;
            }

            function renderError(message) {
                var html = '<div class="result-card danger">';
                html += '<h4 class="mb-3"><i class="fas fa-exclamation-triangle mr-1"></i> Error</h4>';
                html += '<p>' + escapeHtml(message) + '</p>';
                html += '</div>';
                return html;
            }

            function escapeHtml(text) {
                if (text === null || text === undefined) {
                    return '-';
                }
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
            }
        });
    </script>
@stop
