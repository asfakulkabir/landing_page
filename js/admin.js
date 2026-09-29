/* Admin panel - raw JS, no libraries */
(function () {
    'use strict';

    /* ------------------------------------------------- sidebar (mobile) */
    var toggle = document.querySelector('[data-sidebar-toggle]');
    var sidebar = document.querySelector('[data-sidebar]');

    if (toggle && sidebar) {
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            sidebar.classList.toggle('is-open');
        });

        document.addEventListener('click', function (e) {
            if (window.innerWidth > 900) return;
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('is-open');
            }
        });
    }

    /* ------------------------------------------------- delete confirms */
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    /* --------------------------------------- inline order status updates */
    document.querySelectorAll('[data-status-form]').forEach(function (form) {
        var select = form.querySelector('[data-status-select]');
        if (!select) return;

        select.addEventListener('change', function () {
            /* repaint straight away: the page round-trips to the server, so the
               new colour should not wait for the response */
            form.dataset.status = select.value;
            form.submit();
        });
    });

    /* ------------------------------------------------- drag to reorder */
    var table = document.querySelector('[data-sortable]');
    var reorderForm = document.querySelector('[data-reorder-form]');

    if (table && reorderForm) {
        var dragged = null;

        Array.prototype.forEach.call(table.rows, function (row) {
            row.setAttribute('draggable', 'true');

            row.addEventListener('dragstart', function (e) {
                dragged = row;
                row.classList.add('is-dragging');
                e.dataTransfer.effectAllowed = 'move';
                try { e.dataTransfer.setData('text/plain', row.dataset.id || ''); } catch (err) {}
            });

            row.addEventListener('dragend', function () {
                row.classList.remove('is-dragging');
                Array.prototype.forEach.call(table.rows, function (r) { r.classList.remove('is-over'); });
                dragged = null;
            });

            row.addEventListener('dragover', function (e) {
                e.preventDefault();
                if (dragged && dragged !== row) row.classList.add('is-over');
            });

            row.addEventListener('dragleave', function () {
                row.classList.remove('is-over');
            });

            row.addEventListener('drop', function (e) {
                e.preventDefault();
                row.classList.remove('is-over');
                if (!dragged || dragged === row) return;

                var rows = Array.prototype.slice.call(table.rows);
                var from = rows.indexOf(dragged);
                var to = rows.indexOf(row);

                if (from < to) {
                    row.parentNode.insertBefore(dragged, row.nextSibling);
                } else {
                    row.parentNode.insertBefore(dragged, row);
                }
            });
        });

        reorderForm.addEventListener('submit', function () {
            var hidden = reorderForm.querySelector('input[name="order[]"]');
            var ids = Array.prototype.slice.call(table.rows).map(function (r) { return r.dataset.id; });

            ids.forEach(function (id) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'order[]';
                input.value = id;
                reorderForm.appendChild(input);
            });

            if (hidden) hidden.remove();
        });
    }
})();
