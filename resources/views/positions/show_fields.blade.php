<!-- Name Field -->
<div class="col-sm-12">
    {!! Html::label('name', 'Name:') !!}
    <p>{{ $position->name }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Html::label('description', 'Description:') !!}
    <p>{{ $position->description }}</p>
</div>

<!-- Parent Id Field -->
<div class="col-sm-12">
    {!! Html::label('parent_id', 'Parent Id:') !!}
    <p>{{ $position->parent_id }}</p>
</div>
