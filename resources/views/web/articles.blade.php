@extends('layouts.app')

@include('partials.websections', [
'title' => getTitle($title),
'description' => getDescription($description),
'image' => getImage()
])

@section('content')
    @include('partials.webhero')
    <section class="section">
        <div class="container">
            @include('partials.webarticles')
        </div>
    </section>
@endsection
