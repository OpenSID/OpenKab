<script>
    document.getElementById('formSuplemen').addEventListener('submit', async function (e) {
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
            const response = await fetch("{{ $form_action }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    // Pastikan Bearer Token disesuaikan jika diperlukan
                    'Authorization': 'Bearer {{ session('api_token') ?? '' }}'
                },
                body: JSON.stringify(jsonData)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // alert('Data berhasil disimpan!');
                window.location.href = "{{ route('profile.kependudukan.suplemen.index') }}";
            } else {
                // alert('Gagal menyimpan data: ' + (data.message || 'Terjadi kesalahan.'));
            }
        } catch (error) {
            console.error('Error:', error);
            // alert('Terjadi kesalahan saat menyimpan data.');
        }
    });

</script>