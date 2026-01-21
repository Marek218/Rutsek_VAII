// Lightweight gallery modal handler
// Populates #galleryModalImage and #galleryModalTitle when a thumbnail link is clicked.

(function(){
    'use strict';

    function qs(sel, ctx=document){ return ctx.querySelector(sel); }
    function qsa(sel, ctx=document){ return Array.from(ctx.querySelectorAll(sel)); }

    function init(){
        var modal = qs('#galleryModal');
        if(!modal) return;

        var img = qs('#galleryModalImage', modal);
        var titleEl = qs('#galleryModalTitle', modal);
        var closeBtn = qs('#galleryModal .btn-close', modal);
        var lastFocused = null;

        // When a thumbnail link (with data-bs-target="#galleryModal") is clicked
        qsa('a[data-bs-target="#galleryModal"]').forEach(function(a){
            a.addEventListener('click', function(e){
                // allow regular link behavior if href points to image (progressive enhancement)
                try{ lastFocused = document.activeElement; }catch(e){}
                var src = a.getAttribute('data-img') || a.getAttribute('href') || '';
                var t = a.getAttribute('data-title') || '';

                if(!src){ return; }
                e.preventDefault && e.preventDefault();

                // set placeholder while loading
                if(img){
                    img.style.opacity = '0';
                    img.setAttribute('src', src);
                    img.setAttribute('alt', t || '');
                }
                if(titleEl) titleEl.textContent = t || '';

                // open bootstrap modal if available
                try{
                    if(window.bootstrap && typeof bootstrap.Modal === 'function'){
                        var m = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                        m.show();
                    } else {
                        // fallback: add .show and aria attributes
                        modal.classList.add('show');
                        modal.style.display = 'block';
                        modal.setAttribute('aria-hidden', 'false');
                    }
                }catch(e){ console.warn('modal show', e); }

                // handle image load to fade in
                if(img){
                    img.onload = function(){ img.style.transition = 'opacity .25s ease-in'; img.style.opacity = '1'; };
                    img.onerror = function(){
                        img.style.opacity = '1';
                        titleEl && (titleEl.textContent = 'Obrázok sa nepodarilo načítať');
                    };
                }

                // focus close button for accessibility
                setTimeout(function(){ if(closeBtn) closeBtn.focus(); }, 60);
            });
        });

        // When modal hides, clear src to free memory
        function onHide(){
            try{
                if(window.bootstrap && typeof bootstrap.Modal === 'function'){
                    modal.addEventListener('hidden.bs.modal', function(){ if(img) img.setAttribute('src',''); if(titleEl) titleEl.textContent = ''; try{ if(lastFocused) lastFocused.focus(); }catch(e){} });
                } else {
                    // no bootstrap: listen for click on close button and click outside
                    closeBtn && closeBtn.addEventListener('click', function(){ if(img) img.setAttribute('src',''); if(titleEl) titleEl.textContent = ''; try{ if(lastFocused) lastFocused.focus(); }catch(e){} modal.classList.remove('show'); modal.style.display='none'; modal.setAttribute('aria-hidden','true'); });
                    // backdrop click: if click outside modal-content
                    modal.addEventListener('click', function(ev){ if(ev.target === modal){ if(img) img.setAttribute('src',''); if(titleEl) titleEl.textContent = ''; try{ if(lastFocused) lastFocused.focus(); }catch(e){} modal.classList.remove('show'); modal.style.display='none'; modal.setAttribute('aria-hidden','true'); } });
                }
            }catch(e){ console.warn('modal hide handler', e); }
        }

        onHide();
    }

    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
