var modalSkemaSequence = 0;

function getPopupSkemaRequest() {
    if (!popupSkemaRequest) {
        popupSkemaRequest = $.ajax({
            type: 'GET',
            url: "{{ route('presisi.geo-spasial.skema') }}",
            dataType: 'json'
        });
        popupSkemaRequest.fail(function() { popupSkemaRequest = null; });
    }
    return popupSkemaRequest;
}

function renderPopupSkema(detail) {
    return window.PopupSkema
        ? window.PopupSkema.render(detail.skema, detail.unavailable)
        : '<div class="text-muted small">Informasi skema tidak dapat dimuat.</div>';
}

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
var requestSequence = ++coordinateRequestSequence;
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

dataType: 'json',
success: function(data) {
if (requestSequence !== coordinateRequestSequence) return;
// Hapus semua marker dari peta sebelum menambahkan yang baru
map.eachLayer((layer) => {
if (layer instanceof L.Marker) {
layer.remove();
}
});
var isFirst = true;
var markerDetails = [];
var escapePopup = function(value) {
    return String(value == null ? '' : value).replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
};
var renderLocation = function(item) {
    var config = item.config || {};
    var point = item.point || {};
    var koordinat = item.lat != null && item.lng != null ? item.lat + ', ' + item.lng : null;
    if (window.PopupSkema) {
        return window.PopupSkema.renderLokasi({
            nama: item.nama, deskripsi: item.desk, kategori: point.nama,
            provinsi: config.nama_propinsi, kabupaten: config.nama_kabupaten,
            kecamatan: config.nama_kecamatan, desa: config.nama_desa, koordinat: koordinat
        });
    }
    return '<strong>' + escapePopup(item.nama) + '</strong><div>' + escapePopup(item.desk)
        + '</div><div>' + escapePopup(point.nama) + '</div><div>Provinsi: ' + escapePopup(config.nama_propinsi)
        + '</div><div>Kota: ' + escapePopup(config.nama_kabupaten)
        + '</div><div>Kecamatan: ' + escapePopup(config.nama_kecamatan) + '</div><div>Desa: '
        + escapePopup(config.nama_desa) + '</div><div>Koordinat: ' + escapePopup(koordinat) + '</div>';
};
for (var i=0; i < data.length; i++ ){ if (data[i].lat !=null && data[i].lng !=null){
    var item = data[i];
    var basePopup = '<div class="peta-popup">' + renderLocation(item);
    var marker = L.marker([parseFloat(item.lat), parseFloat(item.lng)], {icon: markerIcon})
        .bindPopup(basePopup + '<div class="dataspasial-popup-notice">Memuat informasi skema...</div></div>',
            window.PopupSkema ? window.PopupSkema.opsiPeta(map) : {maxWidth: 360, maxHeight: 300}).addTo(map);
    markerDetails.push({id: item.id, marker: marker, base: basePopup});
    if (isFirst){
    isFirst = false;
    map.panTo(new L.LatLng(parseFloat(data[i].lat), parseFloat(data[i].lng)));
    }
    }
    }

    if (markerDetails.length) {
        getPopupSkemaRequest().done(function(result) {
            if (requestSequence !== coordinateRequestSequence) return;
            markerDetails.forEach(function(entry) {
                var detail = (result.lokasi || {})[String(entry.id)] || {};
                entry.marker.setPopupContent(entry.base + renderPopupSkema(detail) + '</div>');
            });
        }).fail(function() {
            if (requestSequence !== coordinateRequestSequence) return;
            markerDetails.forEach(function(entry) {
                entry.marker.setPopupContent(entry.base + renderPopupSkema({unavailable: true}) + '</div>');
            });
        });
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
    description.innerHTML = escapePopup(item.nama) + '<br><strong>' + escapePopup(item.desk)
        + '</strong><br>' + escapePopup((item.point || {}).nama);

    // Ikon Detail (menggunakan FontAwesome)
    var detailIcon = document.createElement('i');
    detailIcon.className = 'bi bi-info-circle-fill detail-icon'; // Bootstrap Icons

    // Event Listener untuk ikon detail
    detailIcon.addEventListener('click', function () {
    var sequence = ++modalSkemaSequence;
    var base = renderLocation(item);
    var content = document.getElementById('modalContent');
    content.innerHTML = base + '<div class="text-muted small">Memuat informasi skema...</div>';
    $('#detailModal').modal();
    getPopupSkemaRequest().done(function(result) {
        if (sequence !== modalSkemaSequence) return;
        content.innerHTML = base + renderPopupSkema((result.lokasi || {})[String(item.id)] || {});
    }).fail(function() {
        if (sequence !== modalSkemaSequence) return;
        content.innerHTML = base + renderPopupSkema({unavailable: true});
    });
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
