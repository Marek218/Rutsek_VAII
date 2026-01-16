// public/js/contact-ajax.js
// AJAX submit for the "Napíšte nám" form on Contact page.

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

    function formUrlEncode(form) {
        var fd = new FormData(form);
        var parts = [];
        fd.forEach(function (v, k) {
            parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(v == null ? '' : String(v)));
        });
        return parts.join('&');
    }

    function clearInlineErrors(form) {
        var nodes = form.querySelectorAll('[data-ajax-error]');
        for (var i = 0; i < nodes.length; i++) nodes[i].parentNode.removeChild(nodes[i]);

        var invalids = form.querySelectorAll('.is-invalid');
        for (var j = 0; j < invalids.length; j++) invalids[j].classList.remove('is-invalid');

        var top = form.querySelector('[data-ajax-top-alert]');
        if (top && top.parentNode) top.parentNode.removeChild(top);
    }

    function showTopAlert(form, message, tone) {
        var alert = document.createElement('div');
        alert.setAttribute('data-ajax-top-alert', '1');
        alert.className = 'alert alert-' + (tone || 'danger') + ' mt-2';
        alert.textContent = message || 'Nastala chyba.';
        form.insertAdjacentElement('afterbegin', alert);
    }

    function showInlineErrors(form, errors) {
        var map = {
            name: '#contact-name',
            email: '#contact-email',
            message: '#contact-message'
        };

        for (var key in errors) {
            if (!Object.prototype.hasOwnProperty.call(errors, key)) continue;
            var sel = map[key];
            var input = sel ? form.querySelector(sel) : null;
            if (!input) continue;

            input.classList.add('is-invalid');

            var div = document.createElement('div');
            div.className = 'invalid-feedback';
            div.setAttribute('data-ajax-error', '1');
            div.textContent = String(errors[key]);
            input.insertAdjacentElement('afterend', div);
        }
    }

    function init() {
        document.addEventListener('submit', function (e) {
            var form = findClosest(e.target, 'form[data-ajax-contact="1"]');
            if (!form) return;

            e.preventDefault();

            clearInlineErrors(form);

            var action = form.getAttribute('action') || '';
            if (!action) {
                showTopAlert(form, 'Form action chýba.', 'danger');
                return;
            }

            var submitBtn = form.querySelector('button[type="submit"]');
            var originalText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Odosielam...';
            }

            fetch(action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formUrlEncode(form),
                credentials: 'same-origin'
            })
                .then(function (res) {
                    return res.text().then(function (txt) {
                        var data;
                        try { data = txt ? JSON.parse(txt) : null; } catch (e2) { data = null; }
                        return { res: res, data: data, raw: txt };
                    });
                })
                .then(function (r) {
                    if (!r.res.ok || !r.data) {
                        var msg = r.data && (r.data.error || r.data.message) ? (r.data.error || r.data.message) : ('HTTP ' + r.res.status);
                        throw new Error(msg);
                    }

                    if (r.data.ok) {
                        showTopAlert(form, 'Správa bola odoslaná. Ďakujeme.', 'success');
                        form.reset();
                        return;
                    }

                    if (r.data.errors) {
                        showTopAlert(form, 'Skontrolujte prosím formulár.', 'warning');
                        showInlineErrors(form, r.data.errors);
                        return;
                    }

                    throw new Error('Správu sa nepodarilo odoslať.');
                })
                .catch(function (err) {
                    showTopAlert(form, err && err.message ? err.message : 'Správu sa nepodarilo odoslať.', 'danger');
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                });
        }, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
