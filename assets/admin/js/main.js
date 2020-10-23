const bootstrap = require('bootstrap');
import './_charts';
import './_flashes';
import './_tables';

import '../scss/main.scss';

(function () {
    /**
     * Bootstrap tooltips.
     */
    let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        let options = {
            placement: 'auto',
        };
        return new bootstrap.Tooltip(tooltipTriggerEl, options);
    });
})();