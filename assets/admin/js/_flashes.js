const bootstrap = require('bootstrap');

(function () {
    let toastList = [].slice.call(document.querySelectorAll('#flash-container .alert'));
    toastList.map(function (toastEl) {
        let options = {
            delay: 10000,
        };
        return (new bootstrap.Toast(toastEl, options)).show();
    });
})();