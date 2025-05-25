<div class="box-body">
    <h5><b>Rincian Tipe Lokasi</b></h5>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover tabel-rincian">
            <tbody>
                <tr>
                    <td width="20%">Nama</td>
                    <td width="1%">:</td>
                    <td>{{ strtoupper($point->nama) }}</td>
                </tr>
                <tr>
                    <td>Aktif</td>
                    <td>:</td>
                    <td>{{ $status[$point->enabled] }}</td>
                </tr>
                <tr>
                    <td>Simbol</td>
                    <td>:</td>
                    {{-- <td><img src="{{ $point->path_simbol}}" /></td> --}}
                    <td><img src="{{asset('assets/img/gis/lokasi/point/'.$point->simbol)}}" /></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
