// public/js/order-ajax.js
// AJAX for booking form (minimal):
// 1) Availability check (date+time). All services assumed 60 minutes.
// 2) Submit booking without full page reload

//    Made with AI

(function () {
    'use strict';

    function $(sel) { return document.querySelector(sel); }

    function formatSkDate(yyyyMmDd) {
        if (!yyyyMmDd || typeof yyyyMmDd !== 'string') return '';
        var m = yyyyMmDd.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!m) return yyyyMmDd;
        return m[3] + '.' + m[2] + '.' + m[1];
    }

    function formUrlEncode(obj) {
        var parts = [];
        for (var k in obj) {
            if (!Object.prototype.hasOwnProperty.call(obj, k)) continue;
            parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(obj[k] == null ? '' : String(obj[k])));
        }
        return parts.join('&');
    }

    function getFormData(form) {
        var fd = new FormData(form);
        var out = {};
        fd.forEach(function (v, k) { out[k] = v; });
        return out;
    }

    function setStatus(el, text, tone) {
        if (!el) return;
        el.textContent = text || '';
        el.className = 'form-text ' + (tone ? ('text-' + tone) : '');
    }

    function init() {
        var form = document.querySelector('form.order-form');
        if (!form) return;

        var dateEl = $('#date');
        var timeEl = $('#time');
        var submitBtn = form.querySelector('button[type="submit"]');

        var submitUrl = form.getAttribute('action') || '';
        var availabilityUrl = form.getAttribute('data-availability-url') || '';
        var availabilityFallbackUrl = form.getAttribute('data-availability-url-fallback') || '';
        var nextAvailUrl = form.getAttribute('data-next-available-url') || '';
        var nextAvailFallbackUrl = form.getAttribute('data-next-available-url-fallback') || '';

        var statusEl = document.getElementById('availabilityStatus');
        var nextStatusEl = document.getElementById('nextAvailableStatus');
        if (!statusEl && timeEl) {
            statusEl = document.createElement('div');
            statusEl.id = 'availabilityStatus';
            statusEl.className = 'form-text';
            timeEl.insertAdjacentElement('afterend', statusEl);
        }
        if (!nextStatusEl && timeEl) {
            nextStatusEl = document.createElement('div');
            nextStatusEl.id = 'nextAvailableStatus';
            nextStatusEl.className = 'form-text';
            timeEl.insertAdjacentElement('afterend', nextStatusEl);
        }

        function disableSubmit(disabled) {
            if (!submitBtn) return;
            submitBtn.disabled = !!disabled;
        }

        function canCheckAvailability() {
            return !!(availabilityUrl && dateEl && timeEl && dateEl.value && timeEl.value);
        }

        function doFetch(url) {
            return fetch(url, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            }).then(function (res) {
                return res.text().then(function (txt) {
                    var data;
                    try { data = txt ? JSON.parse(txt) : null; } catch (e) { data = null; }
                    return { res: res, data: data, raw: txt, url: url };
                });
            });
        }

        function buildUrl(baseUrl, params) {
            try {
                var u = new URL(baseUrl, window.location.origin);
                Object.keys(params).forEach(function (k) { u.searchParams.set(k, params[k]); });
                return u.toString();
            } catch (e) {
                // very old browsers fallback
                var qs = Object.keys(params).map(function (k) {
                    return encodeURIComponent(k) + '=' + encodeURIComponent(String(params[k]));
                }).join('&');
                return baseUrl + (baseUrl.indexOf('?') >= 0 ? '&' : '?') + qs;
            }
        }

        function checkNextAvailable(params) {
            var baseUrl = nextAvailUrl || nextAvailFallbackUrl;
            if (!baseUrl) {
                setStatus(nextStatusEl, '', '');
                return;
            }

            console.log('Checking next available...', params);

            var url = buildUrl(baseUrl, params);
            doFetch(url)
                .then(function (r) {
                    if (r.res && r.res.status === 404 && nextAvailFallbackUrl && baseUrl !== nextAvailFallbackUrl) {
                        var fb = buildUrl(nextAvailFallbackUrl, params);
                        return doFetch(fb);
                    }
                    return r;
                })
                .then(function (r) {
                    console.log('nextAvailable response', r && r.res && r.res.status, r && r.data);
                    if (!r || !r.res || !r.res.ok || !r.data || !r.data.ok) {
                        setStatus(nextStatusEl, '', '');
                        return;
                    }

                    if (r.data.date && r.data.time) {
                        setStatus(nextStatusEl, 'Najbližší voľný termín: ' + formatSkDate(r.data.date) + ' ' + r.data.time, '');
                    } else {
                        setStatus(nextStatusEl, (r.data.reason || ''), '');
                    }
                })
                .catch(function (e) {
                    console.error('nextAvailable fetch failed', e);
                    setStatus(nextStatusEl, '', '');
                });
        }

        function checkAvailability() {
            if (!canCheckAvailability()) {
                setStatus(statusEl, '', '');
                disableSubmit(false);
                return;
            }

            setStatus(statusEl, 'Overujem dostupnosť termínu...', 'secondary');

            var params = { date: dateEl.value, time: timeEl.value };
            var url = buildUrl(availabilityUrl, params);

            doFetch(url)
                .then(function (r) {
                    // fallback when query-string route isn't available (rare)
                    if (r.res && r.res.status === 404 && availabilityFallbackUrl) {
                        var fb = buildUrl(availabilityFallbackUrl, params);
                        return doFetch(fb);
                    }
                    return r;
                })
                .then(function (r) {
                    if (!r.res.ok || !r.data || !r.data.ok) {
                        var snip = (r.raw || '').toString().replace(/\s+/g, ' ').slice(0, 120);
                        console.error('Availability error', r.url, r.res && r.res.status, r.raw);
                        setStatus(statusEl, 'Dostupnosť sa nepodarilo overiť. ' + (r.res ? ('HTTP ' + r.res.status + '.') : '') + (snip ? (' Odpoveď: ' + snip) : ''), 'warning');
                        disableSubmit(false);
                        return;
                    }

                    if (r.data.available) {
                        setStatus(statusEl, 'Termín je voľný.', 'success');
                        setStatus(nextStatusEl, '', '');
                        disableSubmit(false);
                    } else {
                        setStatus(statusEl, (r.data.reason || 'Termín nie je voľný.'), 'danger');
                        disableSubmit(true);
                        // Suggest next free slot
                        checkNextAvailable(params);
                    }
                })
                .catch(function (e) {
                    console.error('Availability fetch failed', e);
                    setStatus(statusEl, 'Dostupnosť sa nepodarilo overiť (chyba siete).', 'warning');
                    disableSubmit(false);
                });
        }

        function clearInlineErrors() {
            var nodes = form.querySelectorAll('[data-ajax-error]');
            for (var i = 0; i < nodes.length; i++) nodes[i].parentNode.removeChild(nodes[i]);
        }

        function showInlineErrors(errors) {
            var map = {
                first_name: '#first_name',
                last_name: '#last_name',
                email: '#email',
                phone: '#phone',
                service_id: '#service_id',
                date: '#date',
                time: '#time',
                notes: '#notes'
            };

            var firstFocus = null;

            for (var key in errors) {
                if (!Object.prototype.hasOwnProperty.call(errors, key)) continue;
                var sel = map[key];
                var input = sel ? $(sel) : null;
                if (!input) continue;

                var div = document.createElement('div');
                div.className = 'form-text text-danger';
                div.setAttribute('data-ajax-error', '1');
                div.textContent = String(errors[key]);
                input.insertAdjacentElement('afterend', div);
                if (!firstFocus) { firstFocus = input; }
            }

            if (firstFocus && typeof firstFocus.focus === 'function') { try { firstFocus.focus(); } catch(e) {} }
        }

        function showTopAlert(message, tone) {
            var existing = document.getElementById('orderAjaxTopAlert');
            if (existing) existing.parentNode.removeChild(existing);

            var alert = document.createElement('div');
            alert.id = 'orderAjaxTopAlert';
            alert.className = 'alert alert-' + (tone || 'danger');
            alert.textContent = message || 'Nastala chyba.';
            form.insertAdjacentElement('beforebegin', alert);
        }

        function submitAjax(e) {
            if (!submitUrl) return;

            e.preventDefault();
            clearInlineErrors();

            var payload = getFormData(form);

            disableSubmit(true);
            var originalText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) submitBtn.textContent = 'Odosielam...';

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formUrlEncode(payload),
                credentials: 'same-origin'
            })
                .then(function (res) {
                    return res.text().then(function (txt) {
                        var data;
                        try { data = txt ? JSON.parse(txt) : null; } catch (e) { data = null; }
                        return { res: res, data: data, raw: txt };
                    });
                })
                .then(function (r) {
                    if (!r.res.ok) {
                        if (r.data && r.data.errors) {
                            showTopAlert('Skontrolujte prosím formulár. Niektoré polia sú vyplnené nesprávne.', 'warning');
                            showInlineErrors(r.data.errors);
                            disableSubmit(false);
                            if (submitBtn) submitBtn.textContent = originalText;
                            return;
                        }
                        var msg = (r.data && (r.data.error || r.data.message)) ? (r.data.error || r.data.message) : ('HTTP ' + r.res.status);
                        throw new Error(msg);
                    }

                    showTopAlert('Objednávka bola úspešne odoslaná. Ďakujeme!', 'success');
                    form.reset();
                    setStatus(statusEl, '', '');
                    disableSubmit(false);
                    if (submitBtn) submitBtn.textContent = originalText;
                })
                .catch(function (err) {
                    showTopAlert(err && err.message ? err.message : 'Objednávku sa nepodarilo odoslať.', 'danger');
                    disableSubmit(false);
                    if (submitBtn) submitBtn.textContent = originalText;
                });
        }

        if (dateEl) dateEl.addEventListener('change', checkAvailability);
        if (timeEl) timeEl.addEventListener('change', checkAvailability);

        // initial check if values are prefilled
        if (dateEl && dateEl.value && timeEl && timeEl.value) {
            checkAvailability();
        }

        form.addEventListener('submit', submitAjax);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();