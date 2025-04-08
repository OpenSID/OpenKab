// Event listener untuk kategori
$('#nav-kategori a').on('click', function() {
var kategoriId = $(this).data('key');
var kategoriNama = $(this).data('name');

// Ambil kata kunci dari input pencarian
var searchKeyword = $('#search-keyword').val();

// Tambahkan kategori dan kata kunci ke filter, lalu panggil GetListCoordinates
<!-- updateFilterAndFetchData(kategoriId, searchKeyword); -->
GetListCoordinates($("#filter_kabupaten").val(), $("#filter_kecamatan").val(), $("#filter_desa").val(), kategoriId,
searchKeyword);

});

// Event listener untuk perubahan pencarian (ketika mengetik)
$('#search-keyword').on('input', function() {
var searchKeyword = $(this).val();
var kategoriId = getSelectedKategoriId(); // Ambil ID kategori yang sedang dipilih
// Panggil fungsi untuk memperbarui data berdasarkan pencarian dan kategori
<!-- updateFilterAndFetchData(kategoriId, searchKeyword); -->
GetListCoordinates($("#filter_kabupaten").val(), $("#filter_kecamatan").val(), $("#filter_desa").val(), kategoriId,
searchKeyword);

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
GetListCoordinates($("#filter_kabupaten").val(), $("#filter_kecamatan").val(), $("#filter_desa").val(), kategoriId,
searchKeyword);
}


function GetListCoordinates(kabupaten = null, kecamatan = null, desa = null, kategoriId = null, searchKeyword = null) {
var coordUrl = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/plan/get-list-coordinate' }}");
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
headers: header,
dataType: 'json',
success: function(data) {
// Hapus semua marker dari peta sebelum menambahkan yang baru
map.eachLayer((layer) => {
if (layer instanceof L.Marker) {
layer.remove();
}
});
var isFirst = true;
for (var i=0; i < data.length; i++ ){ if (data[i].lat !=null && data[i].lng !=null){ var
    marker=L.marker([parseFloat(data[i].lat), parseFloat(data[i].lng)], {icon: markerIcon}).bindPopup(data[i].nama
    + '<br><strong>' + data[i].desk + '</strong><br>' + data[i].point.nama+"<hr>Provinsi :" +
    data[i].config.nama_propinsi + "<br>Kota :" + data[i].config.nama_kabupaten + "<br>Kecamatan :" +
    data[i].config.nama_kecamatan + "<br>Desa :" + data[i].config.nama_desa ).addTo(map)
    marker.on('mouseover',function(ev) {
    ev.target.openPopup();
    });
    if (isFirst){
    isFirst = false;
    map.panTo(new L.LatLng(parseFloat(data[i].lat), parseFloat(data[i].lng)));
    }
    }
    }

    // Hapus semua kartu dari container
    var cardContainer = document.getElementById('card-container');
    cardContainer.innerHTML = '';

    // Membuat baris baru untuk kartu
    var row = document.createElement('div');
    row.classList.add('row', 'g-3'); // Menambahkan gap antar kolom dengan g-3 (atau g-4 untuk lebih besar)

    data.forEach((item, index) => {
    // Membuat kartu
    var card = document.createElement('div');
    card.className = 'col-md-4 mb-4 geospasial-card';

    // Gambar
    var img = document.createElement('img');
    img.src = item.thumbnail || '{{ asset('assets/img/default-map.png') }}';
    img.alt = 'Thumbnail';

    // Pemisah
    var separator = document.createElement('div');
    separator.className = 'separator';

    // Card Body
    var cardBody = document.createElement('div');

    var description = document.createElement('span');
    description.className = 'description';
    description.innerHTML = item.nama + '<br><strong>' + item.desk + '</strong><br>' + item.point.nama;

    // Ikon Detail (menggunakan FontAwesome)
    var detailIcon = document.createElement('i');
    detailIcon.className = 'bi bi-info-circle-fill detail-icon'; // Bootstrap Icons

    // Event Listener untuk ikon detail
    detailIcon.addEventListener('click', function () {
    document.getElementById('modalContent').innerHTML = `
    <h5>${item.nama}</h5>
    <p>${item.desk}</p>
    <p><strong>Provinsi:</strong> ${item.config.nama_propinsi}</p>
    <p><strong>Kota:</strong> ${item.config.nama_kabupaten}</p>
    <p><strong>Kecamatan:</strong> ${item.config.nama_kecamatan}</p>
    <p><strong>Desa:</strong> ${item.config.nama_desa}</p>
    <p><strong>Latitude:</strong> ${item.lat}</p>
    <p><strong>Longitude:</strong> ${item.lng}</p>
    `;

    $('#detailModal').modal();
    });

    // Menambahkan elemen ke kartu
    card.appendChild(img);
    card.appendChild(detailIcon);
    cardBody.appendChild(separator);
    cardBody.appendChild(description);
    card.appendChild(cardBody);
    row.appendChild(card);

    // Reset baris setelah 3 kartu
    if ((index + 1) % 3 === 0) {
    cardContainer.appendChild(row);
    row = document.createElement('div');
    row.classList.add('row', 'g-3');
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
