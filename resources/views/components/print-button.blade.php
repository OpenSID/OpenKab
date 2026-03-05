{{-- resources/views/components/print-button.blade.php --}}
@props([
    'label' => 'Cetak',
    'icon' => 'fa fa-print',
    'variant' => 'btn-primary',
    'size' => 'btn-sm',
    'printUrl' => '',
    'tableId' => '',
    'filters' => [],
    'additionalParams' => [],
])

@php
    $buttonId = 'print-btn-' . $tableId;
@endphp

<button type="button" class="btn {{ $variant }} {{ $size }}" id="{{ $buttonId }}"
    data-print-url="{{ $printUrl }}" data-table-id="{{ $tableId }}" data-filters="{{ json_encode($filters) }}"
    data-additional-params="{{ json_encode($additionalParams) }}">
    <i class="{{ $icon }}"></i>
    {{ $label }}
</button>

<script nonce="{{ csp_nonce() }}">
    document.addEventListener("DOMContentLoaded", function() {
        const printButton = document.getElementById('{{ $buttonId }}');

        if (printButton) {
            printButton.addEventListener('click', function(e) {
                e.preventDefault();

                const printUrl = new URL(this.dataset.printUrl);
                printUrl.searchParams.set("kode_kabupaten", "{{ session('kabupaten.kode_kabupaten') ?? '' }}");
                printUrl.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
                printUrl.searchParams.set("kode_desa", "{{ session('desa.id') ?? '' }}");                
                const tableId = this.dataset.tableId;
                const filters = JSON.parse(this.dataset.filters || '{}');
                const additionalParams = JSON.parse(this.dataset.additionalParams || '{}');

                // Add filter parameters
                Object.entries(filters).forEach(([filterId, paramName]) => {
                    const filterElement = document.getElementById(filterId);
                    if (filterElement && filterElement.value) {
                        printUrl.searchParams.append(paramName, filterElement.value);
                    }
                });

                // Add search parameter from DataTable
                if (tableId) {
                    const dataTable = $('#' + tableId).DataTable();
                    // Get filters/search from DataTable's ajax.params
                    if (typeof dataTable.ajax.params === 'function') {
                        const params = dataTable.ajax.params();
                        
                        Object.entries(params).forEach(([key, value]) => {
                            if (value && value !== '' && value !== 'null') {
                                printUrl.searchParams.append(key, value);
                            }
                        });
                    }
                }

                // Add additional static parameters
                Object.entries(additionalParams).forEach(([key, value]) => {
                    printUrl.searchParams.append(key, value);
                });                
                // Open print URL in new window
                window.open(printUrl.href, '_blank');
            });
        }
    });
</script>
