<!-- Slug Field -->
<div class="col-sm-12">
    {!! Html::label('slug', 'Slug:') !!}
    <p>{{ $category->slug }}</p>
</div>

<!-- Title Field -->
<div class="col-sm-12">
    {!! Html::label('title', 'Title:') !!}
    <p>{{ $category->title }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Html::label('description', 'Description:') !!}
    <p>{{ $category->description }}</p>
</div>

