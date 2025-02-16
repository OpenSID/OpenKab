<script nonce="{{ csp_nonce() }}">
document.addEventListener("DOMContentLoaded", function(event) {

    const header = @include('layouts.components.header_bearer_api_gabungan');
    var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/infrastruktur' }}");
   // Fungsi untuk mengambil data Komoditas dari API
   fetch(url, {
    method: 'GET',
    headers: header
   })
    .then(response => response.json())
    .then(data => {
        // Menampilkan data Komoditas di dalam div #komoditas-container
        const komoditasTable = document.createElement('table');
        komoditasTable.classList.add('table', 'table-bordered');
        komoditasTable.innerHTML = `
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Jenis Sarana/Prasarana</th>
                    <th>Kondisi Baik</th>
                    <th>Kondisi Rusak</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                </tr>
            </thead>
            <tbody>
                ${data.map(item => `
                    <tr>
                        <td>${item.kategori}</td>
                        <td>${item.jenis_sarana}</td>
                        <td>${item.kondisi_baik}</td>
                        <td>${item.kondisi_rusak}</td>
                        <td>${item.jumlah}</td>
                        <td>${item.satuan}</td>
                    </tr>
                `).join('')}
            </tbody>
        `;
        document.getElementById('infrastruktur').appendChild(komoditasTable);
    });
});

</script>