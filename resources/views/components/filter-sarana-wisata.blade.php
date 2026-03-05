<div class="col-auto">
    <select id="filter-sarana-wisata" class="form-control form-control-sm">
        <option value="">Pilih Jenis Hiburan</option>
        @foreach(App\Enums\SaranaWisataEnum::getInstances() as $sarana)
        <option value="{{ $sarana->value }}">{{ $sarana->description }}</option>
        @endforeach
    </select>
</div>