// public/js/admin-orders.js
// - Sorting for the admin orders table (Rezervácie)
// - AJAX delete/edit handlers for admin actions

(function () {
    'use strict';

    function findClosest(el, selector) {
        if (!el) return null;
        if (el.nodeType === 3) el = el.parentElement;
        return el.closest ? el.closest(selector) : null;
    }

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

    /* ---------- AJAX handlers for admin forms ---------- */
    function ajaxDeleteHandler(e) {
        var form = findClosest(e.target, 'form[data-ajax-delete-order]');
        if (!form) return;
        console.log('ajaxDeleteHandler triggered for form', form);
        e.preventDefault();
        if (!confirm('Naozaj chcete vymazať túto rezerváciu?')) return;

        var action = form.getAttribute('action') || window.location.href;
        var data = new URLSearchParams(new FormData(form)).toString();

        fetch(action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: data, credentials: 'same-origin' })
            .then(function (res) {
                return res.text().then(function (txt) {
                    try { return JSON.parse(txt); } catch (e) { throw new Error('Invalid JSON: ' + txt); }
                });
            })
            .then(function (json) {
                if (json && json.ok) {
                    // remove row
                    var row = findClosest(form, 'tr');
                    if (row && row.parentNode) row.parentNode.removeChild(row);
                } else {
                    console.error('Delete response not OK', json);
                    // fallback to normal submit
                    form.removeAttribute('data-ajax-delete-order');
                    form.submit();
                }
            }).catch(function (err) {
                console.error('AJAX delete failed', err);
                // fallback to normal submit
                form.removeAttribute('data-ajax-delete-order');
                form.submit();
            });
    }

    // Click handler for delete buttons to ensure AJAX triggers even if form submission is interfered
    function deleteButtonClickHandler(e) {
        var btn = e.target.closest && e.target.closest('button[type="submit"]');
        if (!btn) return;
        var form = findClosest(btn, 'form[data-ajax-delete-order]');
        if (!form) return;
        e.preventDefault();
        // delegate to ajaxDeleteHandler by dispatching submit event
        var ev = new Event('submit', { bubbles: true, cancelable: true });
        form.dispatchEvent(ev);
    }

    function ajaxEditHandler(e) {
        var form = findClosest(e.target, 'form[data-ajax-edit-order]');
        if (!form) return;
        e.preventDefault();

        var action = form.getAttribute('action') || window.location.href;
        var data = new URLSearchParams(new FormData(form)).toString();
        var submitBtn = form.querySelector('button[type="submit"]');
        var origText = submitBtn ? submitBtn.textContent : '';
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Ukladám...'; }

        fetch(action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: data, credentials: 'same-origin' })
            .then(function (res) {
                return res.text().then(function (txt) {
                    try { return JSON.parse(txt); } catch (e) { throw new Error('Invalid JSON: ' + txt); }
                });
            })
            .then(function (json) {
                if (json && json.ok) {
                    // on success redirect to list to see updated entries
                    window.location.href = window.location.pathname + '?c=admin&a=orders';
                } else {
                    console.error('Edit response not OK', json);
                    // fallback to normal submit
                    form.removeAttribute('data-ajax-edit-order');
                    form.submit();
                }
            }).catch(function (err) {
                console.error('AJAX edit failed', err);
                // fallback to normal submit
                form.removeAttribute('data-ajax-edit-order');
                form.submit();
            })
            .finally(function () { if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = origText; } });
    }

    function attachAjaxHandlers() {
        document.addEventListener('submit', ajaxDeleteHandler, true);
        document.addEventListener('submit', ajaxEditHandler, true);
        document.addEventListener('click', deleteButtonClickHandler, true);
    }

    function init() {
        initAdminOrdersTable();
        attachAjaxHandlers();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
