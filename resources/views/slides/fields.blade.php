<!-- Title Field -->
<div class="form-group row">
    <div class="col-3">
    {!! Form::label('title', 'Title:') !!}
    </div>
    <div class="col-9">
    {!! Form::text('title', null, ['class' => 'form-control', 'required', 'maxlength' => 255, 'maxlength' => 255]) !!}
    </div>
</div>


<!-- Url Field -->
<div class="form-group row">
    <div class="col-3">
    {!! Form::label('url', 'Url:') !!}
    </div>
    <div class="col-9">
    {!! Form::text('url', null, ['class' => 'form-control', 'maxlength' => 255, 'maxlength' => 255]) !!}
    </div>
</div>


<!-- Thumbnail Field -->
<div class="form-group row">
    <div class="col-3">
    {!! Form::label('thumbnail', 'Thumbnail:') !!}
    </div>
    <div class="col-9">
    {!! Form::text('thumbnail', null, ['class' => 'form-control', 'maxlength' => 255, 'maxlength' => 255]) !!}
    </div>
</div>


<!-- Description Field -->
<div class="form-group row">
    <div class="col-3">
    {!! Form::label('description', 'Description:') !!}
    </div>
    <div class="col-9">
    {!! Form::textarea('description', null, ['class' => 'form-control', 'maxlength' => 65535, 'maxlength' => 65535]) !!}
    </div>
</div>


<!-- State Field -->
<div class="form-group row">
    <div class="form-check">
        {!! Form::hidden('state', 0, ['class' => 'form-check-input']) !!}
        {!! Form::checkbox('state', '1', null, ['class' => 'form-check-input']) !!}
        {!! Form::label('state', 'State', ['class' => 'form-check-label']) !!}
    </div>
</div>
