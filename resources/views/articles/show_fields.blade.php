<!-- Category Id Field -->
<div class="col-sm-12">
    {!! Html::label('category_id', 'Category Id:') !!}
    <p>{{ $article->category_id }}</p>
</div>

<!-- Published At Field -->
<div class="col-sm-12">
    {!! Html::label('published_at', 'Published At:') !!}
    <p>{{ $article->published_at }}</p>
</div>

<!-- Slug Field -->
<div class="col-sm-12">
    {!! Html::label('slug', 'Slug:') !!}
    <p>{{ $article->slug }}</p>
</div>

<!-- Title Field -->
<div class="col-sm-12">
    {!! Html::label('title', 'Title:') !!}
    <p>{{ $article->title }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Html::label('description', 'Description:') !!}
    <p>{{ $article->description }}</p>
</div>

<!-- Content Field -->
<div class="col-sm-12">
    {!! Html::label('content', 'Content:') !!}
    <p>{{ $article->content }}</p>
</div>
