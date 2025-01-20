// Event listener untuk kategori
$('#nav-kategori a').on('click', function() {
    var kategoriId = $(this).data('key');
    var kategoriNama = $(this).data('name');
    
    // Ambil kata kunci dari input pencarian
    var searchKeyword = $('#search-keyword').val();
    
    // Tambahkan kategori dan kata kunci ke filter, lalu panggil GetListCoordinates
    <!-- updateFilterAndFetchData(kategoriId, searchKeyword); -->
    GetListCoordinates($("#filter_kabupaten").val(), $("#filter_kecamatan").val(), $("#filter_desa").val(), kategoriId, searchKeyword);

});

// Event listener untuk perubahan pencarian (ketika mengetik)
$('#search-keyword').on('input', function() {
    var searchKeyword = $(this).val();
    var kategoriId = getSelectedKategoriId();  // Ambil ID kategori yang sedang dipilih
    // Panggil fungsi untuk memperbarui data berdasarkan pencarian dan kategori
    <!-- updateFilterAndFetchData(kategoriId, searchKeyword); -->
    GetListCoordinates($("#filter_kabupaten").val(), $("#filter_kecamatan").val(), $("#filter_desa").val(), kategoriId, searchKeyword);

});
document.querySelectorAll('.nav-link').forEach(function (item) {
    item.addEventListener('click', function () {
        // Menghapus kelas "active" dari kategori lainnya
        document.querySelectorAll('.nav-link').forEach(function (link) {
            link.classList.remove('active');
        });

        // Menambahkan kelas "active" pada kategori yang dipilih
        item.classList.add('active');
    });
});
    function getSelectedKategoriId() {
        var selectedKategori = document.querySelector('.nav-link.active');
        if (selectedKategori) {
            return selectedKategori.getAttribute('data-key');
        }
        return null;
    }

function updateFilterAndFetchData(kategoriId, searchKeyword) {
    // Panggil GetListCoordinates dengan kategori dan kata kunci yang dipilih
    GetListCoordinates($("#filter_kabupaten").val(), $("#filter_kecamatan").val(), $("#filter_desa").val(), kategoriId, searchKeyword);
}


function GetListCoordinates(kabupaten = null, kecamatan = null, desa = null, kategoriId = null, searchKeyword = null) {
    var coordUrl = "{{ url('api/v1/plan/get-list-coordinate') }}";
    var filterParams = [];

    if (kategoriId != null) {
        filterParams.push("filter[kategori]=" + kategoriId);
    }
    if (searchKeyword != null && searchKeyword !== '') {
        filterParams.push("filter[kata_kunci]=" + encodeURIComponent(searchKeyword));
    }
    if (kabupaten != null) {
        filterParams.push("filter[kabupaten]=" + kabupaten);
    }
    if (kecamatan != null) {
        filterParams.push("filter[kecamatan]=" + kecamatan);
    }
    if (desa != null) {
        filterParams.push("filter[desa]=" + desa);
    }

    // Gabungkan parameter filter dengan '&' dan pastikan jika ada filter maka tambahkan '?' di awal URL
    if (filterParams.length > 0) {
        coordUrl += "?" + filterParams.join("&");
    }
    $.ajax({
        type: 'GET',
        url: coordUrl,
        dataType: 'json',
        success: function(data) {
            // Hapus semua marker dari peta sebelum menambahkan yang baru
            map.eachLayer((layer) => {
                if (layer instanceof L.Marker) {
                    layer.remove();
                }
            });

            // Hapus semua kartu dari container
            var cardContainer = document.getElementById('card-container');
            cardContainer.innerHTML = '';

            // Membuat baris baru untuk kartu
            var row = document.createElement('div');
            row.classList.add('row', 'g-3'); // Menambahkan gap antar kolom dengan g-3 (atau g-4 untuk lebih besar)

            data.forEach((item, index) => {
                // Buat kartu
                // Membuat kartu
                var card = document.createElement('div');
                card.className = 'col-md-4 mb-4'; // Setiap kartu akan memiliki lebar 4 kolom dan margin bawah 3
                card.style = 'border: 1px solid #ddd; border-radius: 5px; overflow: hidden; box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1); margin-bottom: 20px; padding: 0;'; // Hapus padding dan margin dari card

                var img = document.createElement('img');
                img.src = item.thumbnail || '{{asset('assets/img/default-map.png')}}';
                img.alt = 'Thumbnail';
                img.style = 'width: 100%; height: 150px; object-fit: cover;';

                // Menambahkan pemisah antara gambar dan detail
                var separator = document.createElement('div');
                separator.style = 'height: 1px; background-color: #ddd; margin: 0;';

                // Card body untuk detail
                var cardBody = document.createElement('div');
                cardBody.style = 'padding: 0; background-color: #f7f7f7;'; // Hilangkan padding di dalam cardBody

                var description = document.createElement('span');
                description.style = 'font-size: 14px; color: #666; display: block; padding: 10px 10px;';
                description.innerHTML = item.nama + '<br><strong>' + item.desk + '</strong><br>' + item.point.nama;

                // Menambahkan pemisah dan deskripsi ke cardBody
                cardBody.appendChild(separator);
                cardBody.appendChild(description);

                // Menambahkan gambar dan detail ke dalam kartu
                card.appendChild(img);
                card.appendChild(cardBody);

                // Menambahkan kartu ke baris
                row.appendChild(card);


                // Jika sudah memiliki 3 kartu dalam satu baris, reset dan buat baris baru
                if ((index + 1) % 3 === 0) {
                    cardContainer.appendChild(row);
                    row = document.createElement('div');
                    row.classList.add('row', 'g-3'); // Membuat row baru dengan gap
                }
            });

            // Menambahkan baris terakhir jika ada sisa kartu yang belum dimasukkan ke baris
            if (row.children.length > 0) {
                cardContainer.appendChild(row);
            }

            // Fokus ke marker pertama (jika ada)
            if (data.length > 0 && data[0].lat && data[0].lng) {
                map.panTo(new L.LatLng(parseFloat(data[0].lat), parseFloat(data[0].lng)));
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error fetching data:', textStatus, errorThrown);
        }
    });
}


