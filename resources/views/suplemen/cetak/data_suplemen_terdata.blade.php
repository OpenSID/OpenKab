@foreach ($terdata as $key => $item)
    <tr>
        <td class="padat">{{ $key + 1 }}</td>
        <td class="textx">{{ $item['terdata_info'] }}</td>
        <td class="textx">{{ $item['terdata_plus'] }}</td>
        <td>{{ $item['terdata_nama'] }}</td>
        <td>{{ $item['tempatlahir'] }}</td>
        <td class="textx">{{ $item['tanggallahir'] }}</td>
        <td>{{ App\Models\Enums\JenisKelaminEnum::getLabel($item['sex']) }}</td>
        <td>{{ 'RT/RW ' . $item['rt'] . '/' . $item['rw'] . ' - ' . strtoupper($item['dusun']) }}</td>
        <td>{{ $item['keterangan'] }}</td>
    </tr>
    @include('suplemen.cetak.suplemen_terdata_detail')
    
@endforeach