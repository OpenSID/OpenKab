(function (window) {
    'use strict';

    var map = null;
    var boundaryLayer = null;
    var layerGroups = {};
    var markerMeta = [];
    var activeLayers = {};
    var thematic = null;
    var polygons = [];
    var meta = {};
    var markerIcon = null;

    var NO_DATA_COLOR = '#d2d6de';
    var SCALE_COLORS = ['#f7fbff', '#c6dbef', '#9ecae1', '#6baed6', '#3182bd', '#08519c'];
    var LEGACY_LAYER_PREFIX = 'legacy:';
    var DATA_LAYER_PREFIX = 'data:';

    function el(id) {
        return document.getElementById(id);
    }

    function show(id, visible) {
        var node = el(id);
        if (node) {
            node.style.display = visible ? '' : 'none';
        }
    }

    function setHtml(id, html) {
        var node = el(id);
        if (node) {
            node.innerHTML = html;
        }
    }

    function esc(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function isPoint(geometry) {
        return geometry && geometry.type === 'Point' &&
            Array.isArray(geometry.coordinates) && geometry.coordinates.length >= 2;
    }

    function isPolygon(geometry) {
        return geometry && (geometry.type === 'Polygon' || geometry.type === 'MultiPolygon');
    }

    function layerKeyOf(props) {
        props = props || {};

        if (props.dataspasial) {
            return DATA_LAYER_PREFIX + (props.sektor_kode || props.sektor_nama || 'dataspasial');
        }

        var key = props.kategori || props.simbol || 'lainnya';

        return LEGACY_LAYER_PREFIX + key;
    }

    function init(options) {
        options = options || {};
        if (map) {
            return map;
        }

        var mapId = options.mapId || 'map';
        map = L.map(mapId).setView([-2.5, 118], 5);

        if (window.PETA_GABUNGAN_ICON && window.PETA_GABUNGAN_ICON.iconUrl) {
            markerIcon = new L.Icon({
                iconUrl: window.PETA_GABUNGAN_ICON.iconUrl,
                shadowUrl: window.PETA_GABUNGAN_ICON.shadowUrl,
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
        }

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        boundaryLayer = L.layerGroup().addTo(map);

        return map;
    }

    function clusterFor(key) {
        if (!layerGroups[key]) {
            layerGroups[key] = L.markerClusterGroup({
                maxClusterRadius: 60,
                chunkedLoading: true,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                removeOutsideVisibleBounds: true
            });
        }

        return layerGroups[key];
    }

    function featureValue(props, kode) {
        if (!props || !props.dataspasial) {
            return null;
        }

        var ds = props.dataspasial;
        if (ds.nilai_map && Object.prototype.hasOwnProperty.call(ds.nilai_map, kode)) {
            return ds.nilai_map[kode];
        }

        if (ds.isian_kode === kode) {
            return ds.nilai;
        }

        return null;
    }

    function isianMeta(kode) {
        var list = (meta.popup_isian || []).concat(meta.filters || []);
        for (var i = 0; i < list.length; i++) {
            if (list[i] && list[i].kode === kode) {
                return list[i];
            }
        }
        return null;
    }

    function popupContent(props) {
        props = props || {};
        var html = '<div class="peta-popup">';

        if (props.nama) {
            html += '<strong>' + esc(props.nama) + '</strong><br>';
        }

        if (props.sektor_nama || props.sektor_kode) {
            html += '<small>Sektor: ' + esc(props.sektor_nama || props.sektor_kode) + '</small><br>';
        } else if (props.kategori) {
            html += '<small>' + esc(props.kategori) + '</small><br>';
        }

        if (props.nama_desa || props.kode_desa) {
            html += '<small>' + esc(props.nama_desa || props.kode_desa) + '</small><br>';
        } else if (props.kode_kecamatan) {
            html += '<small>Kec. ' + esc(props.kode_kecamatan) + '</small><br>';
        }

        var popupIsian = meta.popup_isian || [];
        for (var i = 0; i < popupIsian.length; i++) {
            var item = popupIsian[i];
            var value = featureValue(props, item.kode);
            var display = (value === null || value === undefined || value === '')
                ? (props.dataspasial ? 'Belum ada data untuk periode ini' : '-')
                : value;
            var satuan = item.satuan ? ' ' + esc(item.satuan) : '';

            html += '<div>' + esc(item.label || item.kode) + ': <strong>' + esc(display) + '</strong>' + satuan + '</div>';
        }

        var ds = props.dataspasial || {};
        var sumber = ds.sumber || ds.sumber_data || (props.source === 'presisi' ? 'Data Presisi' : null);
        if (sumber) {
            html += '<small>Sumber: ' + esc(sumber) + '</small><br>';
        }
        if (ds.waktu_input || ds.tanggal_pembaruan) {
            html += '<small>Diperbarui: ' + esc(ds.waktu_input || ds.tanggal_pembaruan) + '</small>';
        }
        if (ds.periode) {
            html += '<small>Periode: ' + esc(ds.periode) + '</small>';
        }

        html += '</div>';

        return html;
    }

    function addMarker(feature) {
        var coords = feature.geometry.coordinates;
        var key = layerKeyOf(feature.properties);

        var marker = L.marker([coords[1], coords[0]], markerIcon ? {icon: markerIcon} : {});
        marker.bindPopup(popupContent(feature.properties || {}), {closeButton: true, autoPan: true});

        marker.on('mouseover', function () {
            this.openPopup();
        });

        clusterFor(key).addLayer(marker);
    }

    function removeAllGroups() {
        Object.keys(layerGroups).forEach(function (key) {
            if (map.hasLayer(layerGroups[key])) {
                map.removeLayer(layerGroups[key]);
            }
        });
    }

    function syncActiveGroups() {
        removeAllGroups();
        Object.keys(activeLayers).forEach(function (key) {
            if (activeLayers[key] && layerGroups[key]) {
                map.addLayer(layerGroups[key]);
            }
        });
    }

    function renderLayerPanel() {
        var container = el('map-layers');
        if (!container) {
            return;
        }

        var groups = {legacy: [], data: []};
        (meta.layers || []).forEach(function (layer) {
            var kind = layer.jenis === 'dataspasial' ? 'data' : 'legacy';
            groups[kind].push(layer);
        });

        var html = '';
        html += '<div class="layer-group"><strong>Geospasial Lama</strong>';
        html += layerItemsHtml(groups.legacy, 'legacy');
        html += '</div>';

        html += '<div class="layer-group mt-2"><strong>DataSpasial</strong>';
        html += layerItemsHtml(groups.data, 'data');
        html += '</div>';

        container.innerHTML = html;

        container.querySelectorAll('input[type=checkbox][data-layer]').forEach(function (input) {
            input.addEventListener('change', function () {
                var key = input.getAttribute('data-layer');
                activeLayers[key] = input.checked;
                syncActiveGroups();
            });
        });
    }

    function layerItemsHtml(layers, kind) {
        if (layers.length === 0) {
            return '<div class="text-muted small">Tidak ada lapisan.</div>';
        }

        return layers.map(function (layer) {
            var key = (kind === 'data' ? DATA_LAYER_PREFIX : LEGACY_LAYER_PREFIX) + layer.key;
            var checked = activeLayers[key] ? ' checked' : '';

            return '<div class="form-check">' +
                '<input class="form-check-input" type="checkbox" data-layer="' + esc(key) + '"' + checked + ' id="layer-' + esc(key) + '">' +
                '<label class="form-check-label" for="layer-' + esc(key) + '">' + esc(layer.label) + ' (' + layer.jumlah + ')</label>' +
                '</div>';
        }).join('');
    }

    function quantile(values, q) {
        if (values.length === 0) {
            return null;
        }
        var pos = (values.length - 1) * q;
        var base = Math.floor(pos);
        var rest = pos - base;
        if (values[base + 1] !== undefined) {
            return values[base] + rest * (values[base + 1] - values[base]);
        }
        return values[base];
    }

    function buildBreaks(values) {
        var sorted = values.slice().sort(function (a, b) { return a - b; });
        var breaks = [];
        for (var i = 1; i < SCALE_COLORS.length; i++) {
            breaks.push(quantile(sorted, i / SCALE_COLORS.length));
        }
        return breaks;
    }

    function colorFor(value, breaks) {
        for (var i = 0; i < breaks.length; i++) {
            if (value <= breaks[i]) {
                return SCALE_COLORS[i];
            }
        }
        return SCALE_COLORS[SCALE_COLORS.length - 1];
    }

    function applyThematic() {
        if (boundaryLayer) {
            boundaryLayer.clearLayers();
        }

        if (!thematic || !thematic.kode) {
            renderPolygons(null);
            renderLegend();
            return;
        }

        var values = [];
        polygons.forEach(function (feature) {
            var value = feature.properties ? feature.properties.nilai : null;
            if (value !== null && value !== undefined && value !== '' && isFinite(Number(value))) {
                values.push(Number(value));
            }
        });

        var breaks = buildBreaks(values);
        renderPolygonLayer(breaks);
        renderLegend(breaks);
    }

    function renderPolygons() {
        if (!boundaryLayer) {
            return;
        }
        boundaryLayer.clearLayers();
        polygons.forEach(function (feature) {
            boundaryLayer.addLayer(polygonLayer(feature));
        });
    }

    function polygonHasValue(feature) {
        var value = feature.properties && feature.properties.nilai;
        return value !== null && value !== undefined && value !== '';
    }

    function polygonStyle(feature) {
        var hasValue = polygonHasValue(feature) && isFinite(Number(feature.properties.nilai));
        if (!thematic) {
            return {color: '#5a6268', weight: 1, fillOpacity: 0.2, fillColor: NO_DATA_COLOR, dashArray: '4,3'};
        }

        if (!hasValue) {
            return {color: '#5a6268', weight: 1, fillOpacity: 0.35, fillColor: NO_DATA_COLOR, dashArray: '4,3'};
        }

        return {
            color: '#5a6268',
            weight: 1,
            fillOpacity: 0.65,
            fillColor: colorFor(Number(feature.properties.nilai), thematic.breaks)
        };
    }

    function polygonLayer(feature) {
        return L.geoJSON(feature, {
            style: function () { return polygonStyle(feature); }
        });
    }

    function legendHtml() {
        if (!thematic) {
            return '';
        }

        var html = '<strong>' + esc(thematic.label) + '</strong>';
        if (thematic.satuan) {
            html += ' <small>(' + esc(thematic.satuan) + ')</small>';
        }
        html += '<div class="d-flex mt-1">';
        SCALE_COLORS.forEach(function (color) {
            html += '<span style="display:inline-block;width:16px;height:12px;background:' + color + ';"></span>';
        });
        html += '</div>';
        html += '<div class="mt-1"><span style="display:inline-block;width:16px;height:12px;background:' + NO_DATA_COLOR + ';border:1px dashed #5a6268;"></span> Tanpa Data</div>';

        return html;
    }

    function renderLegend() {
        var node = el('map-legend');
        if (!node) {
            return;
        }

        if (!thematic) {
            node.style.display = 'none';
            node.innerHTML = '';
            return;
        }

        node.innerHTML = legendHtml();
        node.style.display = '';
    }

    function clearMap() {
        Object.keys(layerGroups).forEach(function (key) {
            layerGroups[key].clearLayers();
        });
        layerGroups = {};
        markerMeta = [];
        polygons = [];
        if (boundaryLayer) {
            boundaryLayer.clearLayers();
        }
    }

    function render(geojson, newMeta) {
        meta = newMeta || {};
        init();

        clearMap();

        var features = (geojson && geojson.features) || [];
        features.forEach(function (feature) {
            if (!feature || feature.type !== 'Feature') {
                return;
            }
            if (isPoint(feature.geometry)) {
                addMarker(feature);
            } else if (isPolygon(feature.geometry)) {
                polygons.push(feature);
            }
        });

        (meta.layers || []).forEach(function (layer) {
            var prefix = layer.jenis === 'dataspasial' ? DATA_LAYER_PREFIX : LEGACY_LAYER_PREFIX;
            var key = prefix + layer.key;
            if (!(key in activeLayers)) {
                activeLayers[key] = true;
            }
        });

        renderLayerPanel();
        syncActiveGroups();

        if (polygons.length > 0) {
            renderThematicPolygons();
        }

        show('map-empty', features.length === 0);
    }

    function renderThematicPolygons() {
        polygons.forEach(function (feature) {
            var styled = polygonLayer(feature);
            styled.eachLayer(function (layer) {
                if (feature.properties && (feature.properties.nama_desa || feature.properties.nama || feature.properties.kode_desa)) {
                    var label = feature.properties.nama_desa || feature.properties.nama || feature.properties.kode_desa;
                    var value = thematic && polygonHasValue(feature) ? feature.properties.nilai : 'Tanpa Data';
                    layer.bindPopup('<strong>' + esc(label) + '</strong><br>' + esc(value));
                }
            });
            boundaryLayer.addLayer(styled);
        });
    }

    function setThematic(option) {
        if (!option || !option.value) {
            thematic = null;
        } else {
            var values = [];
            polygons.forEach(function (feature) {
                var raw = featureValue(feature.properties, option.value);
                if (raw === null || raw === undefined) {
                    raw = feature.properties && feature.properties.nilai;
                }
                if (raw !== null && raw !== undefined && raw !== '' && isFinite(Number(raw))) {
                    values.push(Number(raw));
                }
            });
            thematic = {
                kode: option.value,
                label: option.label || option.value,
                satuan: option.satuan || null,
                breaks: buildBreaks(values)
            };
        }

        if (polygons.length > 0) {
            renderThematicPolygons();
        }
        renderLegend();
    }

    function renderUnavailable(message) {
        show('map-empty', false);
        show('map-unavailable', true);
        if (message) {
            setHtml('map-unavailable', '<h5 class="alert-heading">Data sementara tidak tersedia</h5><p class="mb-0">' + esc(message) + '</p>');
        }
    }

    function renderEmpty() {
        show('map-unavailable', false);
        show('map-empty', true);
    }

    window.PetaGabungan = {
        init: init,
        render: render,
        setThematic: setThematic,
        unavailable: renderUnavailable,
        empty: renderEmpty
    };
})(window);
