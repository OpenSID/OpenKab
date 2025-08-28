<!-- Slug Field -->
<div class="col-sm-12">
    {!! Html::label('Slug:', 'slug') !!}
    <p>{{ $category->slug }}</p>
</div>

<!-- Title Field -->
<div class="col-sm-12">
    {!! Html::label('Judul:', 'title') !!}
    <p>{{ $category->title }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Html::label('Deskripsi:', 'description') !!}
    <p>{{ $category->description }}</p>
</div>
