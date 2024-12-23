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
                <table class="border thick" style="width: 100%;">
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
