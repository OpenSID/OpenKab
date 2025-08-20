@props([
    'kabupatenId' => 'filter_kabupaten',
    'kecamatanId' => 'filter_kecamatan',
    'desaId' => 'filter_desa',
    'filterButtonId' => 'bt_filter',
    'clearButtonId' => 'bt_clear_filter',
    'onFilterChange' => null,
    'apiUrl' => '',
    'apiHeaders' => '{}',
    'loadOnInit' => true,
])

@once
    @push('js')
        <script nonce="{{ csp_nonce() }}">
            document.addEventListener("DOMContentLoaded", function() {
                const wilayahFilter = {
                    kabupatenSelect: document.getElementById('{{ $kabupatenId }}'),
                    kecamatanSelect: document.getElementById('{{ $kecamatanId }}'),
                    desaSelect: document.getElementById('{{ $desaId }}'),
                    filterButton: document.getElementById('{{ $filterButtonId }}'),
                    clearButton: document.getElementById('{{ $clearButtonId }}'),
                    apiHeaders: {!! json_encode($apiHeaders) !!},

                    init() {
                        this.bindEvents();
                        @if ($loadOnInit)
                            this.loadKabupaten();
                        @endif
                    },

                    bindEvents() {
                        if (this.kabupatenSelect) {
                            this.kabupatenSelect.addEventListener('change', () => {
                                this.loadKecamatan();
                                this.clearDesa();
                            });
                        }

                        if (this.kecamatanSelect) {
                            this.kecamatanSelect.addEventListener('change', () => {
                                this.loadDesa();
                            });
                        }

                        if (this.filterButton) {
                            this.filterButton.addEventListener('click', () => {
                                this.applyFilter();
                            });
                        }

                        if (this.clearButton) {
                            this.clearButton.addEventListener('click', () => {
                                this.clearFilter();
                            });
                        }
                    },

                    async loadKabupaten() {
                        if (!this.kabupatenSelect) return;

                        try {
                            const apiUrl =
                                '{{ $apiUrl ?: config('app.databaseGabunganUrl') . '/api/v1/wilayah/kabupaten' }}';
                            const response = await fetch(apiUrl, {
                                headers: this.apiHeaders
                            });

                            if (!response.ok) throw new Error('Failed to load kabupaten');

                            const data = await response.json();
                            this.populateSelect(this.kabupatenSelect, data.data, 'kode_kabupaten',
                                'nama_kabupaten');
                        } catch (error) {
                            console.error('Error loading kabupaten:', error);
                        }
                    },

                    async loadKecamatan() {
                        if (!this.kecamatanSelect || !this.kabupatenSelect) return;

                        const kabupatenCode = this.kabupatenSelect.value;
                        if (!kabupatenCode) {
                            this.clearSelect(this.kecamatanSelect);
                            this.clearDesa();
                            return;
                        }

                        try {
                            const apiUrl =
                                '{{ $apiUrl ?: config('app.databaseGabunganUrl') . '/api/v1/wilayah/kecamatan' }}';
                            const url = new URL(apiUrl);
                            url.searchParams.set('kode_kabupaten', kabupatenCode);

                            const response = await fetch(url.href, {
                                headers: this.apiHeaders
                            });

                            if (!response.ok) throw new Error('Failed to load kecamatan');

                            const data = await response.json();
                            this.populateSelect(this.kecamatanSelect, data.data, 'kode_kecamatan',
                                'nama_kecamatan');
                        } catch (error) {
                            console.error('Error loading kecamatan:', error);
                        }
                    },

                    async loadDesa() {
                        if (!this.desaSelect || !this.kecamatanSelect) return;

                        const kecamatanCode = this.kecamatanSelect.value;
                        if (!kecamatanCode) {
                            this.clearSelect(this.desaSelect);
                            return;
                        }

                        try {
                            const apiUrl =
                                '{{ $apiUrl ?: config('app.databaseGabunganUrl') . '/api/v1/wilayah/desa' }}';
                            const url = new URL(apiUrl);
                            url.searchParams.set('kode_kecamatan', kecamatanCode);

                            const response = await fetch(url.href, {
                                headers: this.apiHeaders
                            });

                            if (!response.ok) throw new Error('Failed to load desa');

                            const data = await response.json();
                            this.populateSelect(this.desaSelect, data.data, 'kode_desa', 'nama_desa');
                        } catch (error) {
                            console.error('Error loading desa:', error);
                        }
                    },

                    populateSelect(selectElement, data, valueField, textField) {
                        // Clear existing options except the first one
                        const firstOption = selectElement.options[0];
                        selectElement.innerHTML = '';
                        selectElement.appendChild(firstOption);

                        // Add new options
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item[valueField] || item.attributes[valueField];
                            option.textContent = item[textField] || item.attributes[textField];
                            selectElement.appendChild(option);
                        });
                    },

                    clearSelect(selectElement) {
                        if (!selectElement) return;

                        const firstOption = selectElement.options[0];
                        selectElement.innerHTML = '';
                        selectElement.appendChild(firstOption);
                    },

                    clearDesa() {
                        this.clearSelect(this.desaSelect);
                    },

                    applyFilter() {
                        const filterData = {
                            kabupaten: this.kabupatenSelect?.value || '',
                            kecamatan: this.kecamatanSelect?.value || '',
                            desa: this.desaSelect?.value || ''
                        };

                        @if ($onFilterChange)
                            {{ $onFilterChange }}(filterData);
                        @else
                            // Default behavior - trigger custom event
                            document.dispatchEvent(new CustomEvent('wilayahFilterChange', {
                                detail: filterData
                            }));
                        @endif
                    },

                    clearFilter() {
                        if (this.kabupatenSelect) this.kabupatenSelect.value = '';
                        if (this.kecamatanSelect) this.clearSelect(this.kecamatanSelect);
                        if (this.desaSelect) this.clearSelect(this.desaSelect);

                        // Trigger filter change with empty values
                        this.applyFilter();
                    },

                    getFilterValues() {
                        return {
                            kabupaten: this.kabupatenSelect?.value || '',
                            kecamatan: this.kecamatanSelect?.value || '',
                            desa: this.desaSelect?.value || ''
                        };
                    },

                    setFilterValues(values) {
                        if (values.kabupaten && this.kabupatenSelect) {
                            this.kabupatenSelect.value = values.kabupaten;
                            this.loadKecamatan().then(() => {
                                if (values.kecamatan && this.kecamatanSelect) {
                                    this.kecamatanSelect.value = values.kecamatan;
                                    this.loadDesa().then(() => {
                                        if (values.desa && this.desaSelect) {
                                            this.desaSelect.value = values.desa;
                                        }
                                    });
                                }
                            });
                        }
                    }
                };

                // Initialize the filter
                wilayahFilter.init();

                // Make it globally accessible
                window.wilayahFilter = wilayahFilter;
            });
        </script>
    @endpush
@endonce
