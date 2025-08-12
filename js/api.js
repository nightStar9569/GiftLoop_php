// Simple API client for GiftLoop PHP backend
(function(window){
  function deriveBaseFromLocation() {
    const parts = window.location.pathname.split('/').filter(Boolean);
    // remove last segment (file name)
    parts.pop();
    return '/' + parts.join('/');
  }

  let baseCandidates = [];
  if (window.APP_BASE && typeof window.APP_BASE === 'string') {
    baseCandidates.push(String(window.APP_BASE));
  }
  baseCandidates.push(deriveBaseFromLocation());
  baseCandidates.push('');

  let BASE = baseCandidates[0].replace(/\/$/,'');
  let API_BASE = `${BASE}/api`;
  let useFallback = false; // when true, use index.php?r=route

  function buildUrl(path) {
    const clean = path.startsWith('/') ? path : '/' + path;
    if (!useFallback) return `${API_BASE}${clean}`;
    const route = clean.replace(/^\//,'');
    return `${API_BASE}/index.php?r=${encodeURIComponent(route)}`;
  }

  async function tryHealthAt(base) {
    const url = `${base.replace(/\/$/,'')}/api/health`;
    try {
      const res = await fetch(url, { method: 'GET', credentials: 'include' });
      if (res.ok) return true;
    } catch (_) {}
    return false;
  }

  async function detectBaseAndFallback() {
    // Probe bases
    for (const candidate of baseCandidates) {
      const norm = String(candidate || '').replace(/\/$/,'');
      if (await tryHealthAt(norm)) {
        BASE = norm;
        API_BASE = `${BASE}/api`;
        useFallback = false;
        return;
      }
    }
    // If none succeeded, stick to first base and use index.php fallback
    BASE = baseCandidates[0].replace(/\/$/,'');
    API_BASE = `${BASE}/api`;
    useFallback = true;
  }

  async function request(path, options = {}) {
    const opts = Object.assign({
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
    }, options);

    async function doFetch() {
      const url = buildUrl(path);
      const res = await fetch(url, opts).catch(() => null);
      return res;
    }

    // First attempt
    let res = await doFetch();
    // If missing and not using fallback, switch to fallback and retry once
    if ((!res || res.status === 404) && !useFallback) {
      useFallback = true;
      res = await doFetch();
    }
    if (!res) throw new Error('Network error');
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      const message = data && data.error ? data.error : `HTTP ${res.status}`;
      throw new Error(message);
    }
    return data;
  }

  // Kick off detection (fire and forget)
  detectBaseAndFallback();

  const api = {
    me() { return request('/auth/me', { method: 'GET' }); },
    login({ email, password }) { return request('/auth/login', { method: 'POST', body: JSON.stringify({ email, password }) }); },
    register({ email, password, firstName, lastName, birthDate }) { return request('/auth/register', { method: 'POST', body: JSON.stringify({ email, password, firstName, lastName, birthDate }) }); },
    logout() { return request('/auth/logout', { method: 'POST' }); },
    oauthGoogleStart() { window.location.href = buildUrl('/auth/google/start'); },

    products() { return request('/products', { method: 'GET' }); },
    checkout(items) { return request('/checkout', { method: 'POST', body: JSON.stringify({ items }) }); },

    gifts() { return request('/gifts', { method: 'GET' }); },
    giftById(id) { return request(`/gifts/${encodeURIComponent(id)}`, { method: 'GET' }); },
    sendGift(payload) { return request('/gifts/send', { method: 'POST', body: JSON.stringify(payload) }); },
    acceptGift(giftId) { return request('/gifts/accept', { method: 'POST', body: JSON.stringify({ giftId }) }); },
    rejectGift(giftId) { return request('/gifts/reject', { method: 'POST', body: JSON.stringify({ giftId }) }); },

    subscribe({ plan, price }) { return request('/subscription', { method: 'POST', body: JSON.stringify({ plan, price }) }); },

    // Admin
    adminUsers() { return request('/admin/users', { method: 'GET' }); },
    adminGiftDelete(giftId) { return request('/gifts/delete', { method: 'POST', body: JSON.stringify({ giftId }) }); },
    adminGiftToggle(giftId) { return request('/gifts/toggle', { method: 'POST', body: JSON.stringify({ giftId }) }); },

    // Corporate
    corporateRiver() { return request('/corporate/river', { method: 'GET' }); },
    corporateDraw() { return request('/corporate/draw', { method: 'POST' }); },
    corporateAwards() { return request('/corporate/awards', { method: 'GET' }); },
    corporateReview({ giftId, rating, comment }) { return request('/corporate/reviews', { method: 'POST', body: JSON.stringify({ giftId, rating, comment }) }); },
  };

  window.giftApi = api;
})(window);
