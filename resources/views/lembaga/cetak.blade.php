@extends('layouts.cetak.index')

@section('title', 'Data Lembaga')

@section('content')
    @include('partials.breadcrumbs')
    <div x-data="{
        data: {},
        loading: true,
        error: null,
    
        validateParam(key, value) {
            // Validate parameter values
            if (!value || value === '' || value === 'null' || value === 'undefined') {
                return false;
            }
    
            // Additional validation based on parameter type
            if (key === 'tahun_berdiri' && !/^\d{4}$/.test(value)) {
                return false;
            }
    
            return true;
        },
    
        async retrievePosts() {
            try {
                this.loading = true;
                this.error = null;
    
                const headers = @include('layouts.components.header_bearer_api_gabungan');
                var create_url = new URL({{ json_encode(config('app.databaseGabunganUrl')) }} + '/api/v1/lembaga');
    
                // Get current URL parameters
                const currentUrl = new URL(window.location.href);
                const urlParams = currentUrl.searchParams;
    
                // Add validated parameters from current URL
                for (const [key, value] of urlParams.entries()) {
                    if (this.validateParam(key, value)) {
                        create_url.searchParams.set(key, value);
                    }
                }
    
                const response = await fetch(create_url.href, {
                    method: 'GET',
                    headers: headers
                });
    
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
    
                const result = await response.json();
    
                if (!result.data) {
                    throw new Error('Data tidak ditemukan dalam response');
                }
    
                this.data = result.data;
                this.loading = false;
    
                console.log(`Data berhasil dimuat: ${result.data.length} record`);
    
                await $nextTick();
                window.print();
    
            } catch (error) {
                this.loading = false;
                this.error = error.message;
                console.error('Terjadi kesalahan:', error);
                alert(`Terjadi kesalahan saat mengambil data: ${error.message}`);
            }
        }
    }" x-init="retrievePosts">

        <!-- Loading indicator -->
        <div x-show="loading" class="text-center p-4">
            <i class="fa fa-spinner fa-spin"></i>
            Memuat data...
        </div>

        <!-- Error message -->
        <div x-show="error" class="alert alert-danger" x-text="error"></div>

        <!-- Data table -->
        <div x-show="!loading && !error">
            <table class="border thick" id="tabel-lembaga">
                <thead>
                    <tr class="border thick">
                        <th class="padat">No</th>
                        <th>Nama {{ config('app.sebutanDesa') }}</th>
                        <th>Kode Lembaga</th>
                        <th>Nama Lembaga</th>
                        <th>Ketua Lembaga</th>
                        <th>Kategori Lembaga</th>
                        <th>Jumlah Anggota Lembaga</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(value, index) in data">
                        <tr>
                            <td class="padat" x-text="index+1"></td>
                            <td x-text="value.attributes.nama_desa"></td>
                            <td x-text="value.attributes.kode || 'N/A'"></td>
                            <td x-text="value.attributes.nama || 'N/A'"></td>
                            <td x-text="value.attributes.nama_ketua || 'N/A'">
                            </td>
                            <td x-text="value.attributes.kategori || 'N/A'"></td>
                            <td x-text="value.attributes.anggota_count || 'N/A'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
@stop
