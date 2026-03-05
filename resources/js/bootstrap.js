import _ from 'lodash';
import $ from 'jquery';
import * as bootstrap from 'bootstrap';
import 'admin-lte/dist/js/adminlte.min';
import 'alpinejs/dist/cdn.min';
import Swal from 'sweetalert2/dist/sweetalert2.min';
import 'select2/dist/js/select2.full.min';
import 'datatables.net/js/jquery.dataTables.min';
import 'datatables.net-bs4/js/dataTables.bootstrap4.min';
import 'datatables.net-responsive-bs4/js/responsive.bootstrap4.min';
import 'datatables.net-select-bs4/js/select.bootstrap4.min';
import 'daterangepicker/daterangepicker';
import 'chart.js/dist/Chart.min';
import 'bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min';
import 'moment/min/moment.min';
import './jsvalidation.min';

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
