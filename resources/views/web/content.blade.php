@extends('layouts.app')

@include('partials.websections', [
'title' => getTitle($title = $object->title),
'description' => getDescription($description = $object->description),
'image' => getImage()
])

@section('content')
    @include('partials.webhero', ['class' => 'has-text-centered'])
    @include('partials.webcontent', ['content' => $object->content])
@endsection
