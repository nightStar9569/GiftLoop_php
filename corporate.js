// Corporate Admin
document.addEventListener('DOMContentLoaded', () => {
  // Role guard: company admin
  const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
  const user = JSON.parse(localStorage.getItem('userData') || '{}');
  const isCompanyAdmin = Boolean(user.isCompanyAdmin || user.isCorporateAdmin);
  if (!isLoggedIn || !isCompanyAdmin) {
    window.location.href = 'auth.php';
    return;
  }

  // Show user menu
  const userMenu = document.querySelector('.user-menu');
  const loginBtn = document.getElementById('login-btn');
  if (userMenu) userMenu.style.display = 'block';
  if (loginBtn) loginBtn.style.display = 'none';

  initOfferForm();
  initOffersList();
  initNumbersModal();
});

function initOfferForm() {
  const offerImage = document.getElementById('offerImage');
  const offerImagePreview = document.getElementById('offerImagePreview');
  const resetBtn = document.getElementById('resetOffer');
  const saveBtn = document.getElementById('saveOffer');

  offerImage?.addEventListener('change', () => {
    const file = offerImage.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      offerImagePreview.innerHTML = `<img src="${e.target.result}" alt="プレビュー" style="max-width:220px; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,.08);" />`;
    };
    reader.readAsDataURL(file);
  });

  resetBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    document.getElementById('offerName').value = '';
    document.getElementById('offerCategory').value = 'other';
    document.getElementById('offerPrice').value = '';
    document.getElementById('offerSuper').value = 'false';
    document.getElementById('offerStart').value = '';
    document.getElementById('offerEnd').value = '';
    document.getElementById('offerDescription').value = '';
    document.getElementById('offerImage').value = '';
    offerImagePreview.innerHTML = '';
  });

  saveBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    const name = document.getElementById('offerName').value.trim();
    const category = document.getElementById('offerCategory').value;
    const price = Number(document.getElementById('offerPrice').value || 0);
    const isSuper = document.getElementById('offerSuper').value === 'true';
    const startAt = document.getElementById('offerStart').value;
    const endAt = document.getElementById('offerEnd').value;
    const description = document.getElementById('offerDescription').value.trim();
    const imgInput = document.getElementById('offerImage');

    if (!name) return showNotification('名称を入力してください。','error');
    if (!price || price < 0) return showNotification('価格を正しく入力してください。','error');
    if (startAt && endAt && new Date(startAt) >= new Date(endAt)) return showNotification('掲載期間が不正です。','error');

    const offers = JSON.parse(localStorage.getItem('corpOffers') || '[]');
    const imageData = imgInput.files && imgInput.files[0] ? offerImagePreview.querySelector('img')?.src || '' : '';
    const newOffer = {
      id: 'offer_'+Math.random().toString(36).slice(2),
      name, category, price, isSuper, startAt, endAt, description,
      imageUrl: imageData,
      status: '下書き', // 下書き | 公開中 | アーカイブ
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString()
    };
    offers.unshift(newOffer);
    localStorage.setItem('corpOffers', JSON.stringify(offers));
    showNotification('オファーを登録しました。','success');
    document.getElementById('resetOffer').click();
    renderOffers();
  });
}

function initOffersList() {
  const searchEl = document.getElementById('corpSearch');
  const statusFiltersEl = document.getElementById('corpStatusFilters');

  const statuses = [
    { key: 'all', label: 'すべて' },
    { key: 'draft', label: '下書き' },
    { key: 'published', label: '公開中' },
    { key: 'archived', label: 'アーカイブ' }
  ];

  let activeStatus = 'all';

  function renderFilters() {
    statusFiltersEl.innerHTML = '';
    statuses.forEach(s => {
      const btn = document.createElement('button');
      btn.className = 'chip' + (activeStatus === s.key ? ' active' : '');
      btn.textContent = s.label;
      btn.addEventListener('click', () => { activeStatus = s.key; renderOffers(); renderFilters(); });
      statusFiltersEl.appendChild(btn);
    });
  }

  searchEl?.addEventListener('input', renderOffers);

  renderFilters();
  renderOffers();
}

function renderOffers() {
  const list = document.getElementById('offersList');
  if (!list) return;
  const search = (document.getElementById('corpSearch')?.value || '').trim().toLowerCase();
  const activeStatus = Array.from(document.getElementById('corpStatusFilters').children).find(c => c.classList.contains('active'))?.textContent || 'すべて';

  let offers = JSON.parse(localStorage.getItem('corpOffers') || '[]');
  offers = offers.filter(o => {
    const matchesText = !search || o.name.toLowerCase().includes(search) || getCategoryText(o.category).toLowerCase().includes(search) || (o.status||'').toLowerCase().includes(search);
    const matchesStatus = activeStatus === 'すべて' || (o.status === mapStatusLabel(activeStatus));
    return matchesText && matchesStatus;
  });

  list.innerHTML = '';
  if (!offers.length) {
    list.innerHTML = '<div class="empty-card">該当するオファーがありません</div>';
    return;
  }

  offers.forEach(o => {
    const row = document.createElement('div');
    row.className = 'admin-row';
    row.innerHTML = `
      <div class="admin-row-main">
        <div class="icon" style="width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:10px; background:#f3f4f6; color:${o.isSuper? '#ffd700' : '#e91e63'};">
          <i class="fas ${o.isSuper? 'fa-crown':'fa-gift'}"></i>
        </div>
        <div class="meta">
          <div class="title">${o.name}</div>
          <div class="sub">¥${(o.price||0).toLocaleString()} ・ ${getCategoryText(o.category)} ・ 状態: ${mapStatusLabel(o.status)}${o.startAt? ' ・ 掲載: '+formatRange(o.startAt,o.endAt): ''}</div>
        </div>
      </div>
      <div class="admin-row-actions">
        <button class="btn-primary" data-action="numbers">当選番号</button>
        <button class="btn-primary" data-action="toggle">${o.status==='published'?'停止':'公開'}</button>
        <button class="btn-primary" data-action="edit">編集</button>
        <button class="btn-primary" data-action="delete">削除</button>
      </div>`;

    row.querySelector('[data-action="delete"]').addEventListener('click', () => deleteOffer(o.id));
    row.querySelector('[data-action="toggle"]').addEventListener('click', () => toggleOfferStatus(o.id));
    row.querySelector('[data-action="edit"]').addEventListener('click', () => editOffer(o.id));
    row.querySelector('[data-action="numbers"]').addEventListener('click', () => openNumbers(o.id));

    list.appendChild(row);
  });
}

function mapStatusLabel(keyOrLabel) {
  const map = { '下書き': '下書き', '公開中': '公開中', 'アーカイブ': 'アーカイブ', 'draft':'下書き','published':'公開中','archived':'アーカイブ' };
  return map[keyOrLabel] || 'draft';
}

function formatRange(start, end) {
  try {
    const s = new Date(start).toLocaleString('ja-JP');
    const e = end ? new Date(end).toLocaleString('ja-JP') : '';
    return e ? `${s} ~ ${e}` : `${s} ~`;
  } catch { return ''; }
}

function deleteOffer(id) {
  const offers = JSON.parse(localStorage.getItem('corpOffers') || '[]');
  localStorage.setItem('corpOffers', JSON.stringify(offers.filter(o => o.id !== id)));
  showNotification('オファーを削除しました。','success');
  renderOffers();
}
function toggleOfferStatus(id) {
  const offers = JSON.parse(localStorage.getItem('corpOffers') || '[]');
  const idx = offers.findIndex(o => o.id === id);
  if (idx<0) return;
  const status = offers[idx].status;
  offers[idx].status = status === 'published' ? 'archived' : 'published';
  offers[idx].updatedAt = new Date().toISOString();
  localStorage.setItem('corpOffers', JSON.stringify(offers));
  showNotification(`状態を「${offers[idx].status==='published'?'公開中':'アーカイブ'}」に更新しました。`,'info');
  renderOffers();
}
function editOffer(id) {
  const offers = JSON.parse(localStorage.getItem('corpOffers') || '[]');
  const offer = offers.find(o => o.id === id);
  if (!offer) return;
  document.getElementById('offerName').value = offer.name;
  document.getElementById('offerCategory').value = offer.category;
  document.getElementById('offerPrice').value = offer.price;
  document.getElementById('offerSuper').value = String(offer.isSuper);
  document.getElementById('offerStart').value = offer.startAt || '';
  document.getElementById('offerEnd').value = offer.endAt || '';
  document.getElementById('offerDescription').value = offer.description || '';
  if (offer.imageUrl) {
    document.getElementById('offerImagePreview').innerHTML = `<img src="${offer.imageUrl}" alt="プレビュー" style="max-width:220px; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,.08);" />`;
  }
  // Save overwrites existing offer
  const saveBtn = document.getElementById('saveOffer');
  const handler = (e) => {
    e.preventDefault();
    offer.name = document.getElementById('offerName').value.trim();
    offer.category = document.getElementById('offerCategory').value;
    offer.price = Number(document.getElementById('offerPrice').value || 0);
    offer.isSuper = document.getElementById('offerSuper').value === 'true';
    offer.startAt = document.getElementById('offerStart').value;
    offer.endAt = document.getElementById('offerEnd').value;
    offer.description = document.getElementById('offerDescription').value.trim();
    const previewImg = document.getElementById('offerImagePreview').querySelector('img');
    if (previewImg) offer.imageUrl = previewImg.src;
    offer.updatedAt = new Date().toISOString();
    const offers = JSON.parse(localStorage.getItem('corpOffers') || '[]');
    const idx = offers.findIndex(o => o.id === id);
    offers[idx] = offer;
    localStorage.setItem('corpOffers', JSON.stringify(offers));
    showNotification('オファーを更新しました。','success');
    document.getElementById('resetOffer').click();
    renderOffers();
    saveBtn.removeEventListener('click', handler);
  };
  saveBtn.addEventListener('click', handler);
}

// Winning Numbers
let currentNumbersOfferId = null;
function initNumbersModal() {
  const modal = document.getElementById('numbersModal');
  const closeX = document.getElementById('closeNumbersModal');
  const closeBtn = document.getElementById('closeNumbersBtn');
  const addBtn = document.getElementById('addNumberBtn');
  const newNumberInput = document.getElementById('newNumber');
  const genBtn = document.getElementById('generateNumbersBtn');
  const genCount = document.getElementById('genCount');
  const genLength = document.getElementById('genLength');
  const importFile = document.getElementById('importNumbersFile');
  const exportBtn = document.getElementById('exportNumbersBtn');

  function open() { modal?.classList.add('show'); modal?.setAttribute('aria-hidden','false'); }
  function close() { modal?.classList.remove('show'); modal?.setAttribute('aria-hidden','true'); }

  closeX?.addEventListener('click', close);
  closeBtn?.addEventListener('click', close);
  modal?.querySelector('.modal-backdrop')?.addEventListener('click', close);

  addBtn?.addEventListener('click', () => {
    const value = (newNumberInput.value || '').trim();
    if (!value) return;
    upsertNumber(currentNumbersOfferId, value, '未使用');
    newNumberInput.value='';
    renderNumbers();
  });

  genBtn?.addEventListener('click', () => {
    const count = Number(genCount.value || 0);
    const length = Number(genLength.value || 0);
    if (!count || !length) return;
    for (let i=0;i<count;i++) {
      const num = Array.from({length},()=>Math.floor(Math.random()*10)).join('');
      upsertNumber(currentNumbersOfferId, num, '未使用');
    }
    renderNumbers();
  });

  importFile?.addEventListener('change', async () => {
    const file = importFile.files?.[0];
    if (!file) return;
    const text = await file.text();
    const rows = text.split(/\r?\n/).map(r=>r.trim()).filter(Boolean);
    rows.forEach(r => upsertNumber(currentNumbersOfferId, r, '未使用'));
    showNotification('CSVを取り込みました。','success');
    renderNumbers();
  });

  exportBtn?.addEventListener('click', () => {
    const rows = [['番号','状態']];
    document.querySelectorAll('#numbersTable tbody tr').forEach(tr => {
      const cols = Array.from(tr.querySelectorAll('td')).slice(0,2).map(td => td.textContent.trim());
      rows.push(cols);
    });
    const csv = rows.map(r => r.join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = 'winning_numbers.csv'; a.click();
    URL.revokeObjectURL(url);
    showNotification('CSVを書き出しました。','success');
  });

  window.openNumbers = (offerId) => {
    currentNumbersOfferId = offerId;
    renderNumbers();
    open();
  };
}

function upsertNumber(offerId, value, status) {
  if (!offerId) return;
  const key = `corpNumbers_${offerId}`;
  const arr = JSON.parse(localStorage.getItem(key) || '[]');
  const idx = arr.findIndex(n => n.value === value);
  if (idx >= 0) arr[idx].status = status; else arr.push({ value, status });
  localStorage.setItem(key, JSON.stringify(arr));
}
function renderNumbers() {
  const tbody = document.querySelector('#numbersTable tbody');
  tbody.innerHTML = '';
  const key = `corpNumbers_${currentNumbersOfferId}`;
  const arr = JSON.parse(localStorage.getItem(key) || '[]');
  if (!arr.length) {
    tbody.innerHTML = '<tr><td colspan="3" style="color:#6b7280;">番号がありません</td></tr>';
    return;
  }
  arr.forEach((n, i) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${n.value}</td>
      <td>${n.status || '未使用'}</td>
      <td>
        <button class="btn-primary" data-action="toggle">切替</button>
        <button class="btn-primary" data-action="delete">削除</button>
      </td>`;
    tr.querySelector('[data-action="toggle"]').addEventListener('click', () => {
      const key = `corpNumbers_${currentNumbersOfferId}`;
      const arr = JSON.parse(localStorage.getItem(key) || '[]');
      arr[i].status = arr[i].status === '未使用' ? '使用済' : '未使用';
      localStorage.setItem(key, JSON.stringify(arr));
      renderNumbers();
    });
    tr.querySelector('[data-action="delete"]').addEventListener('click', () => {
      const key = `corpNumbers_${currentNumbersOfferId}`;
      const arr = JSON.parse(localStorage.getItem(key) || '[]');
      arr.splice(i,1);
      localStorage.setItem(key, JSON.stringify(arr));
      renderNumbers();
    });
    tbody.appendChild(tr);
  });
}

// Expose for row buttons
function openNumbers(offerId) { window.openNumbers?.(offerId); }

// Utils
function getCategoryText(category) {
  const categories = { food:'食品・スイーツ', fashion:'ファッション', beauty:'美容・コスメ', electronics:'家電・電子機器', books:'書籍・文具', hobby:'趣味・スポーツ', home:'生活用品', other:'その他' };
  return categories[category] || 'その他';
} 