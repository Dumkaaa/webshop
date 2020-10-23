(function () {
    /**
     * Make a whole table row work as a link.
     */
    document.getElementsByClassName('table-row-link').forEach(function (row) {
        row.addEventListener('click', function (event) {
            if (event.target instanceof HTMLAnchorElement  || event.target instanceof HTMLInputElement || event.target.classList.contains('no-click')) {
                // The user clicked on an anchor field, input field or field with the no-click class within the table row, ignore.
                return;
            }

            window.location.href = row.dataset.href;
        });
    });

    /**
     * Handle table checkboxes.
     */
    document.getElementsByClassName('table-checkbox').forEach(function (table) {
        const actionRow = table.getElementsByClassName('checkbox-action-row')[0];
        const actionAnchors = actionRow.getElementsByTagName('a');
        const countSpan = actionRow.getElementsByClassName('count')[0];
        const headerCheckboxes = table.querySelectorAll('thead input[type="checkbox"]');
        const headerCheckbox = headerCheckboxes.length > 0 ? headerCheckboxes[0] : null;
        const bodyCheckboxes = table.querySelectorAll('tbody input[type="checkbox"]');

        // Hide the header checkbox if there are no body checkboxes.
        if (headerCheckbox && bodyCheckboxes.length === 0) {
            headerCheckbox.remove();
        }

        // Handle thead checkbox clicks.
        if (headerCheckbox) {
            headerCheckbox.addEventListener('click', function () {
                bodyCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = headerCheckbox.checked;
                });

                updateTableActions();
            });
        }

        // Handle tbody checkbox clicks.
        bodyCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('click', updateTableActions);
        });

        // Update the actions row.
        function updateTableActions() {
            // Index actions.
            let actions = {};
            let actionNames = [];
            actionAnchors.forEach(function (anchor) {
                const action = anchor.dataset.action;
                if (action) {
                    actions[action] = false;
                    actionNames.push(action);
                }
            });

            // Index all values of the selected checkboxes.
            let selectedValues = [];
            bodyCheckboxes.forEach(function (checkbox) {
                if (checkbox.checked) {
                    selectedValues.push(checkbox.dataset.value);
                    actionNames.forEach(function (action) {
                        const actionValue = checkbox.dataset['action'+action.charAt(0).toUpperCase() + action.slice(1)];
                        if (actionValue === 'true') {
                            actions[action] = true;
                        }
                    });
                }
            });

            // Update the header checkbox state (unchecked, checked, indeterminate).
            if (headerCheckbox) {
                headerCheckbox.checked = selectedValues.length === bodyCheckboxes.length;
                headerCheckbox.indeterminate = selectedValues.length > 0 && selectedValues.length !== bodyCheckboxes.length;
            }

            // Update the action row visibility and count.
            actionRow.classList.toggle('show', selectedValues.length > 0);
            countSpan.innerHTML = selectedValues.length;

            actionAnchors.forEach(function (anchor) {
                const action = anchor.dataset.action;
                if (action) {
                    anchor.classList.toggle('disabled', !actions[action]);
                }

                let href = anchor.dataset.baseHref;
                if (!href) {
                    // This is not an action with a base href, ignore.
                    return;
                }

                href += '?values=' + selectedValues.join(',');
                anchor.href = href;
            });
        }
    });
})();