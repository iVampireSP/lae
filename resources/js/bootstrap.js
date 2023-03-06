import _ from 'lodash';
import 'bootstrap';

import './color-mode';

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
import axios from 'axios';

import 'bootstrap/js/dist/popover';
import 'bootstrap/js/dist/tooltip';
import 'bootstrap-icons/font/bootstrap-icons.css';

window._ = _;

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

// 根据 URL 自动激活导航栏
const navLinks = document.querySelectorAll('#nav .nav-link, #nav .dropdown-item');
const currentUrl = window.location.href;

for (let i = 0; i < navLinks.length; i++) {
    const navLink = navLinks[i];
    if (currentUrl === navLink.href) {
        navLink.classList.add('active');
    }
}
