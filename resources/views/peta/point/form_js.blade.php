<script>
    document.getElementById('formSuplemen').addEventListener('submit', async function (e) {
        
        e.preventDefault();

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