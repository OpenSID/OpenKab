(function (window) {
    'use strict';

    var popupNumber = 0;

    function escape(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderLokasi(info) {
        info = info || {};
        var html = '<header class="dataspasial-popup-header">'
            + '<div class="dataspasial-popup-eyebrow">Informasi lokasi</div>'
            + '<h3 class="dataspasial-popup-title">' + escape(info.nama || 'Lokasi') + '</h3>';
        if (info.deskripsi) {
            html += '<p class="dataspasial-popup-description">' + escape(info.deskripsi) + '</p>';
        }
        if (info.kategori || info.sektor) {
            html += '<div class="dataspasial-popup-tags">';
            if (info.kategori) html += '<span class="dataspasial-popup-tag">' + escape(info.kategori) + '</span>';
            if (info.sektor) html += '<span class="dataspasial-popup-tag dataspasial-popup-tag-sector">' + escape(info.sektor) + '</span>';
            html += '</div>';
        }
        html += '</header>';

        var rows = [
            ['Provinsi', info.provinsi],
            ['Kota / Kabupaten', info.kabupaten],
            ['Kecamatan', info.kecamatan],
            ['Desa', info.desa],
            ['Koordinat', info.koordinat],
        ].filter(function (row) { return row[1] !== null && row[1] !== undefined && row[1] !== ''; });
        if (rows.length) {
            html += '<dl class="dataspasial-popup-location">';
            rows.forEach(function (row) {
                html += '<div class="dataspasial-popup-location-row"><dt>' + escape(row[0])
                    + '</dt><dd>' + escape(row[1]) + '</dd></div>';
            });
            html += '</dl>';
        }
        return html;
    }

    function opsiPeta(map) {
        var size = map.getSize();
        return {
            className: 'dataspasial-leaflet-popup',
            maxWidth: Math.max(80, Math.min(360, size.x - 48)),
            minWidth: 0,
            maxHeight: Math.max(80, Math.min(420, size.y - 60))
        };
    }

    function ikutiUkuranPeta(map) {
        var popupAktif = null;
        function sesuaikan() {
            if (!popupAktif) return;
            var options = opsiPeta(map);
            popupAktif.options.maxWidth = options.maxWidth;
            popupAktif.options.maxHeight = options.maxHeight;
            popupAktif.update();
        }
        map.on('popupopen', function (event) {
            popupAktif = event.popup.options.className === 'dataspasial-leaflet-popup' ? event.popup : null;
            sesuaikan();
        });
        map.on('popupclose', function (event) {
            if (popupAktif === event.popup) popupAktif = null;
        });
        map.on('resize', sesuaikan);
    }

    function renderIsian(items, periode, adaPeriode) {
        var sumber = [];
        var html = '<dl class="dataspasial-popup-fields">';
        (items || []).forEach(function (item) {
            var entry = adaPeriode && Array.isArray(item.nilai_periode)
                ? item.nilai_periode.find(function (data) { return String(data.periode) === periode; })
                : null;
            var value = adaPeriode ? (entry ? entry.nilai : null) : item.nilai;
            var display = value === null || value === undefined || value === ''
                ? 'Belum ada data untuk periode ini'
                : (value === true ? 'Ya' : (value === false ? 'Tidak' : value));
            var kosong = value === null || value === undefined || value === '';
            html += '<div class="dataspasial-popup-field"><dt>' + escape(item.label) + '</dt>'
                + '<dd' + (kosong ? ' class="dataspasial-popup-field-empty"' : '') + '>' + escape(display);
            if (item.satuan && value !== null && value !== undefined && value !== '') {
                html += ' ' + escape(item.satuan);
            }
            html += '</dd></div>';
            if (entry && entry.sumber && sumber.indexOf(String(entry.sumber)) === -1) {
                sumber.push(String(entry.sumber));
            }
        });
        html += '</dl>';
        if (sumber.length) {
            html += '<div class="dataspasial-popup-source">Sumber: ' + sumber.map(escape).join(', ') + '</div>';
        }
        return html;
    }

    function render(groups, unavailable) {
        groups = Array.isArray(groups) ? groups : [];
        var accordionName = 'popup-skema-' + (++popupNumber);
        var html = '<section class="dataspasial-popup-skema"><h4 class="dataspasial-popup-section-title">Skema DataSpasial</h4>';

        if (unavailable) {
            html += '<div class="dataspasial-popup-notice">Sebagian informasi skema sementara tidak tersedia.</div>';
        }
        if (!groups.length && !unavailable) {
            html += '<div class="dataspasial-popup-notice">Belum ada informasi skema terbit untuk lokasi ini.</div>';
        }

        groups.forEach(function (group, index) {
            var title = escape(group.sektor || 'DataSpasial');
            var periods = Array.isArray(group.periode) ? group.periode.map(String) : [];
            var selected = group.periode_aktif === null || group.periode_aktif === undefined
                ? (periods[0] || '') : String(group.periode_aktif);
            if (group.versi !== null && group.versi !== undefined) {
                title += ' · v' + escape(group.versi);
            }
            html += '<details name="' + accordionName + '" class="dataspasial-popup-group"' + (index === 0 ? ' open' : '') + '>'
                + '<summary><span>' + title + '</span></summary><div class="dataspasial-popup-group-body">';
            if (group.fasilitas) {
                html += '<div class="dataspasial-popup-facility">' + escape(group.fasilitas) + '</div>';
            }
            html += '<label class="dataspasial-popup-period-label"><span>Periode</span>'
                + '<select class="dataspasial-popup-period" aria-label="Periode ' + escape(group.sektor || 'DataSpasial') + '"'
                + (periods.length ? '' : ' disabled') + '>';
            if (!periods.length) html += '<option>Tidak tersedia</option>';
            periods.forEach(function (period) {
                html += '<option value="' + escape(period) + '"' + (period === selected ? ' selected' : '') + '>'
                    + escape(period || 'Tanpa periode') + '</option>';
            });
            html += '</select></label>';
            if (periods.length) {
                periods.forEach(function (period) {
                    html += '<div data-popup-period="' + escape(period) + '"' + (period === selected ? '' : ' hidden') + '>'
                        + renderIsian(group.isian, period, true) + '</div>';
                });
            } else {
                html += renderIsian(group.isian, '', false);
            }
            html += '</div></details>';
        });

        return html + '</section>';
    }

    document.addEventListener('change', function (event) {
        var select = event.target;
        if (!select.matches || !select.matches('.dataspasial-popup-period')) return;
        var group = select.closest('.dataspasial-popup-group');
        if (!group) return;
        group.querySelectorAll('[data-popup-period]').forEach(function (panel) {
            panel.hidden = panel.getAttribute('data-popup-period') !== select.value;
        });
    });

    window.PopupSkema = { render: render, renderLokasi: renderLokasi, opsiPeta: opsiPeta, ikutiUkuranPeta: ikutiUkuranPeta, escape: escape };
}(window));
