@extends('layouts.cetak.index')

@section('title', 'Data Bantuan')

@section('content')
    <div x-data="{
        data: {},
        async retrievePosts() {
            let url = '{{ url('api/v1/bantuan/cetak') }}';
            let create_url = new URL(url);
            create_url.searchParams.set('kode_kecamatan', '{{ session('kecamatan.kode_kecamatan') ?? '' }}');
            create_url.searchParams.set('config_desa', '{{ session('desa.id') ?? '' }}');
            @foreach ($filter as $key => $value)
                create_url.searchParams.append('filter[{{ $key }}]', '{{ $value }}'); @endforeach
            const response = await (await fetch(create_url.href)).json();
            this.data = response.data
            await $nextTick();
            window.print();
        }
    }" x-init="retrievePosts">
        <table class="border thick" id="tabel-penduduk">
            <thead>
                <tr class="border thick">
                    <th class="padat">No</th>
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
