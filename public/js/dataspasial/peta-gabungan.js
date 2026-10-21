(function (window) {
    'use strict';

    var map = null;
    var boundaryLayer = null;
    var layerGroups = {};
    var activeLayers = {};
    var polygons = [];
    var meta = {};
    var markerIcon = null;
    var layersControl = null;

    var NO_DATA_COLOR = '#d2d6de';
    var LEGACY_LAYER_PREFIX = 'legacy:';
    var DATA_LAYER_PREFIX = 'data:';
    var DEFAULT_CENTER = [-2.5, 118];
    var DEFAULT_ZOOM = 5;
    var MAX_FIT_ZOOM = 15;
    var markerBounds = {};
    var boundaryBounds = null;

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
        map = L.map(mapId).setView(DEFAULT_CENTER, DEFAULT_ZOOM);

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

        var baseLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        if (!layersControl) {
            layersControl = L.control.layers({ 'OpenStreetMap': baseLayer }, {}, { collapsed: false });
            layersControl.addTo(map);
        }

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

        if (!markerBounds[key]) {
            markerBounds[key] = L.latLngBounds([]);
        }
        markerBounds[key].extend([coords[1], coords[0]]);

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

    function visibleBounds() {
        var bounds = L.latLngBounds([]);

        if (boundaryBounds && boundaryBounds.isValid()) {
            bounds.extend(boundaryBounds);
        }

        Object.keys(activeLayers).forEach(function (key) {
            if (activeLayers[key] && markerBounds[key] && markerBounds[key].isValid()) {
                bounds.extend(markerBounds[key]);
            }
        });

        return bounds;
    }

    function fitToVisible() {
        var bounds = visibleBounds();

        if (bounds.isValid()) {
            map.fitBounds(bounds, {padding: [24, 24], maxZoom: MAX_FIT_ZOOM});
        }
    }

    function layerLabel(key) {
        if (key.indexOf(DATA_LAYER_PREFIX) === 0) {
            return key.substring(DATA_LAYER_PREFIX.length);
        }
        if (key.indexOf(LEGACY_LAYER_PREFIX) === 0) {
            return key.substring(LEGACY_LAYER_PREFIX.length);
        }
        return key;
    }

    function syncActiveGroups() {
        if (!map) {
            return;
        }
        if (layersControl) {
            try { layersControl.remove(); } catch (e) { /* noop */ }
            layersControl = null;
        }
        var overlays = {};
        Object.keys(layerGroups).forEach(function (key) {
            if (layerGroups[key]) {
                var label = layerLabel(key);
                overlays[label] = layerGroups[key];
            }
        });
        if (Object.keys(overlays).length) {
            layersControl = L.control.layers({}, overlays, { collapsed: false });
            layersControl.addTo(map);
            Object.keys(layerGroups).forEach(function (key) {
                if (layerGroups[key] && activeLayers[key] !== false && !map.hasLayer(layerGroups[key])) {
                    map.addLayer(layerGroups[key]);
                }
            });
        } else {
            Object.keys(layerGroups).forEach(function (key) {
                if (layerGroups[key] && !map.hasLayer(layerGroups[key])) {
                    map.addLayer(layerGroups[key]);
                }
            });
        }

        fitToVisible();
    }

    function renderLayerPanel() {
        var container = el('map-layers');
        if (!container) {
            return;
        }

        var groups = {legacy: [], data: [], dataspasial: [], kategori: []};
        (meta.layers || []).forEach(function (layer) {            
            var jenis = layer.jenis || '';
            if (jenis === 'dataspasial' || jenis === 'dataspasial_layer' || jenis === 'sektor') {
                groups.dataspasial.push(layer);
                groups.data.push(layer);
            } else if (jenis === 'kategori') {
                groups.kategori.push(layer);
            } else {
                groups.legacy.push(layer);
            }
        });

        var html = '';
        if (groups.dataspasial.length) {
            html += '<div class="layer-group"><strong>Layer DataSpasial</strong>';
            html += layerItemsHtml(groups.dataspasial, 'data');
            html += '</div>';
        }
        if (groups.kategori.length) {
            html += '<div class="layer-group mt-2"><strong>Kategori</strong>';
            html += layerItemsHtml(groups.kategori, 'legacy');
            html += '</div>';
        }
        html += '<div class="layer-group mt-2"><strong>Geospasial Lama</strong>';
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

    function polygonLayer(feature) {
        return L.geoJSON(feature, {
            style: {
                color: '#5a6268',
                weight: 1,
                opacity: 0.9,
                fillOpacity: 0.25,
                fillColor: NO_DATA_COLOR
            },
            onEachFeature: function (f, layer) {
                var props = f.properties || {};
                var label = props.nama_desa || props.nama || props.kode_desa;
                if (label) {
                    layer.bindPopup('<strong>' + esc(label) + '</strong>');
                }
            }
        });
    }

    function clearMap() {
        Object.keys(layerGroups).forEach(function (key) {
            layerGroups[key].clearLayers();
        });
        layerGroups = {};
        polygons = [];
        markerBounds = {};
        boundaryBounds = null;
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

        polygons.forEach(function (feature) {
            var layer = polygonLayer(feature);
            boundaryLayer.addLayer(layer);

            var bounds = layer.getBounds();
            if (bounds.isValid()) {
                if (!boundaryBounds) {
                    boundaryBounds = L.latLngBounds([]);
                }
                boundaryBounds.extend(bounds);
            }
        });

        if (features.length === 0) {
            map.setView(DEFAULT_CENTER, DEFAULT_ZOOM);
        }

        renderLayerPanel();
        syncActiveGroups();

        show('map-empty', features.length === 0);
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
        unavailable: renderUnavailable,
        empty: renderEmpty
    };
})(window);
