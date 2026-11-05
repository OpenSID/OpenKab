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
                        <h3 class="card-title">Ringkasan DataSpasial</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-0">
                            Kerangka menu. Agregat dan visualisasi penuh menjadi lingkup
                            fitur turunan dan dibangun di atas service
                            <code>DataSpasialApiService</code> tanpa mengubah kontraknya.
                        </p>

                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Sektor</th>
                                        <th>Periode</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($result->data as $item)
                                        @php $attributes = $item['attributes'] ?? $item; @endphp
                                        <tr>
                                            <td>{{ $attributes['sektor'] ?? $attributes['sektor_id'] ?? '-' }}</td>
                                            <td>{{ $attributes['periode'] ?? '-' }}</td>
                                            <td>{{ $attributes['total'] ?? '-' }}</td>
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
