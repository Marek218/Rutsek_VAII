// public/js/gallery-errors.js
// Shows an overlay when a gallery image fails to load.

(function () {
    'use strict';

    function init() {
        var imgs = document.querySelectorAll('[data-gallery-item] img[data-gallery-img]');
        for (var i = 0; i < imgs.length; i++) {
            (function (img) {
                img.addEventListener('error', function () {
                    try {
                        var tile = img.closest ? img.closest('[data-gallery-item]') : null;
                        if (!tile) return;
                        tile.classList.add('is-broken');
                        var err = tile.querySelector('[data-gallery-error]');
                        if (err) err.hidden = false;
                    } catch (e) {
                        // ignore
                    }
                }, { once: true });
            })(imgs[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
