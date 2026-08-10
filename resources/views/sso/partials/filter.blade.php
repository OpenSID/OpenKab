<div class="row">
    <div class="col-md-12">
        <div id="collapse-filter" class="collapse">
            <div class="row">
                <div class="col-sm">
                    <div class="form-group">
                        <label>Rentang Waktu</label>
                        <div class="input-group">
                            <input type="text" class="form-control input-daterange" data-date-format="dd-mm-yyyy"
                                value="{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }} - {{ \Carbon\Carbon::now()->endOfMonth()->format('d-m-Y') }}"
                                name="start" data-testid="filter-date">
                        </div>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="form-group">
                        <label>Status</label>
                        {{ Html::select('status', $statuses)->class('select2 form-control width-100')->attributes(['data-testid' => 'filter-status']) }}
                    </div>
                </div>
                <div class="col-sm">
                    <div class="form-group">
                        <label>Administrator</label>
                        {{ Html::select('admin_id', $admins)->class('select2 form-control width-100')->attributes(['data-testid' => 'filter-admin']) }}
                    </div>
                </div>
                <div class="col-sm">
                    <div class="form-group">
                        <label>Kode Desa</label>
                        <input type="text" class="form-control" name="desa_id" data-testid="filter-desa"
                            placeholder="contoh: 5271010001">
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
                                <button type="button" id="filter" class="btn btn-primary" data-testid="bt-filter"><span
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
