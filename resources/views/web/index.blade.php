@extends('layouts.web')

@section('content')
<!-- Header Start -->
<div class="container-fluid header bg-white p-0">
    <div class="row g-0 align-items-center flex-column-reverse flex-md-row">
        @include('web.partials.slider')
    </div>
</div>
<!-- Header End -->


<!-- Search Start -->
<div class="container-fluid bg-primary mb-5 wow fadeIn" data-wow-delay="0.1s" style="padding: 35px;">
    @include('web.partials.search')
</div>
<!-- Search End -->


<!-- Category Start -->
<div class="container-xxl py-5">
    @include('web.partials.statistik')
</div>
<!-- Category End -->

<!-- Property List Start -->
<div class="container-xxl py-5">
    @include('web.partials.property')
</div>
<!-- Property List End -->

<!-- Team Start -->
<div class="container-xxl py-5">
    @include('web.partials.team')
</div>
<!-- Team End -->
@endsection
