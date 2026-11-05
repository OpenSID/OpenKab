@extends('layouts.index')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')

    <x-dataspasial-status :result="$result">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Fasilitas</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-0">
                            Kerangka menu. Alur impor lengkap (template, validasi per baris,
                            ringkasan ditolak dan konflik) menjadi lingkup fitur turunan dan
                            dibangun di atas service <code>DataSpasialApiService</code> tanpa
                            mengubah kontraknya.
                        </p>

                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Rujukan</th>
                                        <th>Nama</th>
                                        <th>Sektor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($result->data as $item)
                                        @php $attributes = $item['attributes'] ?? []; @endphp
                                        <tr>
                                            <td>{{ $attributes['rujukan_fasilitas'] ?? $item['id'] ?? '-' }}</td>
                                            <td>{{ $attributes['nama'] ?? '-' }}</td>
                                            <td>{{ $attributes['sektor'] ?? $attributes['sektor_id'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-dataspasial-status>
@stop
