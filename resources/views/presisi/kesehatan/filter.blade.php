<div class="col-md-4 mb-1">
    <div class="form-group">
        <select name="kuartal" id="kuartal" required class="form-control input-sm" title="Pilih salah satu">
            <option value="null">Kuartal</option>
            @foreach (kuartal2() as $item)
                <option value="{{ $item['ke'] }}" {{ $item['ke'] == $kuartalget ? 'selected' : '' }}>Kuartal ke
                    {{ $item['ke'] }}
                    ({{ $item['bulan'] }})
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="col-md-4 mb-1">
    <div class="form-group">
        <select name="tahun" id="tahun" required class="form-control input-sm" title="Pilih salah satu">
            <option value="null">Tahun</option>
            @foreach ($data['dataTahun'] as $item)
                <option value="{{ $item['tahun'] }}" @selected($item['tahun'] == $tahun)>{{ $item['tahun'] }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="col-md-4 mb-1">
    <div class="form-group">
        <select name="id" id="id" required class="form-control input-sm" title="Pilih salah satu">
            <option value="null">Posyandu</option>
            @foreach ($data['posyandu'] as $item)
                <option value="{{ $item['id'] }}" {{ $item['id'] == $id ? 'selected' : '' }}>
                    {{ $item['nama'] }}</option>
            @endforeach
        </select>
    </div>
</div>
