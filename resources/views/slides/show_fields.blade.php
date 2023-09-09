<!-- Title Field -->
<div class="col-sm-12">
    {!! Form::label('title', 'Title:') !!}
    <p>{{ $slide->title }}</p>
</div>

<!-- Url Field -->
<div class="col-sm-12">
    {!! Form::label('url', 'Url:') !!}
    <p>{{ $slide->url }}</p>
</div>

<!-- Thumbnail Field -->
<div class="col-sm-12">
    {!! Form::label('thumbnail', 'Thumbnail:') !!}
    <p>{{ $slide->thumbnail }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $slide->description }}</p>
</div>

<!-- State Field -->
<div class="col-sm-12">
    {!! Form::label('state', 'State:') !!}
    <p>{{ $slide->state }}</p>
</div>

