$('#suplemen').on('click', '.btn-details', function() {
    var tr = $(this).closest('tr');
    var row = suplemen.row(tr);

    // Ambil data JSON dari atribut data-json
    var jsonData;
    try {
        jsonData = JSON.parse($(this).attr('data-json'));
    } catch (error) {
        console.error("Invalid JSON format", error);
        return;
    }

    var button = $(this);

    // Cek apakah baris sudah terbuka atau belum
    if (row.child.isShown()) {
        // Jika sudah terbuka, sembunyikan
        row.child.hide();
        tr.removeClass('shown');
        
        // Ubah tombol menjadi "Selengkapnya" dan ubah warna ke hijau
        button.text('Selengkapnya');
        button.removeClass('btn-danger');
        button.addClass('btn-success');
    } else {
        // Jika belum terbuka, tampilkan detail
        var detailHtml = '';
        $.each(jsonData, function(key, value) {
            detailHtml += `<p><b>${key}</b>: ${value}</p>`;
        });

        row.child(detailHtml).show();
        tr.addClass('shown');

        // Ubah tombol menjadi "Tutup" dan ubah warna ke merah
        button.text('Tutup');
        button.removeClass('btn-success');
        button.addClass('btn-danger');
    }
});