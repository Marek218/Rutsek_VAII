// public/js/admin-messages.js
// AJAX for Admin -> Messages table:
// - Delete message without page reload

(function () {
    'use strict';

    function findClosest(el, selector) {
        if (el && el.nodeType === 3) el = el.parentElement;
        if (el && typeof el.closest === 'function') return el.closest(selector);
        while (el && el.nodeType === 1) {
            if (el.matches && el.matches(selector)) return el;
            el = el.parentElement;
        }
        return null;
    }

    function init() {
        var table = document.querySelector('[data-admin-messages-table]');
        if (!table) return;

        table.addEventListener('submit', function (e) {
            var form = findClosest(e.target, 'form[data-ajax-delete-message]');
            if (!form) return;

            e.preventDefault();

            if (!confirm('Naozaj chcete vymazať túto správu?')) return;

            var endpoint = form.getAttribute('action') || '';
            var idInput = form.querySelector('input[name="id"]');
            var id = idInput ? idInput.value : '';

            var body = 'id=' + encodeURIComponent(id) + '&ajax=1';

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body,
                credentials: 'same-origin'
            })
                .then(function (res) { return res.json().then(function (data) { return { res: res, data: data }; }); })
                .then(function (r) {
                    if (!r.res.ok || !r.data || !r.data.ok) {
                        throw new Error((r.data && r.data.error) || 'Delete failed');
                    }

                    var tr = findClosest(form, 'tr');
                    if (tr && tr.parentNode) tr.parentNode.removeChild(tr);
                })
                .catch(function (err) {
                    alert(err && err.message ? err.message : 'Správu sa nepodarilo vymazať.');
                });
        }, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
