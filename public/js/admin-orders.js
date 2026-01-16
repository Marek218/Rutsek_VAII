// public/js/admin-orders.js
// - Sorting for the admin orders table (Rezervácie)

(function () {
    'use strict';

    function initAdminOrdersTable() {
        var table = document.querySelector('.table.table-striped.table-hover.align-middle');
        if (!table) return;

        var tbody = table.tBodies[0];
        if (!tbody) return;

        // Ensure each data row has data-datetime
        Array.prototype.slice.call(tbody.querySelectorAll('tr')).forEach(function (row) {
            var cells = row.querySelectorAll('td');
            if (!cells || cells.length < 6) return;

            if (row.dataset.datetime && row.dataset.datetime !== '') return;

            var dateText = (cells[4] && cells[4].textContent ? String(cells[4].textContent).trim() : '');
            var timeText = (cells[5] && cells[5].textContent ? String(cells[5].textContent).trim() : '');

            if (dateText) {
                row.dataset.datetime = dateText + 'T' + (timeText || '00:00');
            } else {
                row.dataset.datetime = '';
            }
        });

        // Make sortable headers clickable
        var headers = table.querySelectorAll('th');
        Array.prototype.slice.call(headers).forEach(function (th) {
            var txt = (th.textContent || '').trim().toLowerCase();
            if (txt === 'meno' || txt === 'dátum' || txt === 'datum') {
                th.classList.add('sortable');
                th.style.cursor = 'pointer';
                if (!th.querySelector('.sort-indicator')) {
                    var span = document.createElement('span');
                    span.className = 'sort-indicator';
                    span.textContent = '';
                    th.appendChild(span);
                }
            }
        });

        function getRows() {
            var all = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
            var dataRows = all.filter(function (r) { return r.dataset.datetime !== undefined && r.dataset.datetime !== ''; });
            var placeholders = all.filter(function (r) { return r.dataset.datetime === undefined || r.dataset.datetime === ''; });
            return { dataRows: dataRows, placeholders: placeholders };
        }

        function compareValues(a, b, asc) {
            if (a === b) return 0;
            return a < b ? (asc ? -1 : 1) : (asc ? 1 : -1);
        }

        var sortState = { key: 'datetime', asc: false };

        Array.prototype.slice.call(table.querySelectorAll('th.sortable')).forEach(function (th) {
            th.addEventListener('click', function () {
                var headerText = (th.textContent || '').trim().toLowerCase();
                var key = 'datetime';
                if (headerText === 'meno') key = 'name';
                if (headerText === 'dátum' || headerText === 'datum') key = 'datetime';

                if (sortState.key === key) {
                    sortState.asc = !sortState.asc;
                } else {
                    sortState.key = key;
                    sortState.asc = (key === 'name');
                }

                // Reset indicators
                Array.prototype.slice.call(table.querySelectorAll('th.sortable')).forEach(function (h) {
                    try { delete h.dataset.order; } catch (e) {}
                    var ind = h.querySelector('.sort-indicator');
                    if (ind) ind.textContent = '';
                });

                th.dataset.order = sortState.asc ? 'asc' : 'desc';
                var curInd = th.querySelector('.sort-indicator');
                if (curInd) curInd.textContent = sortState.asc ? '▲' : '▼';

                var rows = getRows();
                rows.dataRows.sort(function (rA, rB) {
                    var vA, vB;
                    if (sortState.key === 'datetime') {
                        vA = rA.dataset.datetime ? Date.parse(rA.dataset.datetime) : 0;
                        vB = rB.dataset.datetime ? Date.parse(rB.dataset.datetime) : 0;
                    } else {
                        var tdA = rA.querySelector('td');
                        var tdB = rB.querySelector('td');
                        vA = (tdA && tdA.textContent ? String(tdA.textContent) : '').trim().toLowerCase();
                        vB = (tdB && tdB.textContent ? String(tdB.textContent) : '').trim().toLowerCase();
                    }
                    return compareValues(vA, vB, sortState.asc);
                });

                // Append back in order
                rows.dataRows.forEach(function (r) { tbody.appendChild(r); });
                rows.placeholders.forEach(function (r) { tbody.appendChild(r); });
            });
        });

        // Default sort by date
        var sortableHeaders = Array.prototype.slice.call(table.querySelectorAll('th.sortable'));
        var dateHeader = sortableHeaders.find(function (h) {
            var t = (h.textContent || '').trim().toLowerCase();
            return t === 'dátum' || t === 'datum';
        });
        if (dateHeader) dateHeader.click();
    }

    function init() {
        initAdminOrdersTable();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
