<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline rounded-0 elevation-0 border-0">
            <div class="card-header bg-primary rounded-0">
                <div class="row">
                    <div class="col-md-3">
                        <select name="Filter Kabupaten" id="filter_kabupaten" required class="form-control"
                            title="Pilih {{ config('app.sebutanKab') }}">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="Filter Kecamatan" id="filter_kecamatan" required class="form-control"
                            title="Pilih Kecamatan">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="Filter Desa" id="filter_desa" required class="form-control"
                            title="Pilih {{ config('app.sebutanDesa') }}">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="row">
                            <table>
                                <tr>
                                    <td>
                                        <button id="bt_clear_filter"
                                            class="btn btn-sm btn-danger pull-right wh-full">HAPUS FILTER</button>
                                    </td>
                                    <td>
                                        <button id="bt_filter"
                                            class="btn btn-sm btn-primary btn-dark-primary wh-full">TAMPILKAN</button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('components.wilayah_filter_js')
