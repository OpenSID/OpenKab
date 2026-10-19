(function (window) {
    'use strict';

    var map = null;
    var cluster = null;
    var boundaryLayer = null;

    var NO_DATA_COLOR = '#d2d6de';
    var SCALE_COLORS = ['#c6dbef', '#9ecae1', '#6baed6', '#3182bd', '#08519c'];

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

    function init(mapId) {
        if (map) {
            return map;
        }

        map = L.map(mapId || 'map').setView([-2.5, 118], 5);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        cluster = L.markerClusterGroup({
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            maxClusterRadius: 80
        });
        boundaryLayer = L.layerGroup();

        map.addLayer(cluster);
        map.addLayer(boundaryLayer);

        return map;
    }

    function isPoint(geometry) {
        return geometry && geometry.type === 'Point' &&
            Array.isArray(geometry.coordinates) && geometry.coordinates.length >= 2;
    }

    function isPolygon(geometry) {
        return geometry && (geometry.type === 'Polygon' || geometry.type === 'MultiPolygon');
    }

    function datum(props, key) {
        return props && props.dataspasial ? props.dataspasial[key] : null;
    }

    function hasData(props) {
        var value = datum(props, 'nilai');
        return props && props.dataspasial && value !== null && value !== undefined && value !== '';
    }

    function popupContent(props) {
        props = props || {};
        var html = '<div class="peta-popup">';

        if (props.nama) {
            html += '<strong>' + esc(props.nama) + '</strong><br>';
        }
        if (props.sektor_nama || props.sektor_kode) {
            html += '<small>Sektor: ' + esc(props.sektor_nama || props.sektor_kode) + '</small><br>';
        }
        if (props.nama_desa || props.kode_desa) {
            html += '<small>' + esc(props.nama_desa || props.kode_desa) + '</small><br>';
        } else if (props.kode_kecamatan) {
            html += '<small>Kec. ' + esc(props.kode_kecamatan) + '</small><br>';
        }

        if (props.dataspasial) {
            var label = datum(props, 'isian_label') || datum(props, 'isian_kode') || 'Nilai';
            var satuan = datum(props, 'isian_satuan') ? ' ' + esc(datum(props, 'isian_satuan')) : '';

            html += '<hr class="my-1">';
            html += '<div>' + esc(label) + ': <strong>' + esc(datum(props, 'nilai')) + '</strong>' + satuan + '</div>';

            if (datum(props, 'periode')) {
                html += '<small>Periode: ' + esc(datum(props, 'periode')) + '</small><br>';
            }
            if (datum(props, 'sumber') || datum(props, 'sumber_data')) {
                html += '<small>Sumber: ' + esc(datum(props, 'sumber') || datum(props, 'sumber_data')) + '</small><br>';
            }
            if (datum(props, 'waktu_input') || datum(props, 'tanggal_pembaruan')) {
                html += '<small>Diperbarui: ' + esc(datum(props, 'waktu_input') || datum(props, 'tanggal_pembaruan')) + '</small>';
            }
        } else if (props.nilai !== undefined) {
            var value = (props.nilai === null || props.nilai === '') ? 'Tanpa Data' : props.nilai;
            html += '<hr class="my-1"><div>Nilai: <strong>' + esc(value) + '</strong></div>';
        }

        html += '</div>';

        return html;
    }

    function clusterMarker(feature) {
        var coords = feature.geometry.coordinates;
        var marker = L.marker([coords[1], coords[0]]);
        marker.bindPopup(popupContent(feature.properties || {}));
        cluster.addLayer(marker);
    }

    function renderPolygon(feature) {
        var value = feature.properties && feature.properties.nilai;
        var hasValue = value !== null && value !== undefined && value !== '';

        return L.geoJSON(feature, {
            style: {
                color: '#5a6268',
                weight: 1,
                opacity: 0.9,
                fillOpacity: hasValue ? 0.65 : 0.35,
                fillColor: hasValue ? colorForValue(Number(value)) : NO_DATA_COLOR,
                dashArray: hasValue ? null : '4,3'
            }
        });
    }

    function colorForValue(value) {
        if (!isFinite(value)) {
            return NO_DATA_COLOR;
        }
        return SCALE_COLORS[SCALE_COLORS.length - 1];
    }

    function buildLegend(meta) {
        var node = el('map-legend');
        if (!node) {
            return;
        }

        if (!meta || !meta.isian_tematik) {
            node.style.display = 'none';
            node.innerHTML = '';
            return;
        }

        var isian = meta.isian_tematik;
        var html = '<strong>' + esc(isian.label || isian.kode) + '</strong>';
        if (isian.satuan) {
            html += ' <small>(' + esc(isian.satuan) + ')</small>';
        }
        html += '<div style="display:flex;align-items:center;margin-top:4px;">';
        for (var i = 0; i < SCALE_COLORS.length; i++) {
            html += '<span style="display:inline-block;width:18px;height:12px;background:' + SCALE_COLORS[i] + ';"></span>';
        }
        html += '</div>';
        html += '<div><span style="display:inline-block;width:18px;height:12px;background:' + NO_DATA_COLOR +
            ';border:1px dashed #5a6268;"></span> Tanpa Data</div>';

        node.innerHTML = html;
        node.style.display = '';
    }

    function renderUnavailable(message) {
        show('map-empty', false);
        show('map-unavailable', true);
        if (message) {
            setHtml(
                'map-unavailable',
                '<h5 class="alert-heading">Data sementara tidak tersedia</h5><p class="mb-0">' + esc(message) + '</p>'
            );
        }
    }

    function renderEmpty() {
        show('map-unavailable', false);
        show('map-empty', true);
    }

    function render(geojson, meta) {
        init('map');
        show('map-unavailable', false);

        if (cluster) {
            cluster.clearLayers();
        }
        if (boundaryLayer) {
            boundaryLayer.clearLayers();
        }

        var features = (geojson && geojson.features) || [];
        var points = [];

        for (var i = 0; i < features.length; i++) {
            var feature = features[i];
            if (!feature || feature.type !== 'Feature') {
                continue;
            }
            if (isPoint(feature.geometry)) {
                points.push(feature);
            } else if (isPolygon(feature.geometry)) {
                boundaryLayer.addLayer(renderPolygon(feature));
            }
        }

        for (var j = 0; j < points.length; j++) {
            clusterMarker(points[j]);
        }

        if (features.length === 0) {
            renderEmpty();
        } else {
            show('map-empty', false);
        }

        buildLegend(meta);
    }

    window.PetaGabungan = {
        init: function (options) {
            init(options && options.mapId);
        },
        render: render,
        unavailable: renderUnavailable,
        empty: renderEmpty
    };
})(window);
