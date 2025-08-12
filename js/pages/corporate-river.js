(function(){
  async function fetchRiver(){
    const { items } = await giftApiRequest('/corporate/river', { method: 'GET' });
    const grid = document.getElementById('activeCorporateGifts');
    if (!grid) return;
    grid.innerHTML = '';
    (items || []).forEach(item => {
      const card = document.createElement('div');
      card.className = 'recent-gift-card';
      card.innerHTML = `<div class="gift-card"><div class="gift-icon"><i class="fas fa-gift"></i></div><div class="gift-info"><h4>${escapeHtml(item.title)}</h4><div class="gift-category">${escapeHtml(item.category)}</div></div></div>`;
      grid.appendChild(card);
    });
  }

  async function fetchInfo(){
    // We infer ticket count by attempting a HEADless call added to API? For now, show "-" and rely on attempt feedback
    document.getElementById('ticketCount')?.innerText = '-';
    try {
      // Ping me to ensure session; optional
      await (window.giftApi?.me?.() || Promise.resolve());
    } catch(_){}
    // Daily free: best-effort
    document.getElementById('dailyFree')?.innerText = 'あり';
  }

  async function draw(){
    try {
      const res = await giftApiRequest('/corporate/draw', { method: 'POST' });
      if (res.result === 'win') {
        showNotification('当選しました！企業ギフトが付与されました。', 'success');
      } else {
        showNotification('残念！また明日チャレンジしてください。', 'info');
      }
    } catch (e) {
      showNotification(e.message || '抽選に失敗しました。', 'error');
    }
  }

  function escapeHtml(str){
    return String(str || '').replace(/[&<>"]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;'}[s]||s));
  }

  async function giftApiRequest(path, opts){
    // Use existing giftApi when available
    if (window.giftApi && path === '/corporate/river' && !window.giftApi.river) {
      // Fallback to local fetch builder
      return requestRaw(path, opts);
    }
    return requestRaw(path, opts);
  }

  async function requestRaw(path, options){
    const req = window.giftApi ? window.giftApi : null;
    if (!req) {
      // Use js/api.js buildUrl via a tiny shim
      const resp = await fetch('/GiftLoop/api/index.php?r=' + encodeURIComponent(path.replace(/^\//,'')), Object.assign({ headers: { 'Content-Type': 'application/json' }, credentials: 'include' }, options));
      const data = await resp.json().catch(()=>({}));
      if (!resp.ok) throw new Error(data && data.error ? data.error : `HTTP ${resp.status}`);
      return data;
    }
    // If giftApi exists but no corporate methods, still call raw
    const resp = await fetch('/GiftLoop/api/index.php?r=' + encodeURIComponent(path.replace(/^\//,'')), Object.assign({ headers: { 'Content-Type': 'application/json' }, credentials: 'include' }, options));
    const data = await resp.json().catch(()=>({}));
    if (!resp.ok) throw new Error(data && data.error ? data.error : `HTTP ${resp.status}`);
    return data;
  }

  document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('drawNow')?.addEventListener('click', draw);
    fetchInfo();
    fetchRiver();
  });
})(); 