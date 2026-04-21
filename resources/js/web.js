import $ from 'jquery';
import '../vendor/bootstrap-5.3.2/js/bootstrap.bundle.min';
import 'chart.js/dist/Chart.min';
import 'select2/dist/js/select2.full.min';
import { filter } from 'lodash';

window.$ = $;
window.jQuery = $;
window.bootstrap = bootstrap;
$.ajaxSetup({
    beforeSend: function (xhr, settings) {
        const identitasOpenkab = $('meta[name="identitas-openkab"]').attr('content');
        if (settings.url.indexOf('?') === -1) {
            settings.url += '?kode_kabupaten=' + identitasOpenkab+'&filter[kode_kabupaten]=' + identitasOpenkab;
        } else {
            settings.url += '&kode_kabupaten=' + identitasOpenkab+'&filter[kode_kabupaten]=' + identitasOpenkab;
        }
    }
});
