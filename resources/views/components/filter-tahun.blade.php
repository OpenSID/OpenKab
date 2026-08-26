<div class="col-sm-2">
    <select id="filter-tahun" data-testid="filter-tahun" class="form-control form-control-sm">
        @php
        $currentYear = date('Y');
        $startYear = $currentYear - 5;
        $selectedYear = $selectedYear ?? $currentYear;
        @endphp
        <option value="">Pilih tahun</option>
        @for($year = $currentYear; $year >= $startYear; $year--)
        <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
        @endfor
    </select>
</div>