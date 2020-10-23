const bootstrap = require('bootstrap');

(function () {
    /**
     * Load the alerts in the default flash container as bootstrap Toasts.
     */
    let toastList = [].slice.call(document.querySelectorAll('#flash-container .alert'));
    toastList.map(function (toastEl) {
        let options = {
            delay: 10000,
        };
        return (new bootstrap.Toast(toastEl, options)).show();
    });
})();