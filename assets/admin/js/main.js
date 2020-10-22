const bootstrap = require('bootstrap');
import './_charts';
import './_flashes';

import '../scss/main.scss';

(function () {
    let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        let options = {
            placement: 'auto',
        };
        return new bootstrap.Tooltip(tooltipTriggerEl, options);
    });
})();