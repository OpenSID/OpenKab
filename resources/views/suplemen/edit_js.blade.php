
    document.getElementById('formSuplemen').addEventListener('submit', async function (e) {
        const header = @include('layouts.components.header_bearer_api_gabungan');
        const rows = document.querySelectorAll('#dragable-form-utama tr.duplikasi');
        let formDatas = [];

        // Mengumpulkan data dari setiap row
        rows.forEach(function(row) {
            const tipe = row.querySelector('select.pilih_tipe').value;
            const namaKode = row.querySelector('input[name="nama_kode[]"]').value;
            const labelKode = row.querySelector('input[name="label_kode[]"]').value;
            const deskripsiKode = row.querySelector('input[name="deskripsi_kode[]"]').value;
            const required = row.querySelector('input.isian-required').checked ? 1 : 0;
            const kolom = row.querySelector('select[name="kolom[]"]').value;
            const atribut = row.querySelector('textarea[name="atribut_kode[]"]').value;
            const pilihanKode = row.querySelector('textarea[name="pilihan_kode[]"]').value;
            const referensiKode = row.querySelector('select[name="referensi_kode[]"]').value;

            // Menyimpan data dalam array
            formDatas.push({
                tipe: tipe,
                nama_kode: namaKode,
                label_kode: labelKode,
                deskripsi_kode: deskripsiKode,
                required: required,
                kolom: kolom,
                atribut: atribut,
                pilihan_kode: pilihanKode,
                referensi_kode: referensiKode
            });
        });

        // Menyimpan data ke dalam input hidden
        document.getElementById('form_isian').value = JSON.stringify(formDatas);

        e.preventDefault();

        const formData = new FormData(this);
        const jsonData = Object.fromEntries(formData.entries());

        try {
            const response = await fetch(new URL("{{ config('app.databaseGabunganUrl').''.$form_action }}"), {
                method: 'POST',
                headers: header,
                body: JSON.stringify(jsonData)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // alert('Data berhasil disimpan!');
                window.location.href = "{{ route('suplemen') }}";
            } else {
                // alert('Gagal menyimpan data: ' + (data.message || 'Terjadi kesalahan.'));
            }
        } catch (error) {
            console.error('Error:', error);
            // alert('Terjadi kesalahan saat menyimpan data.');
        }
    });

    let jumlahIsianBaru = {{ $jumlah_isian ?? 0 }}; // Menyimpan jumlah baris yang ada saat ini

    // Fungsi untuk menambahkan baris baru
    function tambahBaris() {
        let newRow = document.createElement('tr');
        newRow.classList.add('duplikasi', 'ui-sortable-handle');
        newRow.id = "gandakan-" + jumlahIsianBaru; // ID baris baru
        newRow.setAttribute('data-id', jumlahIsianBaru);

        // Masukkan konten baris baru
        newRow.innerHTML = `
            <td><i class="fa fa-sort-alpha-desc" aria-hidden="true"></i></td>
            <td><select class="form-control input-sm pilih_tipe" name="tipe_kode[]"><option value="">Pilih Tipe</option></select></td>
            <td><input type="text" name="nama_kode[]" class="form-control input-sm isian" required placeholder="Masukkan Nama"></td>
            <td><input type="text" name="label_kode[]" class="form-control input-sm isian" required placeholder="Masukkan Label"></td>
            <td><input type="text" name="deskripsi_kode[]" class="form-control input-sm isian" placeholder="Masukkan Placeholder"></td>
            <td class="text-center"><input class="isian-required" type="checkbox" value="1" name="required_kode[${jumlahIsianBaru}]"></td>
            <td class="text-center"><select class="form-control input-sm" name="kolom[]"><option value="12">col-12</option></select></td>
            <td><textarea class="form-control input-sm isian isian-atribut" name="atribut_kode[]" rows="5" placeholder="Masukkan Atribut"></textarea></td>
            <td><textarea class="form-control input-sm isian isian-pilihan" name="pilihan_kode[]" rows="5" placeholder="Masukkan Pilihan"></textarea></td>
            <td class="padat"><div class="btn-group-vertical"><button type="button" class="btn btn-flat btn-danger btn-sm hapus-kode" title="Hapus Kode Isian" onclick="hapusBaris(${jumlahIsianBaru})"><i class="fa fa-trash hapus-kode"></i></button></div></td>
        `;

        // Tambahkan baris baru ke dalam tabel
        document.getElementById('dragable-form-utama').appendChild(newRow);

        // Update jumlah baris untuk ID berikutnya
        jumlahIsianBaru++;
    }

    // Fungsi untuk menghapus baris
    function hapusBaris(button) {
    // Mengambil elemen <tr> yang merupakan parent dari tombol yang diklik
    var row = button.closest('tr');
    
    if (row) {
        // Menghapus baris tersebut
        row.remove();
    }
}


        
    document.addEventListener('DOMContentLoaded', function() {
        
        const namaKodeInputs = document.querySelectorAll('input[name="nama_kode[]"]');
    
        namaKodeInputs.forEach(input => {
            input.addEventListener("input", function () {
                // Hapus karakter yang tidak valid
                this.value = this.value.replace(/[^a-zA-Z0-9_]/g, "");

                // Pastikan tidak dimulai dengan angka
                if (/^\d/.test(this.value)) {
                    alert("Nama kode tidak boleh dimulai dengan angka.");
                    this.value = this.value.replace(/^\d+/, "");
                }
            });
        });
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


