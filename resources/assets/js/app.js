window.app_env = {
    enable_messenger: process.env.MIX_ENABLE_MESSENGER === 'true',
    enable_skd: process.env.MIX_ENABLE_SKD === 'true'
};

window.userId = $("meta[name=user-id]").attr('content');

window._ = require('lodash');

// Предварительная настройка moment
let moment = require('moment');
moment.locale('uk');
window.moment = moment;

// Библиотека для обработки HTTP запросов
window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}


require('./bootstrap');
require('./vue');
require('./broadcasting');



