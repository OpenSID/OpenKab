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
                            <li class="breadcrumb-item"><a href="#">Halaman</a></li>
                            <li class="breadcrumb-item text-body active" aria-current="page">{{ $object->title }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <!-- Header End -->
        <!-- Konten Halaman -->
        <div class="container-fluid">
            <div class="col-lg-12 wow fadeIn" data-wow-delay="0.5s">
                {!! $object->content !!}
            </div>
        </div>
        <!-- Konten Halaman -->
    </div>
@endsection
