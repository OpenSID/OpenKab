<div class="container-fluid">
    <header class="fs-4 text-center">Daftar Unduhan</header>
    <div class="container">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Unduhan</th>
                        <th>Jumlah Unduhan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unduhans as $index => $unduhan)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $unduhan->title }}</td>
                            <td>
                                @if($unduhan->url)
                                {!! Form::open(['route' => ['web.download.counter', $unduhan->id], 'method' => 'post', 'target' => '_blank']) !!}
                                {!! Form::button('<i class="fa fa-file"></i> Unduh', [
                                    'type' => 'submit',
                                    'class' => 'btn btn-info btn-sm',
                                ]) !!}
                                {!! Form::close() !!}
                                @else
                                 -
                                @endif
                            </td>
                            <td>{{ $unduhan->counter?->total ?? 0 }}</td>
                        </tr>
                    @empty
                        <td colspan="4">Belum ada unduhan yang tersedia</td>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
