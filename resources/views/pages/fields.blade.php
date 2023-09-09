<div class="row">
    <div class="col-9">
        <!-- Title Field -->
        <div class="form-group">
            {!! Form::label('title', 'Judul') !!}
            {!! Form::text('title', null, ['class' => 'form-control', 'required', 'maxlength' => 200]) !!}
        </div>

        <!-- Content Field -->
        <div class="form-group col-sm-12 col-lg-12">
            {!! Form::label('content', 'Isi') !!}
            {!! Form::textarea('content', null, ['class' => 'form-control editor', 'required', 'maxlength' => 65535]) !!}
        </div>
    </div>
    <div class="col-3">
        <!-- thumbnail Field -->
        <div class="form-group">
            @include('pages.foto')
        </div>

        <!-- Published At Field -->
        <div class="form-group ">
            {!! Form::label('published_at', 'Tanggal Posting') !!}
            {!! Form::text('published_at', $page?->local_published_at ?? null, ['class' => 'form-control datepicker','id'=>'published_at', 'required']) !!}
        </div>

        <!-- State Field -->
        <div class="form-group">
            <div class="form-check">
                {!! Form::hidden('state', 0, ['class' => 'form-check-input']) !!}
                {!! Form::checkbox('state', '1', $page?->state ? $page?->state : null, ['class' => 'form-check-input']) !!}
                {!! Form::label('state', 'Aktif', ['class' => 'form-check-label']) !!}
            </div>
        </div>

        <div>
            {!! Form::button('<i class="fas fa-times"></i> Batal', ['type' => 'reset', 'class' => 'btn btn-danger btn-sm'] )  !!}
            {!! Form::button('<i class="fas fa-save"></i> Simpan', ['type' => 'submit', 'class' => 'btn btn-primary btn-sm'] )  !!}
        </div>
    </div>
</div>

@include('partials.asset_datepicker')
@include('partials.asset_tinymce')
{!! JsValidator::formRequest('App\Http\Requests\CreatePageRequest') !!}
