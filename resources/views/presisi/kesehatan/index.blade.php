@extends('layouts.presisi.index')

@section('content_header')
    <div class="box-header">
        <div class="row">
            <div class="col-md-4">
                <h2>e - Stunting</h2>
            </div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <select name="kuartal" id="kuartal" required class="form-control input-sm" title="Pilih salah satu">
                                @foreach (kuartal2() as $item)
                                    <option value="{{ $item['ke'] }}" {{ $item['ke'] == $data['kuartal'] ? 'selected' : '' }}>Kuartal ke {{ $item['ke'] }}
                                        ({{ $item['bulan'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="tahun" id="tahun" required class="form-control input-sm" title="Pilih salah satu">
                            <option value="null">Tahun</option>    
                            @foreach ($data['dataTahun'] as $item)
                                    <option value="{{ $item->tahun }}">{{ $item->tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="id" id="id" required class="form-control input-sm" title="Pilih salah satu">
                                <option value="null">Posyandu</option>
                                @foreach ($data['posyandu'] as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data['id'] ? 'selected' : '' }}>
                                        {{ $item->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-social btn-info" id="cari">
                            <i class="fa fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    @include('presisi.kesehatan.widget')
    <div class="row">
        <div class="col-12 wow fadeInUp" data-wow-delay="0.3s">
            <div class="info-box shadow-none rounded-0">
                <div class="info-box-content">
                    @include('presisi.kesehatan.chart_stunting_umur')
                    @include('presisi.kesehatan.chart_stunting_posyandu')
                    @include('presisi.kesehatan.scorecard')
                </div>
            </div>
        </div>

    </div>
@endsection
@push('js')
    <script>
        $('#cari').click(function() {
            let kuartal = $('#kuartal option:selected').val();
            let tahun = $('#tahun option:selected').val();
            let posyandu = $('#id option:selected').val();
            window.location.href = "{{ url('presisi/kesehatan/') }}/" + kuartal + "/" +
                tahun + "/" + posyandu;
        });
    </script>
@endpush
