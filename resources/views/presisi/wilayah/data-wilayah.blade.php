<div id="summary_wilayah_block" class="row mt-2">
    <div class="col-lg-3 col-md-6">
        <div class="card p-3 bg-light-oren">
            <table>
                <tr>
                    <td rowspan="2" width="24%">
                        <h4 class="rounded-circle c-badge1 mr-2 text-center"><i class="fas fa-bullseye"></i></h4>
                    </td>
                    <td>
                        <h5>
                            <span id="summary-luas_wilayah">0</span> Ha
                        </h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p class="text-sm text-muted">Luas Wilayah</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card p-3 bg-light-green">
            <table>
                <tr>
                    <td rowspan="2" width="24%">
                        <h4 class="rounded-circle c-badge2 mr-2 text-center"><i class="fas fa-check-circle"></i></h4>
                    </td>
                    <td>
                        <h5><span id="summary-luas_pertanian">0</span> Ha</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p class="text-sm text-muted">Total Lahan Pertanian</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card p-3 bg-light-purple">
            <table>
                <tr>
                    <td rowspan="2" width="24%">
                        <h4 class="rounded-circle c-badge3 mr-2 text-center"><i class="fas fa-clock"></i></h4>
                    </td>
                    <td>
                        <h5><span id="summary-luas_perkebunan">0</span> Ha</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p class="text-sm text-muted">Total Lahan Perkebunan</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card p-3 bg-light-blue">
            <table>
                <tr>
                    <td rowspan="2" width="24%">
                        <h4 class="rounded-circle c-badge4 mr-2 text-center"><i class="fas fa-dollar-sign"></i></h4>
                    </td>
                    <td>
                        <h5><span id="summary-luas_hutan">0</span> Ha</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p class="text-sm text-muted">Total Lahan Kehutanan</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@push('js')
    <script nonce="{{ csp_nonce() }}" type="text/javascript">
        document.addEventListener("DOMContentLoaded", function(event) {
            $('#summary_wilayah_block').change(function(event) {
                const indexSearch = {
                    search: {
                        luas_wilayah: 1,
                        luas_pertanian: 1,
                        luas_perkebunan: 1,
                        luas_hutan: 1,
                        luas_peternakan: 1
                    },
                };
                let kabupaten = $('#filter_kabupaten').val();
                let kecamatan = $('#filter_kecamatan').val();
                let desa = $('#filter_desa').val();
                const urlSummary = new URL(
                    "{{ config('app.databaseGabunganUrl') . '/api/v1/data-summary' }}");
                urlSummary.searchParams.set('filter[kode_kabupaten]', kabupaten || '');
                urlSummary.searchParams.set('filter[kode_kecamatan]', kecamatan || '');
                urlSummary.searchParams.set('filter[kode_desa]', desa || '');
                $.get(urlSummary, indexSearch, function(result) {
                    for (let i in result.data) {
                        $(`#summary-${i}`).text(result.data[i]);
                    }
                }, 'json');
            });
            $('#summary_wilayah_block').trigger('change');
        });
    </script>
@endpush
