<div class="card card-widget">
    <div class="widget-user-header text-center">
        <img id="imageResult" src="{{ $page?->thumbnail ? Storage::url($page?->thumbnail) : asset('assets/img/no-image.png') }}"
            alt="Foto" width="200px">
    </div>
    <div class="card-footer">
        <div class="input-group mb-3 px-2 py-2 bg-white shadow-sm">
            <input id="upload" name="foto" type="file" accept="image/*" class="form-control border-0 fade">
            <div class="input-group-append col-12">
                <label for="upload" class="btn  col-12 btn-primary m-0 rounded-pill"> <i
                        class="fa fa-cloud-upload mr-2 text-muted"></i><small
                        class="text-uppercase text-white font-weight-bold text-muted">Gambar Utama</small></label>
            </div>
            @error('foto')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        @if ($page?->thumbnail)
        <div class="form-check">
            <input type="checkbox" name="remove_thumbnail" id="remove_thumbnail" class="form-check-input">
            <label for="remove_thumbnail" class="form-check-label">Hapus gambar</label>
        </div>
        @endif

    </div>
</div>

@push('js')
    <script nonce="{{ csp_nonce() }}" type="text/javascript">
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

    document.addEventListener("DOMContentLoaded", function(event) {
        $('#upload').on('change', function () {
            readURL(this);
        });
    });
    </script>
@endpush
