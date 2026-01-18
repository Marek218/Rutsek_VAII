(function () {
    'use strict';

    // Simple helper to select and escape
    // Show a temporary toast message at top-right
    function toast(text, type = 'info', timeout = 2500) {
        try {
            var t = document.createElement('div');
            t.className = 'gallery-toast alert alert-' + (type === 'error' ? 'danger' : (type === 'success' ? 'success' : 'secondary'));
            t.style.position = 'fixed';
            t.style.right = '12px';
            t.style.top = (12 + (document.querySelectorAll('.gallery-toast').length * 48)) + 'px';
            t.style.zIndex = 1200;
            t.style.minWidth = '180px';
            t.style.opacity = '0.98';
            t.textContent = text;
            document.body.appendChild(t);
            setTimeout(function () { t.remove(); }, timeout);
        } catch (e) { console.log('toast failed', e); }
    }

    function init() {
        var grid = document.getElementById('galleryGrid');
        if (!grid) return; // no gallery on page

        // Debug overlay when ?debug=1
        var debugMode = window.location.search.indexOf('debug=1') !== -1;
        var dbgEl = null;
        function createDebug() {
            dbgEl = document.createElement('div');
            dbgEl.style.position = 'fixed'; dbgEl.style.left = '12px'; dbgEl.style.bottom = '12px'; dbgEl.style.zIndex = '999999';
            dbgEl.style.background = 'rgba(0,0,0,0.75)'; dbgEl.style.color = '#fff'; dbgEl.style.padding = '8px 10px'; dbgEl.style.fontSize = '12px'; dbgEl.style.borderRadius = '6px';
            dbgEl.id = 'gallery-debug-overlay';
            dbgEl.innerHTML = '<strong>Gallery DnD debug</strong><div id="gd-body">init...</div>';
            document.body.appendChild(dbgEl);
        }
        function updateDebug(txt) { if (!debugMode) return; if (!dbgEl) createDebug(); var b = document.getElementById('gd-body'); if (b) b.innerHTML = txt.replace(/\n/g,'<br>'); }

        updateDebug('grid: ' + (!!grid));

        // Only activate reorder when server marked admin or when data-admin-reorder present
        var adminFlag = grid.getAttribute('data-admin-reorder') === '1';
        if (!adminFlag) {
            updateDebug('adminFlag: false — drag disabled');
            return; // not admin, nothing to do
        }
        updateDebug('adminFlag: true');
        // add a class to the grid to toggle CSS that disables anchor pointer-events while reordering
        grid.classList.add('admin-reorder-active');

        var endpoint = grid.getAttribute('data-reorder-endpoint') || '';
        updateDebug('endpoint: ' + (endpoint || '(none)'));

        var dragging = null; // element being dragged
        var placeholder = document.createElement('div');
        placeholder.className = 'gallery-thumb gallery-placeholder';
        placeholder.style.minHeight = '120px';

        // pointer state
        var pointerId = null;

        function tiles() { return Array.from(grid.querySelectorAll('[data-gallery-item]')); }

        function getTileRect(el) { return el.getBoundingClientRect(); }

        var eventsCount = { down:0, move:0, up:0 };

        function onStart(e, tile) {
            // Prevent starting when clicking controls
            if (e.target && (e.target.closest && e.target.closest('form,button'))) return;
            e.preventDefault && e.preventDefault();
            dragging = tile;
            dragging.classList.add('is-dragging');
            dragging.classList.add('dragging-active');

            var r = getTileRect(tile);
            // set fixed size/position so it can move
            dragging.style.width = r.width + 'px';
            dragging.style.height = r.height + 'px';
            dragging.style.position = 'fixed';
            dragging.style.left = r.left + 'px';
            dragging.style.top = r.top + 'px';
            dragging.style.zIndex = 9999;
            dragging.style.pointerEvents = 'none';

            // insert placeholder
            tile.parentNode.insertBefore(placeholder, tile.nextSibling);

            // capture pointerId for pointer events
            if (e.pointerId) {
                pointerId = e.pointerId;
                try { if (dragging.setPointerCapture) dragging.setPointerCapture(pointerId); } catch (er) {}
            }
            eventsCount.down++;
            updateDebug('tiles=' + tiles().length + '\ndown=' + eventsCount.down + ' move=' + eventsCount.move + ' up=' + eventsCount.up);
        }

        function moveAt(clientX, clientY) {
            if (!dragging) return;
            eventsCount.move++;
            var w = dragging.offsetWidth;
            dragging.style.left = (clientX - w / 2) + 'px';
            dragging.style.top = (clientY - 40) + 'px';

            // find closest tile center
            var candidates = tiles().filter(function (t) { return t !== dragging && t !== placeholder; });
            var best = null; var bestDist = Infinity;
            candidates.forEach(function (c) {
                var r = c.getBoundingClientRect();
                var cx = r.left + r.width/2; var cy = r.top + r.height/2;
                var dx = cx - clientX; var dy = cy - clientY; var d = dx*dx + dy*dy;
                if (d < bestDist) { bestDist = d; best = c; }
            });
            if (!best) { updateDebug('move count=' + eventsCount.move); return; }
            var r = best.getBoundingClientRect();
            var before = (clientX - r.left) < (r.width/2);
            if (before) {
                best.parentNode.insertBefore(placeholder, best);
            } else {
                best.parentNode.insertBefore(placeholder, best.nextSibling);
            }
            updateDebug('tiles=' + tiles().length + '\ndown=' + eventsCount.down + ' move=' + eventsCount.move + ' up=' + eventsCount.up);
        }

        function onEnd() {
            if (!dragging) return;
            eventsCount.up++;
            // place dragging element to placeholder position
            try { placeholder.parentNode.insertBefore(dragging, placeholder); } catch (e) { console.warn('insert failed', e); }
            // reset styles
            try { if (pointerId && dragging.releasePointerCapture) dragging.releasePointerCapture(pointerId); } catch (er) {}
            dragging.style.position = '';
            dragging.style.left = '';
            dragging.style.top = '';
            dragging.style.width = '';
            dragging.style.height = '';
            dragging.style.zIndex = '';
            dragging.style.pointerEvents = '';
            dragging.classList.remove('is-dragging');
            dragging.classList.remove('dragging-active');

            // remove placeholder
            placeholder.remove();

            // collect order and save
            var ids = tiles().map(function (t) { return t.getAttribute('data-id'); });
            if (endpoint) saveOrder(ids);
            else toast('Poradie zmenené (neuložené) – endpoint chýba', 'error');

            dragging = null;
            pointerId = null;
            updateDebug('tiles=' + tiles().length + '\ndown=' + eventsCount.down + ' move=' + eventsCount.move + ' up=' + eventsCount.up);
            try { grid.classList.remove('admin-reorder-active'); } catch (e) {}
        }

        function saveOrder(ids) {
            if (!endpoint) return Promise.reject(new Error('no endpoint'));
            var body = 'mode=reorder';
            ids.forEach(function (id) { body += '&order[]=' + encodeURIComponent(id); });
            return fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                credentials: 'same-origin',
                body: body
            }).then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                toast('Poradie uložené', 'success');
                return res.text();
            }).catch(function (err) {
                console.error('saveOrder failed', err);
                toast('Uloženie poradia zlyhalo', 'error');
            });
        }

        // Attach handlers per tile
        function attach() {
            tiles().forEach(function (tile) {
                // Use pointer events when available
                tile.addEventListener('pointerdown', function (ev) {
                    // only primary button (or touch)
                    if (typeof ev.button === 'number' && ev.button !== 0) return;
                    onStart(ev, tile);
                }, { passive: false, capture: true });

                // mouse fallback
                tile.addEventListener('mousedown', function (ev) {
                    if (ev.button !== 0) return;
                    onStart(ev, tile);
                }, { passive: false, capture: true });

                // touch fallback (use first touch)
                tile.addEventListener('touchstart', function (ev) {
                    var t = ev.touches && ev.touches[0]; if (!t) return;
                    // craft a minimal event-like object
                    var fake = { button: undefined, pointerId: undefined, clientX: t.clientX, clientY: t.clientY, target: ev.target, preventDefault: function(){ ev.preventDefault(); } };
                    onStart(fake, tile);
                }, { passive: false, capture: true });
            });

            // movement handlers on window
            window.addEventListener('pointermove', function (ev) {
                if (!dragging) return;
                ev.preventDefault && ev.preventDefault();
                moveAt(ev.clientX, ev.clientY);
            }, { passive: false });

            window.addEventListener('mousemove', function (ev) {
                if (!dragging) return;
                ev.preventDefault && ev.preventDefault();
                moveAt(ev.clientX, ev.clientY);
            }, { passive: false });

            window.addEventListener('touchmove', function (ev) {
                if (!dragging) return;
                var t = ev.touches && ev.touches[0]; if (!t) return;
                moveAt(t.clientX, t.clientY);
                ev.preventDefault();
            }, { passive: false });

            window.addEventListener('pointerup', function () { onEnd(); }, { passive: true });
            window.addEventListener('mouseup', function () { onEnd(); }, { passive: true });
            window.addEventListener('touchend', function () { onEnd(); }, { passive: true });
        }

        attach();

        // small CSS safety in case it's missing
        try {
            var style = document.createElement('style');
            style.textContent = '\n.gallery-thumb.dragging-active{box-shadow: 0 18px 40px rgba(0,0,0,0.35);opacity:0.95;transform:translateZ(0);}\n.gallery-placeholder{background:rgba(60,120,255,0.06);border:2px dashed rgba(60,120,255,0.35);border-radius:10px;}\n.gallery-toast{transition:opacity .2s;}\n';
            document.head.appendChild(style);
        } catch (e) {}

        // done
        console.log('Gallery reorder initialized (admin mode).');
    }

    // Init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
