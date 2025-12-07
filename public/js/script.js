// Client-side utilities for UI enhancements (public/js/script.js)
// - Adds sorting to the admin orders table (by date/time or by name)
// - Safe to load on all pages: will only activate if the admin table exists

(function () {
    'use strict';

    function initAdminOrdersTable() {
        const table = document.querySelector('.table.table-striped.table-hover.align-middle');
        if (!table) return; // not present on this page

        const tbody = table.tBodies[0];
        if (!tbody) return;

        // Enhance rows: ensure data-datetime attribute exists (use existing <td> values)
        Array.from(tbody.querySelectorAll('tr')).forEach(row => {
            // If placeholder row (colspan) keep as-is
            const cells = row.querySelectorAll('td');
            if (!cells || cells.length < 6) return;

            // If already set, leave it
            if (row.dataset.datetime && row.dataset.datetime !== '') return;

            const dateText = cells[4]?.textContent?.trim() || '';
            const timeText = cells[5]?.textContent?.trim() || '';
            // Normalize to ISO-like string for Date.parse
            // Expected input format: YYYY-MM-DD and HH:MM
            if (dateText) {
                row.dataset.datetime = dateText + 'T' + (timeText || '00:00');
            } else {
                row.dataset.datetime = '';
            }
        });

        // Make headers sortable: find header cells for "Meno" and "Dátum"
        const headers = table.querySelectorAll('th');
        headers.forEach(th => {
            const txt = (th.textContent || '').trim().toLowerCase();
            if (txt === 'meno' || txt === 'dátum' || txt === 'datum') {
                th.classList.add('sortable');
                th.style.cursor = 'pointer';
                // add a small visual indicator element if missing
                if (!th.querySelector('.sort-indicator')) {
                    const span = document.createElement('span');
                    span.className = 'sort-indicator';
                    span.textContent = ''; // will be set when sorted
                    th.appendChild(span);
                }
            }
        });

        function getRows() {
            const all = Array.from(tbody.querySelectorAll('tr'));
            const dataRows = all.filter(r => r.dataset.datetime !== undefined && r.dataset.datetime !== '');
            const placeholders = all.filter(r => r.dataset.datetime === undefined || r.dataset.datetime === '');
            return { dataRows, placeholders };
        }

        function compareValues(a, b, asc) {
            if (a === b) return 0;
            return a < b ? (asc ? -1 : 1) : (asc ? 1 : -1);
        }

        // Attach click handlers
        const sortState = { key: 'datetime', asc: false }; // default: descending (upcoming first)

        table.querySelectorAll('th.sortable').forEach(th => {
            th.addEventListener('click', function () {
                const headerText = (th.textContent || '').trim().toLowerCase();
                let key = 'datetime';
                if (headerText === 'meno') key = 'name';
                if (headerText === 'dátum' || headerText === 'datum') key = 'datetime';

                // Toggle or set sort order
                if (sortState.key === key) {
                    sortState.asc = !sortState.asc;
                } else {
                    sortState.key = key;
                    sortState.asc = (key === 'name'); // names asc by default, dates desc by default
                }

                // Visual indicator (simple): set data-order attribute
                table.querySelectorAll('th.sortable').forEach(h => delete h.dataset.order);
                th.dataset.order = sortState.asc ? 'asc' : 'desc';
                // update visual indicators
                table.querySelectorAll('th.sortable').forEach(h => {
                    const ind = h.querySelector('.sort-indicator');
                    if (ind) ind.textContent = '';
                });
                const curInd = th.querySelector('.sort-indicator');
                if (curInd) curInd.textContent = sortState.asc ? '▲' : '▼';

                const { dataRows, placeholders } = getRows();
                dataRows.sort((rA, rB) => {
                    let vA, vB;
                    if (sortState.key === 'datetime') {
                        vA = rA.dataset.datetime ? Date.parse(rA.dataset.datetime) : 0;
                        vB = rB.dataset.datetime ? Date.parse(rB.dataset.datetime) : 0;
                    } else if (sortState.key === 'name') {
                        vA = (rA.querySelector('td')?.textContent || '').trim().toLowerCase();
                        vB = (rB.querySelector('td')?.textContent || '').trim().toLowerCase();
                    } else {
                        vA = (rA.textContent || '').trim().toLowerCase();
                        vB = (rB.textContent || '').trim().toLowerCase();
                    }
                    return compareValues(vA, vB, sortState.asc);
                });

                // Append sorted rows then placeholders
                tbody.append(...dataRows, ...placeholders);
            });
        });

        // Trigger default sort: by datetime descending (nearest upcoming first)
        const dateHeader = Array.from(table.querySelectorAll('th.sortable')).find(h => {
            const t = (h.textContent || '').trim().toLowerCase();
            return t === 'dátum' || t === 'datum';
        });
        if (dateHeader) {
            // Simulate click to sort
            dateHeader.click();
            // ensure indicator reflects default sort
            const ind = dateHeader.querySelector('.sort-indicator');
            if (ind) ind.textContent = dateHeader.dataset.order === 'asc' ? '▲' : '▼';
        }
    }

    // Init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdminOrdersTable);
    } else {
        initAdminOrdersTable();
    }
})();
