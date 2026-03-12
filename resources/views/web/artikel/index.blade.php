@extends('layouts.web')

@section('content')
    <div class="ps-5">
        <!-- Header Start -->
        <div class="container-fluid header-halaman bg-white mb-3">
            <div class="row g-0 align-items-center">
                <div class="col-md-12 mt-lg-5">
                    <h1 class="display-5 animated fadeIn mb-4">{{ $title }}</h1>
                    <nav aria-label="breadcrumb animated fadeIn">
                        <ol class="breadcrumb text-uppercase">
                            <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                            <li class="breadcrumb-item"><a href="#">Halaman</a></li>
                            <li class="breadcrumb-item text-body active" aria-current="page">{{ $title }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <!-- Header End -->

        <!-- Search and Filter -->
        <div class="container-fluid mb-4">
            <form action="{{ route('web.artikel.index') }}" method="GET" class="row g-3 px-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Cari artikel..."
                        value="{{ request('search') }}">
                </div>
                <!-- Optional: Dropdown kategori bisa ditambahkan jika API kategori publik tersedia -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
            </form>
        </div>

        <!-- Konten Halaman -->
        <div class="container-fluid">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 mb-5 px-3">
                @forelse ($articles as $article)
                    <div class="col">
                        <div class="card shadow-sm h-100">
                            @if (isset($article->gambar) && !empty($article->gambar))
                                <img src="{{ $article->gambar }}" width="100%" height="225" class="card-img-top object-fit-cover"
                                    alt="{{ $article->judul ?? '' }}">
                            @else
                                <svg class="bd-placeholder-img card-img-top" width="100%" height="225"
                                    xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Thumbnail"
                                    preserveAspectRatio="xMidYMid slice" focusable="false">
                                    <title>Placeholder</title>
                                    <rect width="100%" height="100%" fill="#55595c" /><text x="50%" y="50%" fill="#eceeef"
                                        dy=".3em">Thumbnail</text>
                                </svg>
                            @endif
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">{{ $article->judul ?? '' }}</h5>
                                <div class="mb-2">
                                    <span class="badge bg-primary">{{ $article->kategori_nama ?? 'Kategori' }}</span>
                                </div>
                                <div class="card-text flex-grow-1">
                                    {!! Str::words(strip_tags($article->isi ?? ''), 20, '...') !!}
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <a href="{{ route('web.artikel.show', $article->id ?? '') }}"
                                            class="text-decoration-none btn btn-sm btn-outline-primary">Selengkapnya</a>
                                    </div>
                                    <small class="text-muted">
                                        {{ isset($article->tgl_upload) ? \Carbon\Carbon::parse($article->tgl_upload)->translatedFormat('d F Y') : '' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-lg-12">
                        <div class="alert alert-warning text-center">
                            <strong>Belum ada artikel yang dipublikasikan.</strong>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Mengingat ini adalah array/collection dari API (bukan standard Laravel paginator), 
                     UI pagination native agak tricky untuk di-render otomatis. 
                     Kita tampilkan tombol Muat Lebih Banyak dengan parameter halaman jika diperlukan -->
        </div>
        <!-- Konten Halaman -->
    </div>
@endsection