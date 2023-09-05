<div class="row">
    <div class="col-md-12">
        <div id="collapse-filter" class="collapse">
            <div class="row">
                <div class="col-sm">
                    <div class="form-group">
                        <label>Tanggal Aktifitas</label>
                        <div class="input-group input-daterange">
                            <input type="text" class="form-control" data-date-format="dd-mm-yyyy" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }}" name="start" >
                            <div class="input-group-addon">sd</div>
                            <input type="text" class="form-control" data-date-format="dd-mm-yyyy" value="{{ \Carbon\Carbon::now()->endOfMonth()->format('d-m-Y') }}" name="end">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm">
                    <div class="form-group">
                        <label>Pengguna</label>
                        {{ Form::select('causer_id', $pengguna, null, ['class' => 'select2 form-control', 'style' => 'width:100%']) }}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="btn-group btn-group-sm btn-block">
                                <button type="button" id="reset" class="btn btn-secondary"><span
                                        class="fas fa-ban"></span></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="btn-group btn-group-sm btn-block">
                                <button type="button" id="filter" class="btn btn-primary"><span
                                        class="fas fa-search"></span></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="mt-0">
        </div>
    </div>
</div>
