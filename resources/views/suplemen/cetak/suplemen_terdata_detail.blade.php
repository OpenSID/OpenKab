@if($suplemen['form_isian'])
<tr>
    <td colspan="9" class="cus-print">
        @foreach (json_decode($suplemen['form_isian']) as $kode)
            @php
                // Dekode JSON dari data_form_isian menjadi array
                $dataFormIsian = json_decode($item['data_form_isian'], true); // decode JSON ke array
                // Ambil nilai berdasarkan nama_kode pada data_form_isian
                $nilai = $dataFormIsian[$kode->nama_kode] ?? '';
            @endphp
            <div><strong>{{ $kode->label_kode }}:</strong> {{ $nilai }}</div>
        @endforeach
    </td>
</tr>

@push('css')
    <style nonce="{{ csp_nonce() }}" >
        .cus-print{
            width: 100%; padding-left: 20px;
        }
    </style>
@endpush
@endif