import _ from 'lodash';
import $ from 'jquery';
import * as bootstrap from 'bootstrap';
import 'admin-lte/dist/js/adminlte.min';
// import {popper} from "@popperjs/core";
// import 'alpinejs/dist/cdn.min';
// import Alpine from 'alpinejs';
// import './alpinejs-csp/csp';
import Swal from 'sweetalert2/dist/sweetalert2.min';
// import 'select2/dist/js/select2.full.min';
// import 'datatables.net/js/jquery.dataTables.min';
// import 'datatables.net-bs5/js/dataTables.bootstrap5.min';
// import 'datatables.net-responsive-bs5/js/responsive.bootstrap5.min';

window._ = _;
window.$ = $;
window.jQuery = $;
window.bootstrap = bootstrap
window.Swal = Swal;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });

