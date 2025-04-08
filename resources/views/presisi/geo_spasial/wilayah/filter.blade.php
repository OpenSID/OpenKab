<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline rounded-0 elevation-0 border-0">
            <div class="card-header bg-primary rounded-0">
                <div class="row">
                    <div class="col-md-3" style="overflow:hidden">
                        <select name="Filter Kabupaten" id="filter_kabupaten" required class="form-control"
                            title="Pilih Kabupaten">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-3" style="overflow:hidden">
                        <select name="Filter Kecamatan" id="filter_kecamatan" required class="form-control"
                            title="Pilih Kecamatan">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-3" style="overflow:hidden">
                        <select name="Filter Desa" id="filter_desa" required class="form-control" title="Pilih Desa">
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
                <!-- <div class="row justify-content-end pt-1">
                    <div class="col-md-4 pull-right text-right">
                        <button id="bt_clear_filter" class="btn btn-sm btn-danger pull-right">HAPUS FILTER</button>
                        <button id="bt_filter" class="btn btn-sm btn-secondary pull-right">TAMPILKAN</button>
                    </div>
                </div> -->

            </div>

            <div class="card-body p-0">
                <div class="row">
                    <div class="col-md-12">
                        <div id="collapse-filter" class="collapse ">
                            <div class="row m-0">
                                <div class=" col-6">
                                    <div class="form-group">
                                        <label>Kecamatan</label>
                                        <select class="form-control " name="search_kecamatan"> </select>
                                    </div>

                                </div>

                                <div class=" col-6">
                                    <div class="form-group">
                                        <label>Desa</label>
                                        <select class="form-control " name="search_desa"> </select>
                                    </div>

                                </div>



                            </div>

                            <hr class="mt-0">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
