<script>
    document.getElementById('formSuplemen').addEventListener('submit', async function (e) {
        const header = @include('layouts.components.header_bearer_api_gabungan');
        e.preventDefault();

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

</script>