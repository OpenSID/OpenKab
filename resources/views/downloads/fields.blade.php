<!-- Title Field -->
<div class="form-group row">
    {!! Html::label('title', 'Judul')->class('col-3') !!}
    <div class="col-9">
        {!! Html::text('title')->class('form-control')->attribute('required')->attribute('maxlength', 255) !!}
    </div>
</div>


<!-- Url Field -->
<div class="form-group row">
    {!! Html::label('url', 'Berkas')->class('col-3') !!}
    <div class="col-9">
        <div class="custom-file">
            <input type="file" name="download_file" class="custom-file-input"
                accept=".doc, .docx, .pdf, .zip, .xls, .xlsx" id="inputGroupFile01"
                aria-describedby="inputGroupFileAddon01" lang="id">
            <label class="custom-file-label" for="inputGroupFile01">Pilih berkas</label>
        </div>
        @if (isset($download))
            @if ($download->url)
                {{ link_to(Storage::url($download->url), 'berkas unduhan', ['class' => 'text-primary', 'target' => '_blank']) }}
            @endif
        @endif
    </div>
</div>

<!-- Description Field -->
<div class="form-group row">
    {!! Html::label('description', 'Keterangan')->class('col-3') !!}
    <div class="col-9">
        {!! Html::textarea('description')->class('form-control')->attribute('rows', 3)->attribute('maxlength', 65535) !!}
    </div>
</div>

<!-- State Field -->
<div class="form-group row">
    {!! Html::label('state', 'Status')->class('col-3') !!}
    <div class="col-9">
        {!! Html::select('state', $stateItem)->class('form-control select2')->attribute('required') !!}
    </div>
</div>

@push('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            $('.custom-file-input').on('change', function() {
                let files = $(this)[0].files;
                let name = [];
                for (var i = 0; i < files.length; i++) {
                    name.push(files[i].name);
                }
                $(".custom-file-label").html(name.join(', '));
            });
        })
    </script>
@endpush
