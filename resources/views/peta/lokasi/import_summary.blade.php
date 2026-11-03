@php
    $summary = Session::get('import_summary');
@endphp

@if ($summary)
    <div class="card card-outline card-info" data-testid="import-summary">
        <div class="card-header">
            <h3 class="card-title">Ringkasan Import</h3>
        </div>
        <div class="card-body">
            <p>
                Total baris: <strong>{{ $summary['total'] }}</strong> &middot;
                Berhasil: <strong class="text-success">{{ $summary['success'] }}</strong> &middot;
                Gagal: <strong class="text-danger">{{ $summary['failed'] }}</strong>
            </p>

            @if (! empty($summary['errors']))
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" data-testid="import-errors">
                        <thead>
                            <tr>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($summary['errors'] as $error)
                                <tr>
                                    <td>{{ $error }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endif
