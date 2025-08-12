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
        const res = await giftApi.gifts();
        let items = (res.items || []).filter(g => {
          const matchesText = !q ||
            (g.name||'').toLowerCase().includes(q) ||
            (g.senderName||'').toLowerCase().includes(q) ||
            (g.recipientName||'').toLowerCase().includes(q) ||
            (g.recipientEmail||'').toLowerCase().includes(q);
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
            items.sort((a,b)=> new Date(b.sent_at||b.created_at||0)-new Date(a.sent_at||a.created_at||0));
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
          <div class="icon" style="width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:10px; background:#f3f4f6; color:${g.isSuper?'#ffd700':'#e91e63'};">
            <i class="fas ${g.isSuper?'fa-crown':'fa-gift'}"></i>
          </div>
          <div class="meta">
            <div class="title">${g.name||'-'}</div>
            <div class="sub">¥${(g.price||0).toLocaleString()} ・ ${getCategoryText(g.category)} ・ 状態: ${g.status||'-'}</div>
          </div>
        </div>
        <div class="admin-row-actions">
          <button class="btn-primary" data-action="view">詳細</button>
          <button class="btn-primary" data-action="toggle-status">状態変更</button>
          <button class="btn-primary" data-action="delete">削除</button>
        </div>`;

      row.querySelector('[data-action="delete"]').addEventListener('click', () => deleteGift(g.id));
      row.querySelector('[data-action="toggle-status"]').addEventListener('click', () => toggleGiftStatus(g.id));
      row.querySelector('[data-action="view"]').addEventListener('click', () => viewGift(g));

      giftsList.appendChild(row);
    });
  }

  async function deleteGift(id) {
    try {
      await giftApi.adminGiftDelete(id);
      showNotification('ギフトを削除しました。', 'success');
      apply();
    } catch (e) {
      showNotification(e.message || '削除に失敗しました。', 'error');
    }
  }
  async function toggleGiftStatus(id) {
    try {
      const res = await giftApi.adminGiftToggle(id);
      showNotification(`状態を「${res.status}」に更新しました。`, 'info');
      apply();
    } catch (e) {
      showNotification(e.message || '更新に失敗しました。', 'error');
    }
  }
  function viewGift(g) {
    showNotification(`${g.name} | ¥${(g.price||0).toLocaleString()} | ${getCategoryText(g.category)} | 状態: ${g.status}`,'info');
  }

  searchEl?.addEventListener('input', apply);
  sortEl?.addEventListener('change', apply);
  renderFilters();
  apply();
}

// Corporate Gifts
function initCorporatePanel() {
  const addBtn = document.getElementById('addCorporateGift');
  const listEl = document.getElementById('corporateGiftsList');
  const key = 'corporateGifts';

  addBtn?.addEventListener('click', () => {
    const name = document.getElementById('corpName').value.trim();
    const category = document.getElementById('corpCategory').value;
    const price = Number(document.getElementById('corpPrice').value || 0);
    const quantity = Number(document.getElementById('corpQuantity').value || 1);
    if (!name) return showNotification('名称を入力してください。','error');
    const corpGifts = JSON.parse(localStorage.getItem(key) || '[]');
    corpGifts.push({ id: 'corp_'+Math.random().toString(36).slice(2), name, category, price, quantity, createdAt: new Date().toISOString() });
    localStorage.setItem(key, JSON.stringify(corpGifts));
    showNotification('会社ギフトを追加しました。','success');
    render();
  });

  function render() {
    const corpGifts = JSON.parse(localStorage.getItem(key) || '[]');
    listEl.innerHTML = '';
    if (!corpGifts.length) {
      listEl.innerHTML = '<div class="empty-card">会社ギフトがありません</div>';
      return;
    }
    corpGifts.forEach(c => {
      const row = document.createElement('div');
      row.className = 'admin-row';
      row.innerHTML = `
        <div class="admin-row-main">
          <div class="meta">
            <div class="title">${c.name}</div>
            <div class="sub">カテゴリー: ${getCategoryText(c.category)} ・ 価格: ¥${(c.price||0).toLocaleString()} ・ 数量: ${c.quantity}</div>
          </div>
        </div>
        <div class="admin-row-actions">
          <button class="btn-primary" data-action="delete">削除</button>
        </div>`;
      row.querySelector('[data-action="delete"]').addEventListener('click', () => {
        const next = (JSON.parse(localStorage.getItem(key) || '[]')).filter(x => x.id !== c.id);
        localStorage.setItem(key, JSON.stringify(next));
        showNotification('会社ギフトを削除しました。','success');
        render();
      });
      listEl.appendChild(row);
    });
  }

  render();
}

// Users Monitoring
function initUsersPanel() {
  const statsEl = document.getElementById('userStats');
  const tableBody = document.querySelector('#usersTable tbody');
  const refreshBtn = document.getElementById('refreshData');
  const exportBtn = document.getElementById('exportData');

  function fetchUsers() {
    // In this demo, we have only current user data and gifts to derive stats
    const currentUser = JSON.parse(localStorage.getItem('userData') || '{}');
    const gifts = JSON.parse(localStorage.getItem('gifts') || '[]');

    // Build a user map from sender/receiver data
    const map = new Map();
    function ensureUser(id, email, name) {
      if (!id) return;
      if (!map.has(id)) map.set(id, { id, email: email||'', name: name||'ユーザー', giftsSent:0, giftsReceived:0, lastActive: null });
      return map.get(id);
    }

    gifts.forEach(g => {
      const sender = ensureUser(g.senderId, '', g.senderName);
      const receiver = ensureUser(g.receiverId, '', '受取人');
      if (sender) { sender.giftsSent += 1; sender.lastActive = g.sentAt || g.createdAt || sender.lastActive; }
      if (receiver) { receiver.giftsReceived += 1; receiver.lastActive = g.receivedAt || receiver.lastActive; }
    });

    // Add current user to map as reference
    if (currentUser?.id) ensureUser(currentUser.id, currentUser.email, `${currentUser.firstName||''}`);

    return Array.from(map.values());
  }

  function render() {
    const users = fetchUsers();
    const totalSent = users.reduce((sum,u)=> sum+u.giftsSent, 0);
    const totalReceived = users.reduce((sum,u)=> sum+u.giftsReceived, 0);
    statsEl.innerHTML = `送信合計: <strong>${totalSent}</strong> ・ 受信合計: <strong>${totalReceived}</strong> ・ ユーザー数: <strong>${users.length}</strong>`;

    tableBody.innerHTML = '';
    users.forEach(u => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${u.name}</td>
        <td>${u.email}</td>
        <td>${u.giftsSent}</td>
        <td>${u.giftsReceived}</td>
        <td>${u.lastActive ? new Date(u.lastActive).toLocaleString('ja-JP') : '-'}</td>
      `;
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