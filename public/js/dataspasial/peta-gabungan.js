(function (window, L, $) {
    'use strict';

    var map, markerCluster, boundaryLayer, pointLayers = {};
    var featureData = null;
    var lastRequestId = 0;
    var selected = {
        sektor_id: null,
        periode: null,
        isian_tematik: null,
        filters: {}
    };

    function noop() {}

    function renderUnavailable(containerId) {
        var $container = $('#' + (containerId || 'map'));
        if (!$container.length) {
            return;
        }
        $container.html(
            '<div class="alert alert-warning" data-testid="dataspasial-unavailable" role="alert">' +
            '<h5 class="alert-heading">Data sementara tidak tersedia</h5>' +
            '<p class="mb-0">Silakan coba lagi nanti.</p>' +
            '</div>'
        );
    }

    function renderEmpty(containerId) {
        var $container = $('#' + (containerId || 'map'));
        if (!$container.length) {
            return;
        }
        $container.html(
            '<div class="alert alert-info" role="alert">' +
            '<p class="mb-0">Tidak ada data untuk pilihan ini.</p>' +
            '</div>'
        );
    }

    function initMap(mapId) {
        if (map) {
            return map;
        }
        mapId = mapId || 'map';
        map = L.map(mapId).setView([-2.5, 118], 5);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        markerCluster = L.markerClusterGroup({
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            maxClusterRadius: 80
        });
        map.addLayer(markerCluster);
        boundaryLayer = L.layerGroup();
        map.addLayer(boundaryLayer);
        return map;
    }

    function clearLayers() {
        if (markerCluster) {
            markerCluster.clearLayers();
        }
        if (boundaryLayer) {
            boundaryLayer.clearLayers();
        }
    }

    function buildPopupContent(props, isianMeta) {
        var html = [];
        html.push('<div class="peta-popup">');
        if (props.nama) {
            html.push('<strong>' + String(props.nama).replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</strong><br>');
        }
        if (props.sektor_nama) {
            html.push('<small>' + String(props.sektor_nama).replace(/&/g, '&amp;') + '</small><br>');
        }
        if (props.kode_desa) {
            html.push('<small>Desa/Kode: ' + String(props.kode_desa).replace(/&/g, '&amp;') + '</small><br>');
        }
        html.push('</div>');
        return html.join('');
    }

    function renderFeatures(features) {
        clearLayers();
        if (!Array.isArray(features) || features.length === 0) {
            return;
        }
        for (var i = 0; i < features.length; i++) {
            var f = features[i];
            if (!f || f.type !== 'Feature') {
                continue;
            }
            var geom = f.geometry;
            var props = f.properties || {};
            if (geom && geom.type === 'Point' && Array.isArray(geom.coordinates) && geom.coordinates.length >= 2) {
                var lat = geom.coordinates[1], lng = geom.coordinates[0];
                if (isFinite(lat) && isFinite(lng)) {
                    var m = L.marker([lat, lng]);
                    m.bindPopup(buildPopupContent(props, null));
                    markerCluster.addLayer(m);
                }
            }
        }
    }

    window.PetaGabungan = {
        init: function (opts) {
            opts = opts || {};
            initMap(opts.mapId || 'map');
        },
        setData: function (data) {
            featureData = data;
            var fc = data;
            if (data && data.type === 'FeatureCollection') {
                fc = data;
            } else if (data && Array.isArray(data.features)) {
                fc = data;
            }
            renderFeatures(fc.features || []);
        },
        applyUnavailable: function () {
            renderUnavailable('map');
        },
        applyEmpty: function () {
            renderEmpty('map');
        },
        clear: clearLayers
    };

})(window, L, window.jQuery || window.$);
