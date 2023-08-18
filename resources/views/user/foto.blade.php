<div class="card card-widget">
    <div class="widget-user-header text-center">
        <img id="imageResult" src="{{ $user->foto ? Storage::url($user->foto) : asset('assets/img/avatar.png') }}"
            alt="Foto" width="200px">
    </div>
    <div class="card-footer">
        <div class="input-group mb-3 px-2 py-2 bg-white shadow-sm">
            <input id="upload" name="foto" type="file" accept="image/*" class="form-control border-0 fade">
            <div class="input-group-append col-12">
                <label for="upload" class="btn  col-12 btn-primary m-0 rounded-pill"> <i
                        class="fa fa-cloud-upload mr-2 text-muted"></i><small
                        class="text-uppercase text-white font-weight-bold text-muted">Ganti Foto</small></label>
            </div>
            @error('foto')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@push('js')
    <script type="text/javascript">
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#imageResult')
                    .attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    $(function () {
        $('#upload').on('change', function () {
            readURL(this);
        });
    });
    </script>
@endpush
