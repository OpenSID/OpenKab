<h5><b>Kode Isian</b></h5>
<div class="table-responsive">
    <table class="table table-hover table-striped kode-isian">
        <thead>
            <tr style="font-weight: bold;">
                <td>#</td>
                <td>TIPE</td>
                <td>NAMA</td>
                <td>LABEL / JUDUL</td>
                <td>PLACEHOLDER</td>
                <td class="padat">HARUS DIISI</td>
                <td>KOLOM</td>
                <td>ATRIBUT</td>
                <td class="isian-pilihan">PILIHAN</td>
                <td>AKSI</td>
            </tr>
        </thead>
        <tbody id="dragable-form-utama">
            @php $jumlah_isian = 0; @endphp
            @if ($jumlah_isian == 0)
                <tr class="duplikasi ui-sortable-handle" id="gandakan-0" data-id="0">
                    <td><i class="fa fa-sort-alpha-desc" aria-hidden="true"></i></td>
                    <td>
                        <select class="form-control input-sm pilih_tipe" name="tipe_kode[]">
                            <option value="" selected>Pilihan Tipe</option>
                            @foreach ($attributes as $attr_key => $attr_value)
                                <option value="{{ $attr_key }}">{{ $attr_value }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="nama_kode[]" class="form-control input-sm isian" required placeholder="Masukkan Nama"></td>
                    <td><input type="text" name="label_kode[]" class="form-control input-sm isian" required placeholder="Masukkan Label"></td>
                    <td><input type="text" name="deskripsi_kode[]" class="form-control input-sm isian" placeholder="Masukkan Placeholder"></td>
                    <td class="text-center"><input class="isian-required" type="checkbox" value="1" name="required_kode[{{ $jumlah_isian }}]"></td>
                    <td class="text-center">
                        <select class="form-control input-sm" name="kolom[]">
                            <option value="" selected>Pilihan lebar</option>
                            @foreach (range(1, 12) as $item)
                                <option value="{{ $item }}">col-{{ $item }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <textarea class="form-control input-sm isian isian-atribut" name="atribut_kode[]" rows="5" placeholder="Masukkan Atribut"></textarea>
                    </td>
                    <td>
                        <textarea class="form-control input-sm isian isian-pilihan" name="pilihan_kode[]" rows="5" placeholder="Masukkan Pilihan"></textarea>
                        <select class="form-control input-sm isian select-manual" name="pilihan_kode[{{ $jumlah_isian }}][]" multiple placeholder="Masukkan Pilihan" style="display: none;">
                        </select>
                        <select class="form-control input-sm isian isian-referensi" name="referensi_kode[]" placeholder="Masukkan Pilihan" style="display: none;">
                            <option value="" selected>Pilihan Referensi</option>
                            @foreach (\App\Models\Enums\ReferensiEnum::all() as $key => $value)
                                <option value="{{ $value }}">{{ $key }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="padat">
                        <div class="btn-group-vertical">
                            <button type="button" class="btn btn-flat btn-danger btn-sm hapus-kode" title="Hapus Kode Isian"><i class="fa fa-trash hapus-kode"></i></button>
                        </div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
    <button type="button" class="btn btn-success btn-sm btn-block tambah-kode" data-type="utama" title="Tambah Kode Isian">
        <i class="fa fa-plus"></i>
    </button>
</div>