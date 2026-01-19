(function(){
    'use strict';

    function toast(text, type='info', timeout=2200){
        try{
            const t = document.createElement('div');
            t.className = 'gallery-toast alert alert-' + (type==='error'?'danger':(type==='success'?'success':'secondary'));
            Object.assign(t.style, {position:'fixed', right:'12px', top: (12 + (document.querySelectorAll('.gallery-toast').length * 48)) + 'px', zIndex:1200, minWidth:'160px', opacity:0.98});
            t.textContent = text; document.body.appendChild(t);
            setTimeout(()=> t.remove(), timeout);
        }catch(e){ console.warn('toast', e); }
    }

    function qs(sel, ctx=document){ return ctx.querySelector(sel); }
    function qsa(sel, ctx=document){ return Array.from(ctx.querySelectorAll(sel)); }

    function init(){
        const grid = qs('#galleryGrid');
        if(!grid) return;
        const isAdmin = grid.getAttribute('data-admin-reorder') === '1';
        if(!isAdmin) return; // reorder only for admin

        const endpoint = grid.getAttribute('data-reorder-endpoint') || null;
        grid.classList.add('admin-reorder-active');

        // small injected styles for placeholder/drag
        try{
            const s = document.createElement('style');
            s.textContent = `
                .gallery-thumb.dragging{ box-shadow:0 18px 40px rgba(0,0,0,0.35); opacity:0.95; transform:translateZ(0); }
                .gallery-placeholder{ background: rgba(0,0,0,0.04); border:2px dashed rgba(0,0,0,0.08); border-radius:8px; }
            `;
            document.head.appendChild(s);
        }catch(e){}

        let dragging = null;
        let placeholder = null;
        let pointerId = null;

        function tiles(){ return qsa('[data-gallery-item]', grid); }

        function createPlaceholder(h, w){
            const ph = document.createElement('div');
            ph.className = 'gallery-thumb gallery-placeholder';
            ph.style.height = h + 'px';
            ph.style.width = w + 'px';
            return ph;
        }

        function onStart(e, tile){
            // prevent drag when clicking on controls inside tile
            if(e.target.closest && e.target.closest('button,form,a')) return;
            e.preventDefault && e.preventDefault();

            dragging = tile;
            const r = tile.getBoundingClientRect();

            // lock size and position
            tile.style.width = r.width + 'px';
            tile.style.height = r.height + 'px';
            tile.style.position = 'fixed';
            tile.style.left = r.left + 'px';
            tile.style.top = r.top + 'px';
            tile.style.zIndex = 9999;
            tile.style.pointerEvents = 'none';
            tile.classList.add('dragging');

            placeholder = createPlaceholder(r.height, r.width);
            tile.parentNode.insertBefore(placeholder, tile.nextSibling);

            if(e.pointerId){ pointerId = e.pointerId; try{ tile.setPointerCapture && tile.setPointerCapture(pointerId); }catch(_){} }
        }

        function moveAt(clientX, clientY){
            if(!dragging) return;
            const w = dragging.offsetWidth;
            dragging.style.left = (clientX - w/2) + 'px';
            dragging.style.top = (clientY - 40) + 'px';

            // find nearest tile center
            const candidates = tiles().filter(t=> t !== dragging && t !== placeholder);
            let best = null; let bestDist = Infinity;
            candidates.forEach(c=>{
                const r = c.getBoundingClientRect();
                const cx = r.left + r.width/2, cy = r.top + r.height/2;
                const dx = cx - clientX, dy = cy - clientY; const d = dx*dx + dy*dy;
                if(d < bestDist){ bestDist = d; best = c; }
            });
            if(!best) return;
            const br = best.getBoundingClientRect();
            const before = (clientX - br.left) < (br.width/2);
            if(before) best.parentNode.insertBefore(placeholder, best);
            else best.parentNode.insertBefore(placeholder, best.nextSibling);
        }

        function onEnd(){
            if(!dragging) return;
            try{ placeholder.parentNode.insertBefore(dragging, placeholder); }catch(e){}

            // reset
            try{ if(pointerId && dragging.releasePointerCapture) dragging.releasePointerCapture(pointerId); }catch(e){}
            dragging.style.position = '';
            dragging.style.left = '';
            dragging.style.top = '';
            dragging.style.width = '';
            dragging.style.height = '';
            dragging.style.zIndex = '';
            dragging.style.pointerEvents = '';
            dragging.classList.remove('dragging');

            placeholder && placeholder.remove();

            // collect ids and save
            const ids = tiles().map(t => t.getAttribute('data-id'));
            if(endpoint){ saveOrder(ids); }
            else toast('Poradie zmenené (neuložené) — endpoint chýba', 'error');

            dragging = null; placeholder = null; pointerId = null;
        }

        function saveOrder(ids){
            if(!endpoint) return;
            const body = new URLSearchParams();
            body.append('mode','reorder');
            ids.forEach(id => body.append('order[]', id));
            fetch(endpoint, {
                method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}, credentials: 'same-origin', body: body.toString()
            }).then(res=>{
                if(!res.ok) throw new Error('HTTP '+res.status);
                toast('Poradie uložené', 'success');
                return res.text();
            }).catch(err=>{ console.error('saveOrder', err); toast('Uloženie poradia zlyhalo', 'error'); });
        }

        // attach handlers
        function attach(){
            tiles().forEach(tile=>{
                // pointer
                tile.addEventListener('pointerdown', function(ev){ if(typeof ev.button === 'number' && ev.button !== 0) return; onStart(ev, tile); }, {passive:false});
                // mouse fallback
                tile.addEventListener('mousedown', function(ev){ if(ev.button !== 0) return; onStart(ev, tile); }, {passive:false});
                // touch fallback
                tile.addEventListener('touchstart', function(ev){ const t = ev.touches && ev.touches[0]; if(!t) return; const fake = { pointerId: undefined, clientX: t.clientX, clientY: t.clientY, target: ev.target, preventDefault: ()=> ev.preventDefault() }; onStart(fake, tile); }, {passive:false});
            });

            window.addEventListener('pointermove', function(ev){ if(!dragging) return; ev.preventDefault && ev.preventDefault(); moveAt(ev.clientX, ev.clientY); }, {passive:false});
            window.addEventListener('mousemove', function(ev){ if(!dragging) return; ev.preventDefault && ev.preventDefault(); moveAt(ev.clientX, ev.clientY); }, {passive:false});
            window.addEventListener('touchmove', function(ev){ if(!dragging) return; const t = ev.touches && ev.touches[0]; if(!t) return; moveAt(t.clientX, t.clientY); ev.preventDefault(); }, {passive:false});

            window.addEventListener('pointerup', onEnd, {passive:true});
            window.addEventListener('mouseup', onEnd, {passive:true});
            window.addEventListener('touchend', onEnd, {passive:true});
        }

        attach();
        console.log('Gallery reorder ready');
    }

    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
