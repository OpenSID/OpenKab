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
        <!-- Konten Halaman -->
        <div class="container-fluid">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                @forelse ($articles as $article)
                    <div class="col">
                        <div class="card shadow-sm">
                            @if ($article->thumbnail)
                                <img src="{{ Storage::url($article->thumbnail) }}" width="100%" height="225"
                                    class="card-img-top object-fit-cover" alt="{{ $article->title }}">
                            @else
                                <svg class="bd-placeholder-img card-img-top" width="100%" height="225"
                                    xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Thumbnail"
                                    preserveAspectRatio="xMidYMid slice" focusable="false">
                                    <title>Placeholder</title>
                                    <rect width="100%" height="100%" fill="#55595c" /><text x="50%" y="50%"
                                        fill="#eceeef" dy=".3em">Thumbnail</text>
                                </svg>
                            @endif
                            <div class="card-body">
                                <p class="card-text">{!! Str::limit($article->content, 100) !!}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="{{ route('article', $article->slug) }}"
                                            class="text-decoration-none">Selengkapnya</a>
                                    </div>
                                    <small class="text-muted">Dibuat pada
                                        {{ $article->created_at->diffForHumans() }}</small>
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
        </div>
        <!-- Konten Halaman -->
    </div>
@endsection
