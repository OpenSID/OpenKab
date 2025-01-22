<div class="row" x-data="group()" x-init="retrieveData()">
    <div class="col-lg-12">
        <div class="">            
            <form id="bantuan-form">
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="col">
                        <div class="mb-4">
                            <label for="sasaran">Nama Grup </label>
                            <input type="text" class="form-control" name="name" x-model="dataGroup.name">
                        </div>
                    </div>

                    <div class="col">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <td width="30" rowspan="2"> </td> <td width="40" colspan="2" rowspan="2"
                                        class="text-center">No</td>
                                    <td rowspan="2" class="text-center">Nama Modul</td>
                                    <td colspan="4" class="text-center">Hak Akses</td>
                                </tr>
                                <tr>
                                    <td class="text-center">Baca</td>
                                    <td class="text-center">Tulis</td>
                                    <td class="text-center">Ubah</td>
                                    <td class="text-center">Hapus</td>
                                </tr>
                            </thead>

                            <template x-for="(value, index) in menu">
                                <tbody>
                                    <tr>
                                        <td><input type="checkbox" name="menu" x-model="value.selected"
                                                @input.debounce.100ms="selected(value)"></td>
                                        <td x-text="(index +1)" colspan="2" width=40 class="text-center"></td>
                                        <td x-text="value.text"></td>
                                        <td class="text-center">
                                            <input class="form-check-input" type="checkbox"
                                                :name="value.permission + '-read'"
                                                x-model="value[value.permission + '-read']">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input" type="checkbox"
                                                :name="value.permission + '-write'"
                                                x-model="value[value.permission + '-write']">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input" type="checkbox"
                                                :name="value.permission + '-edit'"
                                                x-model="value[value.permission + '-edit']">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input" type="checkbox"
                                                :name="value.permission + '-delete'"
                                                x-model="value[value.permission + '-delete']">
                                        </td>
                                    </tr>

                                    <template x-for="(submenu, index2) in value.submenu">
                                        <tr>
                                            <td><input type="checkbox" name="menu" x-model="submenu.selected"
                                                    @input.debounce.100ms="selected_sub(value)"></td>
                                            <td width=20></td>
                                            <td x-text="(index+1)+ '.' + (index2+1)" width=20 class="text-center"></td>
                                            <td x-text="submenu.text" class="ps-5"></td>
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="checkbox"
                                                    :name="submenu.permission + '-read'"
                                                    x-model="submenu[submenu.permission + '-read']">
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="checkbox"
                                                    :name="submenu.permission + '-write'"
                                                    x-model="submenu[submenu.permission + '-write']">
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="checkbox"
                                                    :name="submenu.permission + '-edit'"
                                                    x-model="submenu[submenu.permission + '-edit']">
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="checkbox"
                                                    :name="submenu.permission + '-delete'"
                                                    x-model="submenu[submenu.permission + '-delete']">
                                            </td>
                                        <tr>
                                    </template>
                                </tbody>
                            </template>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <button type="button" id="reset" class="btn btn-danger btn-sm"><i class="fas fa-times"></i>&nbsp;
                        Batal</button>
                    <button x-on:click="simpan()" type="button" class="btn btn-primary btn-sm"><i
                            class="fas fa-save"></i>&nbsp;
                        Simpan </button>
                </div>
            </form>
        </div>
    </div>
</div>
@if(isset($id))
    @include('group.edit_js', ['id' => $id])
@else
    @include('group.create_js')
@endif