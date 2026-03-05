<div class="col-auto">
    <select id="filter-komoditas-wisata" class="form-control form-control-sm">
        <option value="">Pilih Lokasi/Tempat/Area Wisata</option>
        @foreach(App\Enums\KomoditasPotensiWisataEnum::getInstances() as $komoditas)
        <option value="{{ $komoditas->value }}">{{ $komoditas->description }}</option>
        @endforeach
    </select>
</div>