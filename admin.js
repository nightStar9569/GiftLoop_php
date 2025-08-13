// Admin Dashboard
document.addEventListener('DOMContentLoaded', async () => {
  // Auth guard with backend sync first
  try {
    const me = await (window.giftApi?.me?.());
    if (me && me.user) {
      localStorage.setItem('userData', JSON.stringify(me.user));
      localStorage.setItem('isLoggedIn', 'true');
    }
  } catch (_) {}

  const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
  const user = JSON.parse(localStorage.getItem('userData') || '{}');
  const isAdmin = Boolean(user.is_admin || user.isAdmin);
  if (!isLoggedIn || !isAdmin) {
    window.location.href = 'auth.php';
    return;
  }

  // Show user menu
  const userMenu = document.querySelector('.user-menu');
  const loginBtn = document.getElementById('login-btn');
  if (userMenu) userMenu.style.display = 'block';
  if (loginBtn) loginBtn.style.display = 'none';

  initTabs();
  initGiftsPanel();
  initCorporatePanel();
  initUsersPanel();
  initGiftForm();
  initCorpForm();
  initUserForm();
});

function initTabs() {
  const tabs = document.querySelectorAll('.admin-tab');
  const panels = document.querySelectorAll('.admin-panel');
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      panels.forEach(p => p.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById(`tab-${tab.dataset.tab}`).classList.add('active');
    });
  });
}

// Gift create/update form
function initGiftForm() {
  const form = document.getElementById('giftForm');
  if (!form) return;
  const idEl = document.getElementById('giftFormId');
  const nameEl = document.getElementById('giftFormName');
  const priceEl = document.getElementById('giftFormPrice');
  const catEl = document.getElementById('giftFormCategory');
  const statusEl = document.getElementById('giftFormStatus');
  const descEl = document.getElementById('giftFormDesc');
  const superEl = document.getElementById('giftFormSuper');
  const resetBtn = document.getElementById('giftFormReset');
  const imgEl = document.getElementById('giftFormImage');
  const imgFile = document.getElementById('giftFormImageFile');
  if (imgFile) {
    imgFile.addEventListener('change', async () => {
      const file = imgFile.files && imgFile.files[0];
      if (!file) return;
      try {
        showNotification('画像をアップロード中...', 'info');
        const fd = new FormData();
        fd.append('file', file);
        const url = await uploadAdminFile(fd);
        if (imgEl) imgEl.value = url;
        showNotification('アップロード完了', 'success');
      } catch (e) {
        showNotification(e.message || 'アップロードに失敗しました。', 'error');
      } finally {
        imgFile.value = '';
      }
    });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
      name: (nameEl?.value || '').trim(),
      price: Number(priceEl?.value || 0),
      category: catEl?.value || 'other',
      description: (descEl?.value || '').trim() || null,
      imageUrl: (imgEl?.value || '').trim() || null,
      isSuper: !!(superEl && superEl.checked),
      status: statusEl?.value || 'created',
    };
    if (!payload.name || !payload.price) {
      showNotification('名前と価格を入力してください。', 'error');
      return;
    }
    try {
      const id = (idEl?.value || '').trim();
      if (id) {
        await giftApi.adminGiftUpdate(id, payload);
      } else {
        const res = await giftApi.adminGiftCreate(payload);
        if (idEl) idEl.value = String(res.id || '');
      }
      showNotification('保存しました。', 'success');
      // Refresh list
      document.getElementById('giftSearch')?.dispatchEvent(new Event('input'));
    } catch (e) {
      showNotification(e.message || '保存に失敗しました。', 'error');
    }
  });

  resetBtn?.addEventListener('click', () => {
    if (idEl) idEl.value = '';
    if (nameEl) nameEl.value = '';
    if (priceEl) priceEl.value = '';
    if (catEl) catEl.value = 'other';
    if (statusEl) statusEl.value = 'created';
    if (descEl) descEl.value = '';
    if (imgEl) imgEl.value = '';
    if (superEl) superEl.checked = false;
  });
}

async function uploadAdminFile(formData){
  // Try using API builder directly to preserve cookies
  const makeUrl = (path) => {
    try {
      // reuse buildUrl by crafting a temporary request
      return (function(){
        const parts = window.location.pathname.split('/').filter(Boolean); parts.pop();
        const base = '/' + parts.join('/');
        return `${base}/api/admin/upload`;
      })();
    } catch(_) { return '/GiftLoop/api/admin/upload'; }
  };
  const resp = await fetch(makeUrl('/admin/upload'), { method: 'POST', body: formData, credentials: 'include' });
  const data = await resp.json().catch(()=>({}));
  if (!resp.ok) throw new Error(data && data.error ? data.error : `HTTP ${resp.status}`);
  return data.url;
}

// Corporate gift create/update form
function initCorpForm() {
  const form = document.getElementById('corpForm');
  if (!form) return;
  const idEl = document.getElementById('corpFormId');
  const titleEl = document.getElementById('corpTitle');
  const catEl = document.getElementById('corpCategory');
  const spdEl = document.getElementById('corpSupplyPerDay');
  const totalEl = document.getElementById('corpTotalSupply');
  const startEl = document.getElementById('corpStartAt');
  const endEl = document.getElementById('corpEndAt');
  const descEl = document.getElementById('corpDescription');
  const superEl = document.getElementById('corpIsSuper');
  const resetBtn = document.getElementById('corpFormReset');
  const imgEl = document.getElementById('corpImageUrl');
  const imgFile = document.getElementById('corpImageFile');
  if (imgFile) {
    imgFile.addEventListener('change', async () => {
      const file = imgFile.files && imgFile.files[0];
      if (!file) return;
      try {
        showNotification('画像をアップロード中...', 'info');
        const fd = new FormData();
        fd.append('file', file);
        const url = await uploadAdminFile(fd);
        if (imgEl) imgEl.value = url;
        showNotification('アップロード完了', 'success');
      } catch (e) {
        showNotification(e.message || 'アップロードに失敗しました。', 'error');
      } finally {
        imgFile.value = '';
      }
    });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
      title: (titleEl?.value || '').trim(),
      category: catEl?.value || 'other',
      supplyPerDay: Number(spdEl?.value || 0),
      totalSupply: totalEl?.value ? Number(totalEl.value) : null,
      startAt: startEl?.value || '',
      endAt: endEl?.value || '',
      description: (descEl?.value || '').trim() || null,
      imageUrl: (imgEl?.value || '').trim() || null,
      isSuper: !!(superEl && superEl.checked),
    };
    if (!payload.title || !payload.startAt || !payload.endAt) {
      showNotification('タイトルと期間を入力してください。', 'error');
      return;
    }
    try {
      const id = (idEl?.value || '').trim();
      if (id) {
        await giftApi.adminCorporateUpdate(id, payload);
      } else {
        const res = await giftApi.adminCorporateCreate(payload);
        if (idEl) idEl.value = String(res.id || '');
      }
      showNotification('保存しました。', 'success');
      // Refresh list
      const list = document.getElementById('corporateGiftsList');
      if (list) { list.innerHTML = ''; }
      // Re-render
      initCorporatePanel();
    } catch (e) {
      showNotification(e.message || '保存に失敗しました。', 'error');
    }
  });

  resetBtn?.addEventListener('click', () => {
    if (idEl) idEl.value = '';
    if (titleEl) titleEl.value = '';
    if (catEl) catEl.value = 'other';
    if (spdEl) spdEl.value = '';
    if (totalEl) totalEl.value = '';
    if (startEl) startEl.value = '';
    if (endEl) endEl.value = '';
    if (descEl) descEl.value = '';
    if (imgEl) imgEl.value = '';
    if (superEl) superEl.checked = false;
  });
}

// User create form
function initUserForm() {
  const form = document.getElementById('userForm');
  if (!form) return;
  const emailEl = document.getElementById('userEmail');
  const passEl = document.getElementById('userPassword');
  const firstEl = document.getElementById('userFirstName');
  const lastEl = document.getElementById('userLastName');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = (emailEl?.value || '').trim();
    const password = (passEl?.value || '').trim();
    const firstName = (firstEl?.value || '').trim();
    const lastName = (lastEl?.value || '').trim();
    if (!email || !password) { showNotification('メールとパスワードを入力してください。','error'); return; }
    try {
      await giftApi.adminUserCreate({ email, password, firstName, lastName });
      showNotification('ユーザーを作成しました。','success');
      if (emailEl) emailEl.value = '';
      if (passEl) passEl.value = '';
      if (firstEl) firstEl.value = '';
      if (lastEl) lastEl.value = '';
      document.getElementById('refreshData')?.click();
    } catch (e) {
      showNotification(e.message || '作成に失敗しました。','error');
    }
  });
}

// Gifts Management
function initGiftsPanel() {
  const giftsList = document.getElementById('giftsList');
  const searchEl = document.getElementById('giftSearch');
  const sortEl = document.getElementById('giftSort');
  const statusFiltersEl = document.getElementById('giftStatusFilters');
  const categoryFiltersEl = document.getElementById('giftCategoryFilters');

  const statusDefs = [
    { key: 'all', label: 'すべて' },
    { key: 'created', label: '作成済' },
    { key: 'sent', label: '送信済' },
    { key: 'pending', label: '保留中' },
    { key: 'accepted', label: '受取済' },
    { key: 'rejected', label: '拒否済' },
    { key: 'owned', label: '所有済' },
  ];
  const categories = [
    { key: 'all', label: '全カテゴリ' },
    { key: 'food', label: '食品・スイーツ' },
    { key: 'fashion', label: 'ファッション' },
    { key: 'beauty', label: '美容・コスメ' },
    { key: 'electronics', label: '家電・電子機器' },
    { key: 'books', label: '書籍・文具' },
    { key: 'hobby', label: '趣味・スポーツ' },
    { key: 'home', label: '生活用品' },
    { key: 'other', label: 'その他' },
  ];

  let activeStatus = 'all';
  let activeCategory = 'all';

  function renderFilters() {
    if (statusFiltersEl) {
      statusFiltersEl.innerHTML = '';
      statusDefs.forEach(s => {
        const btn = document.createElement('button');
        btn.className = 'chip' + (activeStatus === s.key ? ' active' : '');
        btn.textContent = s.label;
        btn.addEventListener('click', () => { activeStatus = s.key; apply(); renderFilters(); });
        statusFiltersEl.appendChild(btn);
      });
    }
    if (categoryFiltersEl) {
      categoryFiltersEl.innerHTML = '';
      categories.forEach(c => {
        const btn = document.createElement('button');
        btn.className = 'chip' + (activeCategory === c.key ? ' active' : '');
        btn.textContent = c.label;
        btn.addEventListener('click', () => { activeCategory = c.key; apply(); renderFilters(); });
        categoryFiltersEl.appendChild(btn);
      });
    }
  }

  function apply() {
    const q = (searchEl?.value || '').trim().toLowerCase();
    (async () => {
      try {
        const res = await giftApi.adminGifts();
        let items = (res.items || []).filter(g => {
          const matchesText = !q || (g.name||'').toLowerCase().includes(q);
          const matchesStatus = activeStatus === 'all' || g.status === activeStatus;
          const matchesCategory = activeCategory === 'all' || g.category === activeCategory;
          return matchesText && matchesStatus && matchesCategory;
        });

        switch (sortEl?.value) {
          case 'price_desc': items.sort((a,b)=> (a.price||0) < (b.price||0) ? 1 : -1); break;
          case 'price_asc': items.sort((a,b)=> (a.price||0) - (b.price||0)); break;
          case 'name_asc': items.sort((a,b)=> (a.name||'').localeCompare(b.name||'')); break;
          case 'recent':
          default:
            items.sort((a,b)=> new Date(b.created_at||0)-new Date(a.created_at||0));
        }
        renderList(items);
      } catch (e) {
        showNotification(e.message || 'ギフト一覧の取得に失敗しました。', 'error');
        renderList([]);
      }
    })();
  }

  function renderList(items) {
    if (!giftsList) return;
    giftsList.innerHTML = '';
    if (!items.length) {
      giftsList.innerHTML = `<div class="empty-card">該当するギフトがありません</div>`;
      return;
    }
    items.forEach(g => {
      const row = document.createElement('div');
      row.className = 'admin-row';
      row.innerHTML = `
        <div class="admin-row-main">
          ${g.image_url ? `<div class="thumb" style="width:56px;height:56px;border-radius:10px;overflow:hidden;flex:0 0 auto;background:#f3f4f6;display:flex;align-items:center;justify-content:center"><img src="${g.image_url}" alt="${g.name||''}" style="width:100%;height:100%;object-fit:cover" /></div>` : `<div class="icon" style="width:56px; height:56px; display:flex; align-items:center; justify-content:center; border-radius:10px; background:#f3f4f6; color:${g.isSuper?'#ffd700':'#e91e63'};"><i class="fas ${g.isSuper?'fa-crown':'fa-gift'}"></i></div>`}
          <div class="meta">
            <div class="title">${g.name||'-'}</div>
            <div class="sub">¥${(g.price||0).toLocaleString()} ・ ${getCategoryText(g.category)} ・ 状態: ${getStatusText(g.status)}</div>
          </div>
        </div>
        <div class="admin-row-actions">
          <button class="btn-primary" data-action="edit">編集</button>
          <button class="btn-primary" data-action="delete">削除</button>
        </div>`;

      row.querySelector('[data-action="delete"]').addEventListener('click', async () => {
        if (!confirm('このギフトを削除しますか？')) return;
        try { await giftApi.adminGiftDelete(g.id); showNotification('ギフトを削除しました。','success'); apply(); } catch(e){ showNotification(e.message||'削除に失敗しました。','error'); }
      });
      row.querySelector('[data-action="edit"]').addEventListener('click', async () => openGiftEditModal(g));

      giftsList.appendChild(row);
    });
  }

  // Modal handlers for gift editing
  function openGiftEditModal(gift){
    const modal = document.getElementById('giftEditModal');
    if (!modal) return;
    // Prefill
    assignValue('giftEditId', gift.id);
    assignValue('giftEditName', gift.name||'');
    assignValue('giftEditPrice', gift.price||0);
    setSelect('giftEditCategory', gift.category||'other');
    setSelect('giftEditStatus', gift.status||'created');
    assignValue('giftEditDesc', gift.description||'');
    assignValue('giftEditImage', gift.image_url||'');
    const superEl = document.getElementById('giftEditSuper');
    if (superEl) superEl.checked = !!gift.isSuper;

    // Upload handler
    const fileEl = document.getElementById('giftEditImageFile');
    if (fileEl && !fileEl._bound) {
      fileEl.addEventListener('change', async () => {
        const file = fileEl.files && fileEl.files[0]; if (!file) return;
        try {
          showNotification('画像をアップロード中...', 'info');
          const fd = new FormData(); fd.append('file', file);
          const url = await uploadAdminFile(fd);
          assignValue('giftEditImage', url);
          showNotification('アップロード完了', 'success');
        } catch(e){ showNotification(e.message||'アップロードに失敗しました。','error'); }
        fileEl.value='';
      });
      fileEl._bound = true;
    }

    // Submit handler
    const form = document.getElementById('giftEditForm');
    if (form && !form._bound) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = getValue('giftEditId');
        const payload = {
          name: getValue('giftEditName'),
          price: Number(getValue('giftEditPrice')||0),
          category: getSelect('giftEditCategory'),
          status: getSelect('giftEditStatus'),
          description: getValue('giftEditDesc') || null,
          imageUrl: getValue('giftEditImage') || null,
          isSuper: !!(document.getElementById('giftEditSuper')?.checked)
        };
        if (!payload.name || !payload.price) { showNotification('名前と価格を入力してください。','error'); return; }
        try {
          await giftApi.adminGiftUpdate(id, payload);
          showNotification('更新しました。','success');
          closeGiftEditModal();
          // Refresh list
          document.getElementById('giftSearch')?.dispatchEvent(new Event('input'));
        } catch(e){ showNotification(e.message||'更新に失敗しました。','error'); }
      });
      form._bound = true;
    }

    const cancelBtn = document.getElementById('giftEditCancel');
    if (cancelBtn && !cancelBtn._bound) {
      cancelBtn.addEventListener('click', closeGiftEditModal);
      cancelBtn._bound = true;
    }
    modal.style.display = 'flex';
  }

  function closeGiftEditModal(){
    const modal = document.getElementById('giftEditModal');
    if (modal) modal.style.display = 'none';
  }

  function assignValue(id, v){ const el = document.getElementById(id); if (el) el.value = v; }
  function getValue(id){ const el = document.getElementById(id); return el ? el.value : ''; }
  function setSelect(id, v){ const el = document.getElementById(id); if (el) el.value = v; }
  function getSelect(id){ const el = document.getElementById(id); return el ? el.value : ''; }

  searchEl?.addEventListener('input', apply);
  sortEl?.addEventListener('change', apply);
  renderFilters();
  apply();
}

// Corporate Gifts
function initCorporatePanel() {
  const listEl = document.getElementById('corporateGiftsList');
  async function render() {
    if (!listEl) return;
    try {
      const res = await giftApi.adminCorporate();
      const items = res.items || [];
      listEl.innerHTML = '';
      if (!items.length) { listEl.innerHTML = '<div class="empty-card">会社ギフトがありません</div>'; return; }
      items.forEach(c => {
        const row = document.createElement('div');
        row.className = 'admin-row';
        row.innerHTML = `
          <div class="admin-row-main">
            <div style="display:flex; gap:12px; align-items:center">
              ${c.image_url ? `<div class="thumb" style="width:56px;height:56px;border-radius:10px;overflow:hidden;flex:0 0 auto;background:#f3f4f6;display:flex;align-items:center;justify-content:center"><img src="${c.image_url}" alt="${c.title||''}" style="width:100%;height:100%;object-fit:cover" /></div>` : ''}
              <div>
                <div class="title">${c.title}</div>
                <div class="sub">カテゴリ: ${getCategoryText(c.category)} ・ 期間: ${c.start_at} 〜 ${c.end_at}</div>
              </div>
            </div>
          </div>
          <div class="admin-row-actions">
            <button class="btn-primary" data-action="edit">編集</button>
            <button class="btn-primary" data-action="delete">削除</button>
          </div>`;
        row.querySelector('[data-action="delete"]').addEventListener('click', async () => {
          if (!confirm('この会社ギフトを削除しますか？')) return;
          try { await giftApi.adminCorporateDelete(c.id); showNotification('削除しました。','success'); render(); } catch(e){ showNotification(e.message||'削除に失敗しました。','error'); }
        });
        row.querySelector('[data-action="edit"]').addEventListener('click', () => openCorpEditModal(c));
        listEl.appendChild(row);
      });
    } catch (e) {
      showNotification(e.message||'取得に失敗しました。','error');
    }
  }
  render();
}

// Corporate edit modal (top-level)
function openCorpEditModal(c){
  const modal = document.getElementById('corpEditModal');
  if (!modal) return;
  const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = v; };
  const getVal = (id) => { const el = document.getElementById(id); return el ? el.value : ''; };
  const setSel = (id, v) => { const el = document.getElementById(id); if (el) el.value = v; };

  setVal('corpEditId', c.id);
  setVal('corpEditTitle', c.title||'');
  setSel('corpEditCategory', c.category||'other');
  setVal('corpEditSupplyPerDay', c.supply_per_day||'');
  setVal('corpEditTotalSupply', c.total_supply||'');
  setVal('corpEditStartAt', (c.start_at||'').replace(' ','T'));
  setVal('corpEditEndAt', (c.end_at||'').replace(' ','T'));
  setVal('corpEditDescription', c.description||'');
  setVal('corpEditImageUrl', c.image_url||'');
  const superEl = document.getElementById('corpEditSuper');
  if (superEl) superEl.checked = !!c.is_super;

  const fileEl = document.getElementById('corpEditImageFile');
  if (fileEl && !fileEl._bound) {
    fileEl.addEventListener('change', async () => {
      const file = fileEl.files && fileEl.files[0]; if (!file) return;
      try {
        showNotification('画像をアップロード中...', 'info');
        const fd = new FormData(); fd.append('file', file);
        const url = await uploadAdminFile(fd);
        setVal('corpEditImageUrl', url);
        showNotification('アップロード完了', 'success');
      } catch(e){ showNotification(e.message||'アップロードに失敗しました。','error'); }
      fileEl.value='';
    });
    fileEl._bound = true;
  }

  const form = document.getElementById('corpEditForm');
  if (form && !form._bound) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const id = getVal('corpEditId');
      const payload = {
        title: getVal('corpEditTitle'),
        category: getVal('corpEditCategory') || 'other',
        supplyPerDay: Number(getVal('corpEditSupplyPerDay')||0),
        totalSupply: getVal('corpEditTotalSupply') ? Number(getVal('corpEditTotalSupply')) : null,
        startAt: getVal('corpEditStartAt'),
        endAt: getVal('corpEditEndAt'),
        description: getVal('corpEditDescription') || null,
        imageUrl: getVal('corpEditImageUrl') || null,
        isSuper: !!(document.getElementById('corpEditSuper')?.checked)
      };
      if (!payload.title || !payload.startAt || !payload.endAt) { showNotification('必須項目を入力してください。','error'); return; }
      try {
        await giftApi.adminCorporateUpdate(id, payload);
        showNotification('更新しました。','success');
        closeCorpEditModal();
        // Refresh list
        const list = document.getElementById('corporateGiftsList');
        if (list) list.innerHTML = '';
        // re-render
        initCorporatePanel();
      } catch(e){ showNotification(e.message||'更新に失敗しました。','error'); }
    });
    form._bound = true;
  }

  const cancelBtn = document.getElementById('corpEditCancel');
  if (cancelBtn && !cancelBtn._bound) {
    cancelBtn.addEventListener('click', closeCorpEditModal);
    cancelBtn._bound = true;
  }

  modal.style.display = 'flex';
}

function closeCorpEditModal(){
  const modal = document.getElementById('corpEditModal');
  if (modal) modal.style.display = 'none';
}

// Users Monitoring
function initUsersPanel() {
  const statsEl = document.getElementById('userStats');
  const tableBody = document.querySelector('#usersTable tbody');
  const refreshBtn = document.getElementById('refreshData');
  const exportBtn = document.getElementById('exportData');

  async function fetchUsers() {
    const res = await giftApi.adminUsers();
    return res.items || [];
  }

  async function render() {
    const users = await fetchUsers();
    const totalSent = users.reduce((sum,u)=> sum+(u.gifts_sent||0), 0);
    const totalReceived = users.reduce((sum,u)=> sum+(u.gifts_received||0), 0);
    if (statsEl) statsEl.innerHTML = `送信合計: <strong>${totalSent}</strong> ・ 受信合計: <strong>${totalReceived}</strong> ・ ユーザー数: <strong>${users.length}</strong>`;

    tableBody.innerHTML = '';
    users.forEach(u => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${(u.first_name||'') + ' ' + (u.last_name||'')}</td>
        <td>${u.email}</td>
        <td>${u.gifts_sent||0}</td>
        <td>${u.gifts_received||0}</td>
        <td>${u.is_admin? '管理者' : '-'}</td>
      `;
      tr.addEventListener('click', async () => {
        const isAdmin = confirm('管理者権限を切り替えますか？ (OKで切替)');
        if (isAdmin) {
          try { await giftApi.adminUserToggleAdmin(u.id); showNotification('権限を更新しました。','success'); render(); } catch(e){ showNotification(e.message||'更新に失敗しました。','error'); }
        }
      });
      tableBody.appendChild(tr);
    });
  }

  refreshBtn?.addEventListener('click', render);
  exportBtn?.addEventListener('click', () => {
    const rows = [['ユーザー','メール','送信数','受信数','最終活動']];
    document.querySelectorAll('#usersTable tbody tr').forEach(tr => {
      const cols = Array.from(tr.querySelectorAll('td')).map(td => td.textContent.replace(/\n/g,' ').trim());
      rows.push(cols);
    });
    const csv = rows.map(r => r.map(cell => '"'+(cell||'').replace(/"/g,'\"')+'"').join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = 'users.csv'; a.click();
    URL.revokeObjectURL(url);
    showNotification('CSVを書き出しました。','success');
  });

  render();
}

// Helper
function getStatusText(status) {
  const map = {
    created: '作成済',
    sent: '送信済',
    pending: '保留中',
    accepted: '受取済',
    rejected: '拒否済',
    owned: '所有済'
  };
  return map[String(status || '')] || '-';
}
function getCategoryText(category) {
  const categories = {
    food: '食品・スイーツ',
    fashion: 'ファッション',
    beauty: '美容・コスメ',
    electronics: '家電・電子機器',
    books: '書籍・文具',
    hobby: '趣味・スポーツ',
    home: '生活用品',
    other: 'その他'
  };
  return categories[category] || 'その他';
} 
 