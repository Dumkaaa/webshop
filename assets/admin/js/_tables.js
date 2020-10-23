(function () {
    /**
     * Make a whole table row work as a link.
     */
    document.getElementsByClassName('table-row-link').forEach(function(row) {
        row.addEventListener('click', function (event) {
            if (event.target.href) {
                // The user clicked on a link within the table row, ignore.
                return;
            }

            window.location.href = row.dataset.href;
        });
    });
})();