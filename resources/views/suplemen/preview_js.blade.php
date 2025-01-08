<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('previewButton').addEventListener('click', function () {
            let isValid = true;
            const requiredFields = document.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid'); // Tambahkan class error pada input
                } else {
                    field.classList.remove('is-invalid'); // Hapus class error jika sudah terisi
                }
            });

            // Jika tidak valid, cegah modal tampil
            if (!isValid) {
                // alert('Harap isi semua bidang yang wajib (required).');
                return;
            }
            // Ambil nilai dari form

            // Ambil data dari PHP
            const sasaranMap = JSON.parse('<?php echo json_encode(unserialize(SASARAN)); ?>');

            // Ambil value yang dipilih dari dropdown
            const selectedValue = document.querySelector('select[name="sasaran"]').value;

            // Ambil nama sasaran berdasarkan value
            const sasaran = sasaranMap[selectedValue] || 'Belum dipilih';
            const nama = document.querySelector('input[name="nama"]').value || 'Belum diisi';
            const keterangan = document.querySelector('textarea[name="keterangan"]').value || 'Belum diisi';
            const status = document.querySelector('select[name="status"]').value || 'Belum dipilih';

            // Format status
            const statusText = status === '1' ? 'Aktif' : status === '0' ? 'Tidak Aktif' : 'Belum dipilih';

            // Ambil data kode isian
            const kodeIsianRows = document.querySelectorAll('#dragable-form-utama tr');
            // let kodeIsianContent = '<ul>';
            // kodeIsianRows.forEach(row => {
            //     const tipe = row.querySelector('select[name="tipe_kode[]"]').value || 'N/A';
            //     const namaKode = row.querySelector('input[name="nama_kode[]"]').value || 'N/A';
            //     const label = row.querySelector('input[name="label_kode[]"]').value || 'N/A';
            //     kodeIsianContent += `<li>${tipe} - ${namaKode} - ${label}</li>`;
            // });
            // kodeIsianContent += '</ul>';
            let kodeIsianContent = '';
            kodeIsianRows.forEach(row => {
                const tipe = row.querySelector('select[name="tipe_kode[]"]').value || 'N/A';
                const namaKode = row.querySelector('input[name="nama_kode[]"]').value || 'N/A';
                const label = row.querySelector('input[name="label_kode[]"]').value || 'N/A';
                const placeholder = row.querySelector('input[name="deskripsi_kode[]"]') ? row.querySelector('input[name="deskripsi_kode[]"]').value : '';
                const kolom = row.querySelector('select[name="kolom[]"]').value || '12';
                // Cek jika elemen required ada
                const requiredElement = row.querySelector('input[name="required_kode[]"]');
                const required = requiredElement && requiredElement.checked ? 'required' : '';

                // Menentukan class kolom
                const widthClass = 'col-sm-' + kolom;

                let fieldHtml = '';
                if (tipe === 'date') {
                    fieldHtml = `
                        <div class="row mb-3">
                            <label class="col-sm-3 control-label" for="${namaKode}">${label}</label>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="${widthClass}">
                                        <div class="input-group input-group-sm date">
                                            <input type="date" class="form-control input-sm pull-right" name="input_data[${namaKode}]" id="${namaKode}" ${required} placeholder="${placeholder}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                } else if (tipe === 'text') {
                    fieldHtml = `
                        <div class="row mb-3">
                            <label class="col-sm-3 control-label" for="${namaKode}">${label}</label>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="${widthClass}">
                                        <input type="text" class="form-control" name="input_data[${namaKode}]" id="${namaKode}" ${required} placeholder="${placeholder}">
                                    </div>
                                </div>
                            </div>
                        </div>`;
                } else if (tipe === 'email') {
                    fieldHtml = `
                        <div class="row mb-3">
                            <label class="col-sm-3 control-label" for="${namaKode}">${label}</label>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="${widthClass}">
                                        <input type="email" class="form-control" name="input_data[${namaKode}]" id="${namaKode}" ${required} placeholder="${placeholder}">
                                    </div>
                                </div>
                            </div>
                        </div>`;
                } else if (tipe === 'url') {
                    fieldHtml = `
                        <div class="row mb-3">
                            <label class="col-sm-3 control-label" for="${namaKode}">${label}</label>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="${widthClass}">
                                        <input type="url" class="form-control" name="input_data[${namaKode}]" id="${namaKode}" ${required} placeholder="${placeholder}">
                                    </div>
                                </div>
                            </div>
                        </div>`;
                } else if (tipe === 'number') {
                    fieldHtml = `
                        <div class="row mb-3">
                            <label class="col-sm-3 control-label" for="${namaKode}">${label}</label>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="${widthClass}">
                                        <input type="number" class="form-control" name="input_data[${namaKode}]" id="${namaKode}" ${required} placeholder="${placeholder}">
                                    </div>
                                </div>
                            </div>
                        </div>`;
                } else if (tipe === 'time') {
                    fieldHtml = `
                        <div class="row mb-3">
                            <label class="col-sm-3 control-label" for="${namaKode}">${label}</label>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="${widthClass}">
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-addon">
                                                <i class="fa fa-clock-o"></i>
                                            </div>
                                            <input type="time" class="form-control input-sm" name="input_data[${namaKode}]" id="${namaKode}" ${required} placeholder="${placeholder}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                } else if (tipe === 'textarea') {
                    fieldHtml = `
                        <div class="row mb-3">
                            <label class="col-sm-3 control-label" for="${namaKode}">${label}</label>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="${widthClass}">
                                        <textarea class="form-control" name="input_data[${namaKode}]" id="${namaKode}" ${required} placeholder="${placeholder}"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                } else if (tipe === 'select-manual') {
                    const pilihan = row.querySelector('textarea[name="pilihan_kode[]"]').value.split(','); // Assuming choices are in a comma-separated string
                    let optionsHtml = '<option value="">-- Pilih --</option>';
                    pilihan.forEach(option => {
                        optionsHtml += `<option value="${option}">${option}</option>`;
                    });

                    fieldHtml = `
                        <div class="row mb-3">
                            <label class="col-sm-3 control-label" for="${namaKode}">${label}</label>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="${widthClass}">
                                        <select class="form-control" name="input_data[${namaKode}]" id="${namaKode}" ${required}>
                                            ${optionsHtml}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                }

                kodeIsianContent += fieldHtml;
            });


            // Isi modal dengan data
            document.getElementById('previewSasaran').textContent = sasaran;
            document.getElementById('previewNama').textContent = nama;
            document.getElementById('previewKeterangan').textContent = keterangan;
            document.getElementById('previewStatus').textContent = statusText;
            document.getElementById('previewKodeIsian').innerHTML = kodeIsianContent;

            // Tampilkan modal
            $('#previewModal').modal('show');
        });
    });
</script>