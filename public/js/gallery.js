// public/js/gallery.js
// - Admin drag & drop reorder for gallery (saves sort_order via POST mode=reorder)
// - Lightbox helper (Bootstrap modal) for gallery

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

    function initGalleryLightbox() {
        var galleryModal = document.getElementById('galleryModal');
        if (!galleryModal) return;

        galleryModal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            if (!trigger) return;
            var img = trigger.getAttribute('data-img');

            var modalImg = document.getElementById('galleryModalImage');
            if (modalImg) {
                modalImg.src = img || '';
                modalImg.alt = trigger.getAttribute('data-title') || '';
            }
        });

        galleryModal.addEventListener('hidden.bs.modal', function () {
            var modalImg = document.getElementById('galleryModalImage');
            if (modalImg) {
                modalImg.src = '';
                modalImg.alt = '';
            }
        });
    }

    function initGalleryReorder() {
        var grid = document.querySelector('[data-gallery-grid][data-admin-reorder="1"]');
        if (!grid) return;

        var saveBtn = document.querySelector('[data-gallery-reorder-save]');
        if (!saveBtn) return;

        var endpoint = saveBtn.getAttribute('data-reorder-endpoint') || '';
        var redirectUrl = saveBtn.getAttribute('data-reorder-redirect') || '';
        if (!endpoint) return;

        var isDragging = false;
        var draggedEl = null;

        var placeholder = document.createElement('div');
        placeholder.className = 'gallery-thumb gallery-placeholder';
        placeholder.setAttribute('aria-hidden', 'true');

        function tiles() {
            return Array.prototype.slice.call(grid.querySelectorAll('[data-gallery-item][data-id]'));
        }

        function getClosestTile(x, y) {
            var list = tiles().filter(function (t) { return t !== draggedEl; });
            var best = null;
            var bestDist = Infinity;
            list.forEach(function (t) {
                var r = t.getBoundingClientRect();
                var cx = r.left + r.width / 2;
                var cy = r.top + r.height / 2;
                var dx = cx - x;
                var dy = cy - y;
                var d = dx * dx + dy * dy;
                if (d < bestDist) { bestDist = d; best = t; }
            });
            return best;
        }

        function insertPlaceholderNear(target, x) {
            if (!target) return;
            var r = target.getBoundingClientRect();
            var before = (x - r.left) < (r.width / 2);
            if (before) {
                grid.insertBefore(placeholder, target);
            } else {
                grid.insertBefore(placeholder, target.nextSibling);
            }
        }

        function collectOrder() {
            return tiles()
                .filter(function (t) { return t !== placeholder; })
                .map(function (t) { return parseInt(t.getAttribute('data-id') || '0', 10); })
                .filter(function (id) { return id > 0; });
        }

        function postOrder(orderIds) {
            var body = 'mode=reorder';
            orderIds.forEach(function (id) { body += '&order[]=' + encodeURIComponent(String(id)); });

            return fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: body,
                credentials: 'same-origin'
            }).then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.text();
            });
        }

        // Prevent modal open while dragging
        grid.addEventListener('click', function (e) {
            if (!isDragging) return;
            var a = findClosest(e.target, 'a[data-bs-toggle="modal"]');
            if (a) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);

        function resetDraggedEl(el) {
            el.style.position = '';
            el.style.left = '';
            el.style.top = '';
            el.style.zIndex = '';
            el.style.pointerEvents = '';
            el.style.width = '';
            el.style.height = '';
            el.classList.remove('is-dragging');
        }

        function finishDrag() {
            if (!isDragging || !draggedEl) return;

            resetDraggedEl(draggedEl);
            grid.insertBefore(draggedEl, placeholder);

            placeholder.remove();
            placeholder = document.createElement('div');
            placeholder.className = 'gallery-thumb gallery-placeholder';
            placeholder.setAttribute('aria-hidden', 'true');

            isDragging = false;
            draggedEl = null;
        }

        function attachHandlers() {
            tiles().forEach(function (tile) {
                tile.removeAttribute('draggable');

                tile.addEventListener('pointerdown', function (e) {
                    // only primary button
                    if (e.button !== 0) return;
                    // ignore delete button/form clicks
                    if (findClosest(e.target, 'button') || findClosest(e.target, 'form')) return;

                    isDragging = true;
                    draggedEl = tile;
                    draggedEl.classList.add('is-dragging');

                    // placeholder where tile was
                    grid.insertBefore(placeholder, draggedEl.nextSibling);

                    // fixed positioning so it follows pointer
                    var rect = draggedEl.getBoundingClientRect();
                    draggedEl.dataset.origWidth = String(rect.width);
                    draggedEl.style.width = rect.width + 'px';
                    draggedEl.style.height = rect.height + 'px';
                    draggedEl.style.position = 'fixed';
                    draggedEl.style.left = rect.left + 'px';
                    draggedEl.style.top = rect.top + 'px';
                    draggedEl.style.zIndex = '9999';
                    draggedEl.style.pointerEvents = 'none';

                    try { draggedEl.setPointerCapture(e.pointerId); } catch (err) {}
                    e.preventDefault();
                }, { passive: false });
            });
        }

        attachHandlers();

        window.addEventListener('pointermove', function (e) {
            if (!isDragging || !draggedEl) return;

            var w = parseFloat(draggedEl.dataset.origWidth || '0') || draggedEl.getBoundingClientRect().width;
            draggedEl.style.left = (e.clientX - w / 2) + 'px';
            draggedEl.style.top = (e.clientY - 40) + 'px';

            var target = getClosestTile(e.clientX, e.clientY);
            insertPlaceholderNear(target, e.clientX);
        }, { passive: true });

        window.addEventListener('pointerup', finishDrag, { passive: true });
        window.addEventListener('pointercancel', finishDrag, { passive: true });

        saveBtn.addEventListener('click', function () {
            var ids = collectOrder();
            if (ids.length === 0) return;

            saveBtn.disabled = true;
            var original = saveBtn.textContent;
            saveBtn.textContent = 'Ukladám...';

            postOrder(ids)
                .then(function () {
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    } else {
                        window.location.reload();
                    }
                })
                .catch(function () {
                    saveBtn.disabled = false;
                    saveBtn.textContent = original || 'Uložiť poradie';
                    alert('Poradie sa nepodarilo uložiť.');
                });
        });
    }

    function init() {
        initGalleryLightbox();
        initGalleryReorder();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
