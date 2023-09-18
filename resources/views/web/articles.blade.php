@extends('layouts.app')

@include('web.partials.sections', [
'title' => getTitle($title),
'description' => getDescription($description),
'image' => ''// getImage()
])

@section('content')
    @include('web.partials.hero')
    <section class="section">
        <div class="container">
            @include('web.partials.articles')
        </div>
    </section>
@endsection
