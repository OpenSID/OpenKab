import _ from 'lodash';
import $ from 'jquery';
import * as bootstrap from 'bootstrap';
import 'admin-lte/dist/js/adminlte.min';
import 'alpinejs/dist/cdn.min';
import Swal from 'sweetalert2/dist/sweetalert2.min';
import 'datatables.net/js/jquery.dataTables.mjs';
import 'datatables.net-bs4/js/dataTables.bootstrap4.mjs';
import 'datatables.net-responsive/js/dataTables.responsive.mjs';
import 'datatables.net-responsive-bs4/js/responsive.bootstrap4.mjs';
import 'datatables.net-select/js/dataTables.select.mjs';
import 'datatables.net-select-bs4/js/select.bootstrap4.mjs';
import 'daterangepicker/daterangepicker';
import 'chart.js/dist/Chart.min';
import 'bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min';
import 'moment/min/moment.min';
import './jsvalidation.min';

// Load select2 (import via CJS bridge to avoid Rollup CJS detection issues)
import select2Factory from './select2-bridge';
if (typeof select2Factory === 'function') select2Factory(window, $);

window.isEmpty = _.isEmpty;
window._ = _;
window.$ = $;
window.jQuery = $;
window.bootstrap = bootstrap;
window.Swal = Swal;
window.moment = moment;
/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
