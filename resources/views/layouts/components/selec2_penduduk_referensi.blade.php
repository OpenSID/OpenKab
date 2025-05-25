@push('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            let header = @include('layouts.components.header_bearer_api_gabungan');
            $('#status').select2({
                theme: 'bootstrap4',
                data: {!! json_encode(App\Models\Enums\StatusPendudukEnum::select2()) !!},
                allowClear: true,
                placeholder: "Pilih Status",
                width: '100%',
            })

            $('#status-dasar').select2({
                theme: 'bootstrap4',
                data: {!! json_encode(App\Models\Enums\StatusDasarEnum::select2()) !!},
                allowClear: true,
                placeholder: "Pilih Status Dasar",
                width: '100%',
            })

            $('#sex').select2({
                theme: 'bootstrap4',
                data: {!! json_encode(App\Models\Enums\JenisKelaminEnum::select2()) !!},
                allowClear: true,
                placeholder: "Pilih Jenis Kelamin",
                width: '100%',
            })
        })
    </script>
@endpush
