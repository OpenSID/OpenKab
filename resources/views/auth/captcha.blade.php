@unless(app()->environment('testing'))
<div class="form-group{{ $errors->has('captcha') ? ' has-error' : '' }}">
    <div class="col-auto">
        <div class="captcha">
            <span>{!! captcha_img('mini') !!}</span>
            <button type="button" class="btn btn-success btn-refresh"><i class="fa fa-refresh"></i></button>
        </div>
        <input id="captcha" type="text" class="form-control" required placeholder="Masukkan Kode Verifikasi"
            name="captcha">
        @if ($errors->has('captcha'))
        <span class="help-block">
            <strong>{{ $errors->first('captcha') }}</strong>
        </span>
        @endif
    </div>
</div>

@section('js')
<script nonce="{{ csp_nonce() }}">
    document.addEventListener("DOMContentLoaded", function (event) {
        $(".btn-refresh").click(function() {
            $(".captcha span img").attr('src','/captcha/mini?'+ Date.now());            
        });
    })
</script>
@endsection
@endunless