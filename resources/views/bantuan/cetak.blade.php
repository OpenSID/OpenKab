@extends('layouts.cetak.index')

@section('title', 'Data Bantuan')

@section('content')
    @include('partials.breadcrumbs')
    <div x-data="{
        data: {},
        async retrievePosts() {
            try {
                const headers = @include('layouts.components.header_bearer_api_gabungan');
                var create_url = new URL({{ json_encode(config('app.databaseGabunganUrl')) }} + '/api/v1/bantuan/cetak');
    
                create_url.searchParams.set('kode_kecamatan', {{ json_encode(session('kecamatan.kode_kecamatan') ?? '') }});
                create_url.searchParams.set('config_desa', {{ json_encode(session('desa.id') ?? '') }});
    
                @foreach ($filter as $key => $value)
                    create_url.searchParams.append('filter[{{ $key }}]', {{ json_encode($value) }}); @endforeach
    
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
                    <th class="padat">Nama Program</th>
                    <th class="padat">Asal Dana</th>
                    <th class="padat">Jumlah Peserta</th>
                    <th class="padat">Masa Berlaku</th>
                    <th class="padat">Sasaran</th>
                    <th class="padat">Status</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(value, index) in data">
                    <tr>
                        <td class="padat" x-text="index+1"></td>
                        <td x-text="value.attributes.nama_desa"></td>
                        <td x-text="value.attributes.nama"></td>
                        <td x-text="value.attributes.asaldana"></td>
                        <td x-text="value.attributes.jumlah_peserta"></td>
                        <td x-text="value.attributes.sdate + ' s.d. ' + value.attributes.edate"></td>
                        <td x-text="value.attributes.nama_sasaran"></td>
                        <td x-text="value.attributes.nama_status"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
@stop
