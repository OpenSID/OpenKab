<!-- Category Id Field -->
<div class="col-sm-12">
    {!! Html::label('ID Kategori:', 'category_id') !!}
    <p>{{ $article->category_id }}</p>
</div>

<!-- Published At Field -->
<div class="col-sm-12">
    {!! Html::label('Tanggal Terbit:', 'published_at') !!}
    <p>{{ $article->published_at }}</p>
</div>

<!-- Slug Field -->
<div class="col-sm-12">
    {!! Html::label('Slug:', 'slug') !!}
    <p>{{ $article->slug }}</p>
</div>

<!-- Title Field -->
<div class="col-sm-12">
    {!! Html::label('Judul:', 'title') !!}
    <p>{{ $article->title }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Html::label('Deskripsi:', 'description') !!}
    <p>{{ $article->description }}</p>
</div>

<!-- Content Field -->
<div class="col-sm-12">
    {!! Html::label('Konten:', 'content') !!}
    <p>{{ $article->content }}</p>
</div>
