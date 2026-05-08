@extends('layouts.index')

@section('content_header')
    <h1>Tambah Artikel</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            @include('common.errors')
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('articles.index') }}" class="btn btn-primary btn-sm"><i
                            class="fas fa-arrow-circle-left"></i></i>&ensp;Kembali ke Daftar Artikel</a>
                </div>
                {!! Html::form('POST', route('articles.store'))->attribute('enctype', 'multipart/form-data')->open() !!}

                <div class="card-body">

                    <div>
                        @include('articles.fields')
                    </div>

                </div>

                {!! Html::form()->close() !!}

            </div>
        </div>
    </div>
@endsection

@push('js')
    @if(count($categories) == 0)
    <script nonce="{{ csp_nonce() }}">
        console.log('Category check: empty');
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal === 'undefined') {
                console.error('Swal is not defined');
                return;
            }
            Swal.fire({
                icon: 'info',
                title: 'Kategori Belum Tersedia',
                text: 'Kategori artikel wajib diisi. Buat kategori artikel terlebih dahulu sebelum menambahkan artikel.',
                showCancelButton: true,
                confirmButtonText: 'Buat Kategori',
                cancelButtonText: 'Nanti Saja'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('categories.create') }}";
                }
            });
        });
    </script>
    @endif
@endpush
