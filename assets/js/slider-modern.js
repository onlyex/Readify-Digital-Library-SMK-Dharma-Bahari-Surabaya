(function(){
  function initSmartSlider(){
    const root = document.getElementById('smartSliderModern');
    if(!root) return;
    const track = root.querySelector('.ss-track');
    const slides = Array.from(root.querySelectorAll('.ss-slide'));
    const prevBtn = root.querySelector('.ss-prev');
    const nextBtn = root.querySelector('.ss-next');
    const dotsWrap = root.querySelector('.ss-dots');

    let idx = (slides.length ? slides.length - 1 : 0); // start at last slide so it appears from the right
    let slideWidth = slides[0] ? slides[0].getBoundingClientRect().width + getGap() : 0;
    let visible = calcVisible();
    // maximum shift (positive) used for clamping. track translateX values will be in [-maxTranslate, 0]
    let maxTranslate = 0;
    let autoplay = true;
    let timer = null;
    const AUTOPLAY_MS = 60000; // much slower autoplay (60s)

    function getGap(){ return 8; }
    function calcVisible(){ const w = window.innerWidth; if(w <= 480) return 1; if(w <= 800) return 2; if(w <= 1100) return 3; return 4; }
    function updateSizes(){ visible = calcVisible(); if(slides[0]){ const rect = slides[0].getBoundingClientRect(); slideWidth = rect.width + getGap(); } // recompute clamp limits
      updateClampLimits();
      idx = Math.min(idx, Math.max(0, slides.length - 1)); moveTo(idx); renderDots(); }

    function updateClampLimits(){ const viewport = root.querySelector('.ss-viewport'); if(!viewport) return; const vpRect = viewport.getBoundingClientRect(); const trackWidth = track.scrollWidth || (slides.length * slideWidth); maxTranslate = Math.max(0, trackWidth - vpRect.width - RIGHT_GUTTER); }
    const CENTER_BIAS = 0.92; // bias center toward the right so slider appears more right-aligned
    const RIGHT_GUTTER = 120; // smaller gutter so slider can reach further right but not fully to edge
    function moveTo(index){ // center the slide at `index`, biased slightly to the left of center
      index = Math.max(0, Math.min(index, slides.length-1));
      const viewport = root.querySelector('.ss-viewport');
      const vpRect = viewport.getBoundingClientRect();
      const rect = slides[index].getBoundingClientRect();
      // compute the offset so the center of slide aligns slightly left of viewport center
      const centerViewport = vpRect.width * CENTER_BIAS;
      const slideCenter = (rect.left - vpRect.left) + rect.width/2;
      let shift = slideCenter - centerViewport;
      // clamp shift so the track doesn't translate beyond its natural bounds
      // use precomputed maxTranslate (positive number). translateX = -shift, so allowed range is [-maxTranslate, 0]
      updateClampLimits();
      shift = Math.max(0, Math.min(shift, maxTranslate));
      track.style.transition = 'transform 1.6s cubic-bezier(.2,.9,.3,1)';
      track.style.transform = 'translateX(' + (-shift) + 'px)';
      idx = index;
      // assign classes for styling
      slides.forEach((s,i)=>{ s.classList.remove('active','prev','next','near-prev','near-next','inactive'); });
      slides.forEach((s,i)=>{ if(i < idx-2 || i > idx+2) s.classList.add('inactive'); });
      if(slides[idx]) slides[idx].classList.add('active');
      if(slides[idx-1]) slides[idx-1].classList.add('prev');
      if(slides[idx+1]) slides[idx+1].classList.add('next');
      if(slides[idx-2]) slides[idx-2].classList.add('near-prev');
      if(slides[idx+2]) slides[idx+2].classList.add('near-next');
      updateActiveDot(); updateBackgroundForIndex(idx);
    }
    function next(){ moveTo(idx >= slides.length-1 ? 0 : idx + 1); }
    function prev(){ moveTo(idx <= 0 ? slides.length-1 : idx - 1); }
    // autoplay moves slides so new cards appear from the RIGHT -> LEFT direction
    function startAutoplay(){ stopAutoplay(); if(!autoplay) return; timer = setInterval(prev, AUTOPLAY_MS); }
    function stopAutoplay(){ if(timer) clearInterval(timer); timer = null; }

    // hide dots (user requested removal)
    if(dotsWrap) dotsWrap.style.display = 'none';
    function renderDots(){ /* disabled - dots hidden */ }
    function updateActiveDot(){ /* no-op since dots hidden */ }

    // Update section background based on a representative slide (center of visible area)
    const bgEl = root.querySelector('.ss-bg');
    // use custom gradient backgrounds instead of using the slide cover image
    // If the slider element includes a `data-bg` attribute, that image will be used
    // as the panel background. Otherwise pick a subtle gradient from the palette.
    const _bgGradients = [
      'linear-gradient(135deg, rgba(75,123,236,0.14) 0%, rgba(120,180,255,0.06) 100%)',
      'linear-gradient(135deg, rgba(255,170,120,0.14) 0%, rgba(255,220,180,0.06) 100%)',
      'linear-gradient(135deg, rgba(120,200,150,0.14) 0%, rgba(200,240,210,0.06) 100%)',
      'linear-gradient(135deg, rgba(200,160,255,0.14) 0%, rgba(230,210,255,0.06) 100%)',
      'linear-gradient(135deg, rgba(240,200,110,0.12) 0%, rgba(255,240,210,0.05) 100%)'
    ];
    function updateBackgroundForIndex(index){
      if(!bgEl || !slides.length) return;
      // prefer an explicit background provided via `data-bg` on the root element
      const explicit = (root.dataset && root.dataset.bg) ? root.dataset.bg.trim() : '';
      if(explicit){
        // use the provided image URL
        bgEl.style.backgroundImage = 'url("' + explicit.replace(/"/g,'') + '")';
        bgEl.style.backgroundSize = 'cover';
        bgEl.style.backgroundPosition = 'center center';
        bgEl.style.opacity = '0.18';
        return;
      }
      // fallback to picking a stable gradient based on index so backgrounds cycle
      const g = _bgGradients[Math.abs(index) % _bgGradients.length];
      bgEl.style.backgroundImage = g;
      bgEl.style.backgroundSize = 'auto';
      bgEl.style.backgroundPosition = 'center center';
      bgEl.style.opacity = '0.14';
    }

    // improved drag handling using current translate
    let isDown=false, startX=0, startTranslate=0, startTime=0;
    function clampTranslate(val){ // ensure translateX stays within [-maxTranslate, 0]
      if(typeof val !== 'number') return 0; updateClampLimits(); return Math.max(-maxTranslate, Math.min(0, val)); }
    function getCurrentTranslate(){ const st = getComputedStyle(track).transform; if(!st || st === 'none') return 0; try{ const m = new WebKitCSSMatrix(st); return m.m41; }catch(e){ const vals = st.split('(')[1].split(')')[0].split(','); return parseFloat(vals[4]) || 0; } }
    track.addEventListener('pointerdown', e=>{ isDown = true; startX = e.clientX; startTranslate = getCurrentTranslate(); startTime = Date.now(); track.setPointerCapture(e.pointerId); track.style.transition = 'none'; stopAutoplay(); });
    window.addEventListener('pointermove', e=>{ if(!isDown) return; const dx = e.clientX - startX; let offset = startTranslate + dx; // clamp while dragging so content won't be moved out of bounds
      offset = clampTranslate(offset); track.style.transform = 'translateX('+ offset +'px)'; });
    window.addEventListener('pointerup', e=>{
      if(!isDown) return;
      isDown = false;
      track.style.transition = 'transform 1.6s cubic-bezier(.2,.9,.3,1)';
      const dx = e.clientX - startX;
      const threshold = Math.max(20, slideWidth * 0.18);
      const dt = Date.now() - (startTime || 0);
      const QUICK_TAP_MS = 300; // increase quick-tap window to reduce accidental moves

      // If the pointerup happened on an interactive part of the card (title/author/meta/cover),
      // treat it as an intentional click and do NOT force a slide change here. This prevents
      // immediate slide movements when users click the title/author area.
      try{
        var tgt = e.target;
        if(tgt && typeof tgt.closest === 'function'){
          if(tgt.closest('.ss-meta, .ss-card, .ss-title, .ss-author, .ss-cover')){
            // allow native click handling (links) and restart autoplay, but do not change slides
            startAutoplay();
            return;
          }
        }
      }catch(err){ /* ignore */ }

      if(Math.abs(dx) > threshold){
        if(dx < 0) { next(); } else { prev(); }
      } else {
        if(dt < QUICK_TAP_MS){
          // Quick tap - don't force a re-center to avoid accidental immediate moves.
        } else {
          // Longer press without much movement: re-center to nearest index based on current translate
          const curT = getCurrentTranslate(); // negative or zero
          // estimate index from translate: positive shift = -translate
          const est = Math.round(( -curT ) / (slideWidth || 1));
          moveTo(Math.max(0, Math.min(est, slides.length-1)));
        }
      }
      startAutoplay();
    });
    root.addEventListener('mouseleave', ()=>{ if(isDown) { isDown=false; moveTo(idx); startAutoplay(); } });

    if(prevBtn) prevBtn.addEventListener('click', ()=>{ prev(); startAutoplay(); });
    // make the RIGHT button follow the autoplay direction (right-to-left),
    // so clicking the visible right control advances the same way autoplay does.
    if(nextBtn) nextBtn.addEventListener('click', ()=>{ prev(); startAutoplay(); });
    // Keep autoplay running even when hovered — do not stop on mouse enter

    let resizeTimer = null; window.addEventListener('resize', ()=> { clearTimeout(resizeTimer); resizeTimer = setTimeout(()=> { updateSizes(); }, 120); });

    setTimeout(()=> { updateSizes(); renderDots(); startAutoplay(); }, 50);
  }

  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initSmartSlider); else initSmartSlider();
})();
