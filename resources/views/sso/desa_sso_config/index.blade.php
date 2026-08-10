@extends('layouts.index')

@section('title', 'Konfigurasi SSO Desa')

@section('content_header')
    <h1>Konfigurasi SSO Desa</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            @include('common.errors')
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('sso-config.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i>&ensp;Tambah Konfigurasi
                    </a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-striped" id="desa-sso-config" data-testid="datatable-sso-config">
                            <thead>
                                <tr>
                                    <th>Kode Desa</th>
                                    <th>URL OpenSID</th>
                                    <th>Status</th>
                                    <th class="padat">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($configs as $config)
                                    <tr>
                                        <td>{{ $config->desa_id }}</td>
                                        <td>{{ $config->opensid_url }}</td>
                                        <td>
                                            @if ($config->enabled)
                                                <span class="badge badge-success">Aktif</span>
                                            @else
                                                <span class="badge badge-secondary">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('sso-config.edit', $config) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            {!! Html::form('DELETE', route('sso-config.destroy', $config))
                                                ->id('form-delete-'.$config->id)
                                                ->style('display:inline') !!}
                                            <button type="button" class="btn btn-sm btn-danger btn-confirm-delete"
                                                data-form="#form-delete-{{ $config->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            {!! Html::form()->close() !!}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Belum ada konfigurasi. Gunakan
                                            <code>SSO_OPENSID_BASE_URL</code> sebagai fallback.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $configs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            document.querySelectorAll('.btn-confirm-delete').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (confirm('Yakin ingin menghapus konfigurasi ini?')) {
                        document.querySelector(btn.dataset.form).submit();
                    }
                });
            });
        });
    </script>
@endsection
