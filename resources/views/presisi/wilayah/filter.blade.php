<div class="row" >
    <div class="col-md-12" >
        <div class="card card-primary card-outline rounded-0 elevation-0 border">
            <div class="card-header" style=" background-color: lightblue!important;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <p><b> Pilih Kecamatan:</b></p>
                        </div>
                        <div class="row">
                            <select name="Filter Kecamatan" id="filter_kecamatan" required class="form-control" title="Pilih Kecamatan">
                                <option value="">All</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <p><b> Pilih Desa:</b></p>
                        </div>
                        <div class="row">
                            <select name="Filter Desa" id="filter_desa" required class="form-control" title="Pilih Desa">
                                <option value="">All</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-end pt-1">
                    <div class="col-md-4 pull-right text-right">
                        <button id="bt_clear_filter" class="btn btn-sm btn-danger pull-right" style="display:none;">HAPUS FILTER</button>
                        <button id="bt_filter" class="btn btn-sm btn-secondary pull-right">TAMPILKAN</button>
                    </div>
                </div>

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