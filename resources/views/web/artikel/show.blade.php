@extends('layouts.web')

@section('content')
    <div class="ps-5">
        <!-- Header Start -->
        <div class="container-fluid header-halaman bg-white mb-3">
            <div class="row g-0 align-items-center">
                <div class="col-md-12 mt-lg-5">
                    <nav aria-label="breadcrumb animated fadeIn">
                        <ol class="breadcrumb text-uppercase">
                            <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('web.artikel.index') }}">Artikel</a></li>
                            <li class="breadcrumb-item text-body active" aria-current="page">
                                {{ Str::limit($object->judul ?? 'Detail Artikel', 30) }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <!-- Header End -->

        <!-- Konten Halaman -->
        <div class="container-fluid px-4 mb-5">
            <div class="row justify-content-center">
                <div class="col-lg-10 wow fadeIn" data-wow-delay="0.1s">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white pb-0 border-0 text-center">
                            <h2 class="mt-4 mb-3">{{ $object->judul ?? '' }}</h2>
                            <div class="d-flex justify-content-center gap-3 mb-4 text-muted">
                                <span><i
                                        class="fa fa-folder-open text-primary me-2"></i>{{ $object->kategori_nama ?? 'Kategori' }}</span>
                                <span><i
                                        class="fa fa-calendar-alt text-primary me-2"></i>{{ isset($object->tgl_upload) ? \Carbon\Carbon::parse($object->tgl_upload)->translatedFormat('d F Y') : '' }}</span>
                            </div>
                        </div>

                        @if (isset($object->gambar) && !empty($object->gambar))
                            <img src="{{ $object->gambar }}" class="card-img-top img-fluid px-4 mb-3 rounded"
                                alt="{{ $object->judul ?? '' }}" style="max-height: 500px; object-fit: contain;">
                        @endif

                        <div class="card-body p-4 p-md-5">
                            <div class="artikel-content">
                                {!! $object->isi ?? '' !!}
                            </div>
                        </div>

                        <div class="card-footer bg-white text-center py-4 border-top">
                            <a href="{{ route('web.artikel.index') }}" class="btn btn-outline-primary px-4"><i
                                    class="fa fa-arrow-left me-2"></i>Kembali ke Daftar Artikel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Konten Halaman -->
    </div>
@endsection

@push('styles')
    <style>
        .artikel-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .artikel-content {
            line-height: 1.8;
            font-size: 1.05rem;
        }
    </style>
@endpush