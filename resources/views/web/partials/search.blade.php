<div class="row g-2">
    <div class="col-md-10">
        <div class="row g-2">
            <div class="col-md-6">
                {!! Form::select('search_kecamatan', $listKecamatan, null, ['class' => 'form-select border-0 py-3']) !!}
            </div>
            <div class="col-md-6">
                {!! Form::select('search_desa', $listDesa, null, ['class' => 'form-select border-0 py-3']) !!}
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <button class="btn btn-dark border-0 w-100 py-3">Tampilkan</button>
    </div>
</div>

@push('scripts')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function (event) {
            $('select[name=search_desa]').find('optgroup').hide()
            $('select[name=search_kecamatan]').change(function(){
                let _val = $(this).val()
                $('select[name=search_desa]').find('optgroup').hide()
                $('select[name=search_desa]').find('optgroup[label="'+_val+'"]').show()
                $('select[name=search_desa]').find('optgroup[label="'+_val+'"]').find('option:first').prop('selected', 1)
                $('select[name=search_desa]').trigger('change')
            })
        })
    </script>
@endpush
