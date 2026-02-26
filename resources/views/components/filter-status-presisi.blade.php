<div class="col-sm-2">    
    <select class="form-control form-control-sm" id="filter-status-kelengkapan" name="filter[status-kelengkapan]">
        <option value="">Semua Status</option>
        @foreach(App\Enums\StatusKelengkapanPresisiEnum::getAll() as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>