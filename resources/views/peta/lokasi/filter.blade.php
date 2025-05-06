<div class="row">
    <div class="col-sm-2">
        <select id="status" class="form-control input-sm select2">
            <option value="">Pilih Status</option>
            @foreach ($status as $key => $item)
                <option value="{{ $key }}">{{ $item }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-3">
        <select id="point" class="form-control input-sm select2">
            <option value="">Pilih Jenis</option>
            @foreach ($point as $item)
                <option data-children='{!! $item->children->toJson() !!}' value="{{ $item->id }}">{{ $item->nama }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-sm-3">
        <select id="subpoint" class="form-control input-sm select2">
            <option value="">Pilih Kategori</option>
            @foreach ($point as $item)
                {{-- <optgroup label="{{ $item->nama }}">
                    @foreach ($item->children as $child)
                        <option value="{{ $child->id }}">{{ $child->nama }}</option> --}}
                <optgroup label="{{ $item['nama'] }}">
                    @foreach ($item['children'] as $child)
                        <option value="{{ $child['id'] }}">{{ $child['nama'] }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </div>
</div>