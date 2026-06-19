import $ from 'jquery';
import '../vendor/bootstrap-5.3.2/js/bootstrap.bundle.min';
import 'chart.js/dist/Chart.min';
import select2 from 'select2/dist/js/select2.full.min';
if (typeof select2 === 'function') select2(window, $);
import { filter } from 'lodash';

window.$ = $;
window.jQuery = $;
window.bootstrap = bootstrap;

// Definisikan config di top-level atau import dari config file
const AJAX_PARAMS_CONFIG = {
    metaTagName: 'identitas-openkab',
    paramNames: ['kode_kabupaten', 'filter[kode_kabupaten]']
};
const identitasOpenkab = $(`meta[name="${AJAX_PARAMS_CONFIG.metaTagName}"]`).attr('content') || '';
$.ajaxSetup({
    beforeSend: function (xhr, settings) {
        if (!identitasOpenkab) {
            console.warn(`Meta tag ${AJAX_PARAMS_CONFIG.metaTagName} tidak ditemukan`);
            return;
        }

        // Validasi format kode kabupaten (hanya angka, 4-6 digit)
        if (!identitasOpenkab || !/^\d{4}$/.test(identitasOpenkab)) {
            console.error('Invalid kode_kabupaten format');
            return; // Abort jika tidak valid
        }

        // Encode value sebelum ditambahkan ke URL
        const encodedValue = encodeURIComponent(identitasOpenkab);

        const params = AJAX_PARAMS_CONFIG.paramNames
            .map(name => `${name}=${encodedValue}`)
            .join('&');

        settings.url += settings.url.indexOf('?') === -1 ? `?${params}` : `&${params}`;
    }
});
