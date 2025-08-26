<!-- Name Field -->
<div class="col-sm-12">
    {!! Html::label('name', 'Name:') !!}
    <p>{{ $department->name }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Html::label('description', 'Description:') !!}
    <p>{{ $department->description }}</p>
</div>

<!-- Parent Id Field -->
<div class="col-sm-12">
    {!! Html::label('parent_id', 'Parent Id:') !!}
    <p>{{ $department->parent_id }}</p>
</div>
