@extends('layouts.cetak.index')

@section('title', 'Data Kesehatan')

@section('content')
    @include('partials.breadcrumbs')
    <div x-data="{
        data: {},
        async retrievePosts() {
            try {
                const headers = @include('layouts.components.header_bearer_api_gabungan');
                var create_url = new URL({{ json_encode(config('app.databaseGabunganUrl')) }} + '/api/v1/data/kesehatan');
    
                // Get current URL parameters and add them to create_url
                const currentUrl = new URL(window.location.href);
                const urlParams = currentUrl.searchParams;
    
                // Add all search parameters from current URL
                for (const [key, value] of urlParams.entries()) {
                    if (value && value !== '' && value !== 'null') {
                        create_url.searchParams.set(key, value);
                    }
                }
    
                const response = await fetch(create_url.href, {
                    method: 'GET',
                    headers: headers
                });
    
                if (!response.ok) throw new Error('Gagal mengambil data');
    
                const result = await response.json();
                this.data = result.data;
    
                await $nextTick();
                window.print();
            } catch (error) {
                console.error('Terjadi kesalahan:', error);
                alert('Terjadi kesalahan saat mengambil data.');
            }
        }
    }" x-init="retrievePosts">
        <table class="border thick" id="tabel-penduduk">
            <thead>
                <tr class="border thick">
                    <th class="padat">No</th>
                    <th class="padat">Nama {{ config('app.sebutanDesa') }}</th>
                    <th class="padat">NIK</th>
                    <th class="padat">Golongan Darah</th>
                    <th class="padat">Cacat</th>
                    <th class="padat">Sakit Menahun</th>
                    <th class="padat">Akseptor KB</th>
                    <th class="padat">Status Kehamilan</th>
                    <th class="padat">Asuransi Kesehatan</th>
                    <th class="padat">Status Gizi Balita</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(value, index) in data">
                    <tr>
                        <td class="padat" x-text="index+1"></td>
                        <td x-text="value.attributes.nama_desa"></td>
                        <td x-text="value.attributes.nik"></td>
                        <td x-text="value.attributes.golongan_darah"></td>
                        <td x-text="value.attributes.cacat"></td>
                        <td x-text="value.attributes.sakit_menahun"></td>
                        <td x-text="value.attributes.kb"></td>
                        <td x-text="value.attributes.hamil"></td>
                        <td x-text="value.attributes.asuransi"></td>
                        <td x-text="value.attributes.status_gizi"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
@stop
