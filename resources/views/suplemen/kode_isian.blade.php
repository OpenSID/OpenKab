<h5><b>Kode Isian</b></h5>
<div class="table-responsive">
    <table class="table table-hover table-striped kode-isian">
        <thead>
            <tr style="font-weight: bold;">
                <td>#</td>
                <td>TIPE</td>
                <td>NAMA</td>
                <td>LABEL</td>
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
                    <td><input type="text" name="nama_kode[]" class="form-control input-sm isian" placeholder="Masukkan Nama"></td>
                    <td><input type="text" name="label_kode[]" class="form-control input-sm isian" placeholder="Masukkan Label"></td>
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
                            <button type="button" class="btn btn-flat btn-danger btn-sm hapus-kode" title="Hapus Kode Isian" style="display:none;"><i class="fa fa-trash hapus-kode"></i></button>
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


<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Menangani perubahan tipe
    document.querySelectorAll('.pilih_tipe').forEach(function(select) {
        select.addEventListener('change', function() {
            const row = this.closest('tr');
            const tipeValue = this.value;

            // Menampilkan atau menyembunyikan elemen berdasarkan tipe
            const isianPilihan = row.querySelector('.isian-pilihan');
            const selectManual = row.querySelector('.select-manual');
            const isianReferensi = row.querySelector('.isian-referensi');

            if (tipeValue === 'select-manual') {
                isianPilihan.style.display = 'none';
                selectManual.style.display = 'block';
                isianReferensi.style.display = 'none';
            } else if (tipeValue === 'select-otomatis') {
                isianPilihan.style.display = 'none';
                selectManual.style.display = 'none';
                isianReferensi.style.display = 'block';
            } else {
                isianPilihan.style.display = 'block';
                selectManual.style.display = 'none';
                isianReferensi.style.display = 'none';
            }
        });
    });

    // Menambahkan kode isian baru
    document.querySelector('.tambah-kode').addEventListener('click', function() {
        const tableBody = document.querySelector('#dragable-form-utama');
        const newRow = document.querySelector('tr.duplikasi').cloneNode(true);
        const newRowId = Date.now(); // Membuat ID unik untuk baris baru
        newRow.id = `gandakan-${newRowId}`;
        newRow.setAttribute('data-id', newRowId);
        
        // Menampilkan tombol hapus untuk row baru
        const deleteButton = newRow.querySelector('.hapus-kode');
        deleteButton.style.display = 'inline-block';

        // Reset input pada row baru
        newRow.querySelectorAll('input, textarea, select').forEach(input => input.value = '');
        
        // Menambahkan row baru ke tabel
        tableBody.appendChild(newRow);
    });

    // Menangani hapus kode isian
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('hapus-kode')) {
            const row = e.target.closest('tr');
            row.remove();
        }
    });
    $('.pilih_tipe').on('change', function() {
                var tipe = $(this).val();
                var atribut = '';
                var option = '{}';
                var parents = $(this).parents('.duplikasi');
                var isian_atribut = parents.find('.isian-atribut');
                var isian_pilihan = parents.find('.isian-pilihan').not('.select-manual');
                var isian_manual = parents.find('.select-manual');
                var isian_referensi = parents.find('.isian-referensi');
                var isian_required = parents.find('.isian-required');
                var isian = parents.find('.isian');
                var pindah_kode = parents.find('.pindah-kode');

                if (tipe == '') {
                    atribut = 'Masukkan Atribut';
                    option = 'Masukkan Pilihan';
                    isian.prop("disabled", true);
                    isian_required.prop("disabled", true);
                    isian.removeClass('required');
                    isian_referensi.addClass('hide');

                    isian_manual.addClass('hide');
                    isian_manual.removeClass('required');

                    // pindah_kode.addClass('hide');
                } else {
                    isian.prop("disabled", false);
                    isian_required.prop("disabled", false);
                    isian.addClass('required');
                    isian_atribut.removeClass('required');
                    isian_referensi.addClass('hide');
                    isian_manual.addClass('hide');
                    isian_manual.removeClass('required');

                    // pindah_kode.removeClass('hide');

                    if (tipe == 'select-manual') {
                        atribut = 'size="5"';
                        isian_manual.removeClass('hide')
                        isian_manual.addClass('required select2');

                        isian_manual.select2({
                            tags: true,
                            placeholder: "Masukkan Pilihan",
                            createTag: function(params) {
                                return {
                                    id: params.term,
                                    text: params.term,
                                    newOption: true
                                };
                            },
                            templateResult: function(data) {
                                var $result = $("<span></span>");
                                $result.text(data.text);

                                if (data.newOption) {
                                    $result.append(" <em>(Buat Baru)</em>");
                                }

                                return $result;
                            },
                            insertTag: function(data, tag) {
                                data.push(tag);
                            }
                        });

                        loadSelect2()

                        isian_referensi.addClass('hide');
                        isian_referensi.removeClass('required');
                        isian_pilihan.addClass('hide');
                        isian_pilihan.removeClass('show');
                        isian_pilihan.removeClass('required');
                    } else {
                        option = '{}';
                        isian_referensi.addClass('hide');
                        isian_referensi.removeClass('required');
                        isian_pilihan.removeClass('hide');
                        isian_pilihan.removeClass('required');

                        isian_manual.addClass('hide');
                        isian_manual.removeClass('required');
                        isian_manual.removeClass('select2')
                        if (isian_manual[0].classList.contains('select2-hidden-accessible') == true) {
                            isian_manual.removeAttr("data-select2-id").removeClass(
                                "select2-hidden-accessible").removeAttr("aria-hidden")
                            isian_manual[0].nextElementSibling.remove()
                        }

                        if (tipe == 'select-otomatis') {
                            atribut = 'size="5"';
                            isian_referensi.removeClass('hide');
                            isian_referensi.addClass('required');
                            isian_pilihan.addClass('hide');
                            isian_pilihan.removeClass('required');
                        } else if (tipe == 'text') {
                            atribut = 'minlength="5" maxlength="50"';
                        } else if (tipe == 'number') {
                            atribut = 'min="1" max="100" step="1"';
                        } else if (tipe == 'email') {
                            atribut = 'minlength="5" maxlength="50"';
                        } else if (tipe == 'url') {
                            atribut = 'minlength="5" maxlength="50"';
                        } else if (tipe == 'date') {
                            atribut = 'min="2021-01-01" max="2021-12-31"';
                        } else if (tipe == 'time') {
                            atribut = 'min="00:00" max="23:59"';
                        } else {
                            atribut = 'minlength="5" maxlength="50" rows="5"';
                        }
                        if (tipe == 'hari' || tipe == 'hari-tanggal') {
                            atribut = 'Masukkan atribut';
                            // isian_atribut.removeClass('required');
                            // isian_atribut.prop("disabled", true);;
                        }
                        isian_pilihan.prop("disabled", true);
                        isian_pilihan.addClass('required');
                    }
                }

                isian_atribut.attr("placeholder", atribut);
                isian_pilihan.attr("placeholder", option);
                $(this).parents('.duplikasi').find('.isian').val('');
            });
});

</script>
