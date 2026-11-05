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
                        <h3 class="card-title">Peta Gabungan</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-0">
                            Kerangka menu. Peta geospasial penuh menjadi lingkup fitur
                            turunan dan dibangun di atas service
                            <code>DataSpasialApiService</code> tanpa mengubah kontraknya.
                        </p>

                        <div class="mt-3">
                            <p class="mb-1"><strong>FeatureCollection</strong> diterima</p>
                            <p class="mb-0">
                                <span class="badge badge-info">{{ count($result->data) }}</span>
                                lapisan geospasial
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-dataspasial-status>
@stop
