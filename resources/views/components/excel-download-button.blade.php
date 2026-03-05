@props([
    'id' => 'download-excel',
    'label' => 'Excel',
    'size' => 'btn-sm',
    'variant' => 'btn-success',
    'icon' => 'fa fa-file-excel',
    'disabled' => false,
    'loadingText' => 'Downloading...',
    'downloadUrl' => '',
    'tableId' => '',
    'filename' => 'data_export',
    'apiHeaders' => [],
    'additionalParams' => [],
    'class' => '',
])

<button id="{{ $id }}" type="button" class="btn {{ $variant }} {{ $size }} {{ $class }}"
    @if ($disabled) disabled @endif data-download-url="{{ $downloadUrl }}"
    data-table-id="{{ $tableId }}" data-filename="{{ $filename }}">
    <i class="{{ $icon }}"></i>
    {{ $label }}
</button>

@once
    @push('js')
        <script nonce="{{ csp_nonce() }}">
            document.addEventListener("DOMContentLoaded", function() {
                // Handle all excel download buttons
                document.querySelectorAll('[data-download-url]').forEach(function(button) {
                    button.addEventListener('click', function() {
                        const downloadUrl = this.getAttribute('data-download-url');
                        const tableId = this.getAttribute('data-table-id');
                        const filename = this.getAttribute('data-filename');

                        downloadExcelData(this, downloadUrl, tableId, filename);
                    });
                });

                async function downloadExcelData(button, downloadUrl, tableId, filename) {
                    const originalHtml = button.innerHTML;
                    try {
                        // Get headers - try to use the global header from the page
                        const headers = @include('layouts.components.header_bearer_api_gabungan');

                        // Get table instance if tableId is provided
                        if (tableId) {
                            const table = $('#' + tableId).DataTable();
                            const info = table.page.info();
                            const totalData = info.recordsTotal;

                            if (totalData === 0) {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Tidak Ada Data',
                                        text: 'Tidak ada data untuk diunduh. Silakan periksa filter Anda.',
                                        confirmButtonText: 'OK'
                                    });
                                } else {
                                    alert('Tidak ada data untuk diunduh');
                                }
                                return;
                            }
                        }

                        // Show loading state
                        button.disabled = true;
                        button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Downloading...';

                        // Prepare download URL
                        const url = new URL(downloadUrl);
                        url.searchParams.set("kode_kabupaten", "{{ session('kabupaten.kode_kabupaten') ?? '' }}");
                        url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
                        url.searchParams.set("kode_desa", "{{ session('desa.id') ?? '' }}");                
                        let urlParams = new URLSearchParams();

                        // Add additional params
                        @if ($additionalParams)
                            @foreach ($additionalParams as $param)
                                urlParams.append('{{ $param['key'] }}', '{{ $param['value'] }}');
                            @endforeach
                        @endif
                        if (tableId) {
                            // Get filter parameters from DataTable
                            const table = $('#' + tableId).DataTable();
                            const filterParams = table.ajax.params();

                            // Remove pagination parameters since we want all data
                            delete filterParams['page[size]'];
                            delete filterParams['page[number]'];

                            // Handle umur filter conversion if exists
                            if (filterParams['filter[umur]'] && typeof filterParams['filter[umur]'] === 'object') {
                                const umurObj = filterParams['filter[umur]'];

                                if (umurObj.min && umurObj.min !== '') {
                                    filterParams['filter[umur][min]'] = umurObj.min;
                                }
                                if (umurObj.max && umurObj.max !== '') {
                                    filterParams['filter[umur][max]'] = umurObj.max;
                                }
                                if (umurObj.satuan) {
                                    filterParams['filter[umur][satuan]'] = umurObj.satuan;
                                }

                                delete filterParams['filter[umur]'];
                            }

                            // Convert to URLSearchParams
                            Object.keys(filterParams).forEach(key => {
                                const value = filterParams[key];
                                if (value !== null && value !== undefined && value !== '' && value !==
                                    'null') {
                                    urlParams.append(key, value);
                                }
                            });

                            const info = table.page.info();
                            urlParams.append('totalData', info.recordsTotal);
                        }
        
                        // Make fetch request
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                ...headers,
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                            },
                            body: urlParams
                        });

                        if (!response.ok) {
                            const errorText = await response.text();
                            throw new Error(`HTTP ${response.status}: ${errorText}`);
                        }

                        // Validate response content type
                        const contentType = response.headers.get('content-type');
                        if (!contentType || (!contentType.includes(
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') &&
                                !contentType.includes('application/vnd.ms-excel'))) {
                            throw new Error('Response is not a valid Excel file');
                        }

                        // Get filename from response headers or generate one
                        const contentDisposition = response.headers.get('content-disposition');
                        let finalFilename = filename + '.xlsx';

                        if (contentDisposition) {
                            const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(contentDisposition);
                            if (matches != null && matches[1]) {
                                finalFilename = matches[1].replace(/['"]/g, '');
                            }
                        } else {
                            // Generate filename with timestamp
                            const now = new Date();
                            const timestamp = now.toISOString().slice(0, 19).replace(/[-:T]/g, '');
                            finalFilename = `${filename}_${timestamp}.xlsx`;
                        }

                        // Create blob and download
                        const blob = await response.blob();
                        const downloadLink = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = downloadLink;
                        a.download = finalFilename;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(downloadLink);
                        document.body.removeChild(a);

                        // Show success message
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: `File Excel "${finalFilename}" berhasil diunduh`,
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }

                    } catch (error) {
                        console.error('Download error:', error);

                        // Show error message
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Download!',
                                html: `
                        <p>Terjadi kesalahan saat mengunduh file Excel:</p>
                        <p><small>${error.message}</small></p>
                        <p>Silakan coba lagi atau hubungi administrator.</p>
                    `,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('Gagal download: ' + error.message);
                        }
                    } finally {
                        // Reset button state
                        console.log('Resetting button state');
                        console.log('Original HTML:', originalHtml);
                        button.disabled = false;
                        button.innerHTML = originalHtml;
                    }
                }
            });
        </script>
    @endpush
@endonce
