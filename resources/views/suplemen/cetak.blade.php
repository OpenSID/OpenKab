<table>
    <tbody>
        <tr>
            <td>
                @include('suplemen.cetak.header')
            </td>
        </tr>
        <tr>
            <td>
                <hr class="garis">
            </td>
        </tr>
        @include('suplemen.cetak.data_suplemen')
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <table class="border thick w-full">
                    <thead>
                        @include('suplemen.cetak.table')
                    </thead>
                    <tbody>
                        @include('suplemen.cetak.data_suplemen_terdata')
                        
                    </tbody>
                </table><br><br>
            </td>
        </tr>
    </tbody>
</table>
@push('css')
    <style nonce="{{ csp_nonce() }}" >
        .w-full{
            width: 100%;
        }
    </style>
@endpush