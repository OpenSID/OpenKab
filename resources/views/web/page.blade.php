@extends('layouts.web')

@section('content')
<!-- Header Start -->
<div class="container-fluid header-halaman bg-white p-0">
    <div class="row g-0 align-items-center">
        <div class="col-md-12 p-5 mt-lg-5">
            <h1 class="display-5 animated fadeIn mb-4">{{ $object->title }}</h1>
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
<div class="container-fluid bg-primary mb-5 wow fadeIn" data-wow-delay="0.1s" style="padding: 10px;"></div>



<!-- Konten Halaman -->
<div class="container-fluid">
        <div class="bg-light rounded p-3">
            <div class="bg-white rounded p-4" style="border: 1px dashed rgba(0, 185, 142, .3)">
                <div class="row g-5 align-items-center">

                    <div class="col-lg-12 wow fadeIn" data-wow-delay="0.5s">
                        {!! $object->content !!}
                    </div>
                </div>
            </div>

    </div>
</div>
<!-- Konten Halaman -->
@endsection
