// Send Gift functionality
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in
    const isLoggedIn = localStorage.getItem('isLoggedIn');
    if (isLoggedIn !== 'true') {
        window.location.href = 'auth.php';
        return;
    }

    // Show user menu and hide login button
    const userMenu = document.querySelector('.user-menu');
    const loginBtn = document.getElementById('login-btn');
    if (userMenu && userMenu.style.display === 'none') {
        userMenu.style.display = 'block';
    }
    if (loginBtn && loginBtn.style.display !== 'none') {
        loginBtn.style.display = 'none';
    }

    // Initialize new selection flow
    initOwnedGifts();
    initRecipientSelection();
    initSendAction();
});

function initOwnedGifts() {
    const listEl = document.getElementById('ownedGiftsList');
    const searchEl = document.getElementById('ownedGiftsSearch');
    const filtersEl = document.getElementById('ownedGiftsFilters');
    const sortEl = document.getElementById('ownedGiftsSort');
    if (!listEl) return;

    let allGifts = [];
    getOwnedGifts().then(list => { allGifts = list; applyFiltersAndSort(); }).catch(()=>applyFiltersAndSort());
    let filtered = [];

    const categories = [
        { key: 'all', label: 'すべて' },
        { key: 'food', label: '食品・スイーツ' },
        { key: 'fashion', label: 'ファッション' },
        { key: 'beauty', label: '美容・コスメ' },
        { key: 'electronics', label: '家電・電子機器' },
        { key: 'books', label: '書籍・文具' },
        { key: 'hobby', label: '趣味・スポーツ' },
        { key: 'home', label: '生活用品' },
        { key: 'other', label: 'その他' },
    ];

    let activeCategory = 'all';
    let activeSort = 'recommended';

    function renderFilters() {
        if (!filtersEl) return;
        filtersEl.innerHTML = '';
        categories.forEach(cat => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'chip' + (cat.key === activeCategory ? ' active' : '');
            chip.textContent = cat.label;
            chip.setAttribute('data-category', cat.key);
            chip.addEventListener('click', () => {
                activeCategory = cat.key;
                applyFiltersAndSort();
                renderFilters();
            });
            filtersEl.appendChild(chip);
        });
    }

    function applyFiltersAndSort() {
        const q = (searchEl?.value || '').trim().toLowerCase();
        filtered = allGifts.filter(g => {
            const matchQuery = !q || g.name.toLowerCase().includes(q) || getCategoryText(g.category).toLowerCase().includes(q);
            const matchCategory = activeCategory === 'all' || g.category === activeCategory;
            return matchQuery && matchCategory;
        });

        switch (activeSort) {
            case 'latest':
                filtered.sort((a, b) => new Date(b.createdAt || b.receivedAt || 0) - new Date(a.createdAt || a.receivedAt || 0));
                break;
            case 'price_asc':
                filtered.sort((a, b) => (a.price || 0) - (b.price || 0));
                break;
            case 'price_desc':
                filtered.sort((a, b) => (b.price || 0) - (a.price || 0));
                break;
            case 'name_asc':
                filtered.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
                break;
            case 'recommended':
            default:
                filtered.sort((a, b) => Number(Boolean(b.isSuper)) - Number(Boolean(a.isSuper)) || (b.price || 0) - (a.price || 0));
        }
        render(filtered);
    }

    function render(items) {
        listEl.innerHTML = '';
        if (items.length === 0) {
            listEl.innerHTML = `
                <div style="padding:1.25rem; background:#f9fafb; border:1px dashed #e5e7eb; border-radius:12px; text-align:center; color:#374151;">
                  <div style="font-weight:700; margin-bottom:.25rem;">所有している贈り物がありません</div>
                  <div style="color:#6b7280; margin-bottom:.75rem;">ショップで新しい贈り物を購入してみましょう。</div>
                  <a href="shop.php" class="btn-primary" style="text-decoration:none;">
                    ショップへ移動
                  </a>
                </div>`;
            disableSendButton();
            updatePreview(null);
            return;
        }
        items.forEach(gift => {
            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'owned-gift-row';
            row.style.cssText = 'display:flex; align-items:center; gap:.75rem; padding:.75rem 1rem; border:1px solid #e5e7eb; border-radius:10px; background:#fff; text-align:left; cursor:pointer;';
            row.innerHTML = `
                <div class="icon" style="width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:10px; background:#f3f4f6; color:${gift.isSuper ? '#ffd700' : '#e91e63'};">
                    <i class="fas ${gift.isSuper ? 'fa-crown' : 'fa-gift'}"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${gift.name}</div>
                    <div style="font-size:.9rem; color:#6b7280;">¥${(gift.price||0).toLocaleString()} ・ ${getCategoryText(gift.category)}</div>
                </div>
                <div style="display:flex; align-items:center; gap:.5rem; color:#111827;">
                    <span class="badge" style="display:${gift.isSuper ? 'inline-flex' : 'none'}; align-items:center; gap:.25rem; padding:.2rem .5rem; border-radius:9999px; background:#fff7cc; color:#7a5d00; border:1px solid #ffec87; font-size:.75rem;"> <i class="fas fa-star"></i> SUPER</span>
                    <div style="font-weight:700;">選択</div>
                </div>
            `;
            row.addEventListener('click', () => {
                setSelectedGift(gift);
                updatePreview(gift);
                enableSendButtonIfReady();
                highlightSelected(row);
            });
            listEl.appendChild(row);
        });
    }

    function highlightSelected(activeRow) {
        listEl.querySelectorAll('.owned-gift-row').forEach(el => {
            el.style.borderColor = '#e5e7eb';
        });
        if (activeRow) activeRow.style.borderColor = '#e91e63';
    }

    if (searchEl) {
        searchEl.addEventListener('input', applyFiltersAndSort);
    }
    if (sortEl) {
        sortEl.addEventListener('change', () => {
            activeSort = sortEl.value;
            applyFiltersAndSort();
        });
    }

    renderFilters();
    // applyFiltersAndSort will run after async load

}

async function getOwnedGifts() {
    // Owned gifts: gifts received (status accepted) plus user-created gifts not yet sent or available
    const user = JSON.parse(localStorage.getItem('userData') || '{}');
    // Fetch from backend and filter by ownership/receiver
    // Note: for demo we request all and filter client-side
    const res = await (window.giftApi?.gifts?.() || Promise.resolve({ items: [] }));
    const gifts = res.items || [];
    const received = gifts.filter(g => g.status === 'accepted' && Number(g.receiver_id || g.receiverId) === Number(user.id));
    const owned = gifts.filter(g => g.status === 'owned' && Number(g.owner_id || g.ownerId) === Number(user.id));
    const created = gifts.filter(g => g.status === 'created' && Number(g.owner_id || g.ownerId) === Number(user.id));
    const list = [...received, ...owned, ...created];
    const map = new Map();
    list.forEach(g => map.set(g.id, g));
    return Array.from(map.values());
}

function initRecipientSelection() {
    const nameEl = document.getElementById('recipientName');
    const emailEl = document.getElementById('recipientEmail');
    const summaryEl = document.getElementById('recipientSummary');
    const suggestionsEl = document.getElementById('contactSuggestions');

    const contacts = getRecentContacts();

    function updateSummary() {
        const { name, email } = getRecipient();
        if (summaryEl) {
            summaryEl.textContent = (name || email) ? `受取人: ${name ? name : ''}${name && email ? ' / ' : ''}${email ? email : ''}` : '';
        }
        if (suggestionsEl) {
            const q = (nameEl?.value || emailEl?.value || '').trim().toLowerCase();
            if (!q) {
                suggestionsEl.style.display = 'none';
                suggestionsEl.innerHTML = '';
            } else {
                const matches = contacts.filter(c => (c.name.toLowerCase().includes(q) || c.email.toLowerCase().includes(q))).slice(0, 5);
                if (matches.length) {
                    suggestionsEl.style.display = 'block';
                    suggestionsEl.innerHTML = `
                        <div style="display:flex; flex-direction:column; gap:.5rem; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:.5rem;">
                            ${matches.map(m => `
                                <button type="button" class="contact-suggestion" style="display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.6rem .75rem; border-radius:8px; border:1px solid transparent; background:#fff; cursor:pointer;">
                                    <span><strong>${m.name}</strong> <span style="color:#6b7280;">${m.email}</span></span>
                                    <i class="fas fa-plus" style="color:#e91e63;"></i>
                                </button>
                            `).join('')}
                        </div>
                    `;
                    Array.from(suggestionsEl.querySelectorAll('.contact-suggestion')).forEach((btn, idx) => {
                        btn.addEventListener('click', () => {
                            const selected = matches[idx];
                            if (nameEl) nameEl.value = selected.name;
                            if (emailEl) emailEl.value = selected.email;
                            updateSummary();
                            enableSendButtonIfReady();
                            suggestionsEl.style.display = 'none';
                        });
                    });
                } else {
                    suggestionsEl.style.display = 'none';
                    suggestionsEl.innerHTML = '';
                }
            }
        }
    }

    function onChange() {
        updateSummary();
        enableSendButtonIfReady();
    }

    if (nameEl) nameEl.addEventListener('input', onChange);
    if (emailEl) emailEl.addEventListener('input', onChange);

    updateSummary();
}

function getRecentContacts() {
    // Simple recent contacts sourced from previous sent gifts
    const gifts = JSON.parse(localStorage.getItem('gifts') || '[]');
    const contactsMap = new Map();
    for (const g of gifts) {
        if (g.recipientEmail || g.recipientName) {
            const key = (g.recipientEmail || g.recipientName).toLowerCase();
            if (!contactsMap.has(key)) {
                contactsMap.set(key, { name: g.recipientName || '名前なし', email: g.recipientEmail || '' });
            }
        }
    }
    return Array.from(contactsMap.values()).slice(0, 10);
}

function initSendAction() {
    const btn = document.getElementById('sendSelectedGiftBtn');
    const modal = document.getElementById('sendConfirmModal');
    const backdrop = modal?.querySelector('.modal-backdrop');
    const closeBtn = document.getElementById('closeModalBtn');
    const cancelBtn = document.getElementById('cancelModalBtn');
    const confirmBtn = document.getElementById('confirmSendBtn');
    const modalGiftName = document.getElementById('modalGiftName');
    const modalRecipient = document.getElementById('modalRecipient');

    if (!btn) return;

    function openModal() {
        const gift = getSelectedGift();
        const { name, email } = getRecipient();
        if (modalGiftName) modalGiftName.textContent = gift?.name || '-';
        if (modalRecipient) modalRecipient.textContent = (name || email) ? `${name ? name : ''}${name && email ? ' / ' : ''}${email ? email : ''}` : '-';
        modal?.classList.add('show');
        modal?.setAttribute('aria-hidden', 'false');
    }
    function closeModal() {
        modal?.classList.remove('show');
        modal?.setAttribute('aria-hidden', 'true');
    }

    btn.addEventListener('click', () => {
        const gift = getSelectedGift();
        const { name, email } = getRecipient();
        if (!gift) {
            showNotification('贈り物を選択してください。', 'error');
            return;
        }
        if (!name && !email) {
            showNotification('受取人の名前またはメールアドレスを入力してください。', 'error');
            return;
        }
        openModal();
    });

    backdrop?.addEventListener('click', closeModal);
    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);

    confirmBtn?.addEventListener('click', async () => {
        const gift = getSelectedGift();
        const { name, email } = getRecipient();
        closeModal();
        showNotification('贈り物を送信中...', 'info');
        try {
            await giftApi.sendGift({
                name: gift.name,
                description: gift.description || '',
                price: gift.price || 0,
                category: gift.category || 'other',
                isSuper: !!gift.isSuper,
                recipientName: name || '',
                recipientEmail: email || ''
            });
            showNotification('贈り物を送信しました！', 'success');
            setTimeout(() => { window.location.href = 'river.php'; }, 1000);
        } catch (err) {
            showNotification(err.message || '送信に失敗しました。', 'error');
        }
    });
}

function enableSendButtonIfReady() {
    const btn = document.getElementById('sendSelectedGiftBtn');
    if (!btn) return;
    const gift = getSelectedGift();
    const { name, email } = getRecipient();
    btn.disabled = !(gift && (name || email));
}
function disableSendButton() {
    const btn = document.getElementById('sendSelectedGiftBtn');
    if (btn) btn.disabled = true;
}

function getRecipient() {
    const nameEl = document.getElementById('recipientName');
    const emailEl = document.getElementById('recipientEmail');
    return { name: (nameEl?.value || '').trim(), email: (emailEl?.value || '').trim() };
}

function setSelectedGift(gift) {
    sessionStorage.setItem('selectedGiftToSend', JSON.stringify(gift));
}
function getSelectedGift() {
    const raw = sessionStorage.getItem('selectedGiftToSend');
    return raw ? JSON.parse(raw) : null;
}

function updatePreview(gift) {
    const previewName = document.getElementById('previewName');
    const previewPrice = document.getElementById('previewPrice');
    const previewCategory = document.getElementById('previewCategory');
    const previewIcon = document.getElementById('previewIcon');
    if (!previewName || !previewPrice || !previewCategory || !previewIcon) return;
    if (!gift) {
        previewName.textContent = '贈り物が未選択です';
        previewPrice.textContent = '0';
        previewCategory.textContent = 'カテゴリー';
        previewIcon.className = 'fas fa-gift';
        previewIcon.style.color = '#e91e63';
        return;
    }
    previewName.textContent = gift.name;
    previewPrice.textContent = gift.price?.toLocaleString?.() || String(gift.price || '0');
    previewCategory.textContent = getCategoryText(gift.category);
    previewIcon.className = `fas ${gift.isSuper ? 'fa-crown' : 'fa-gift'}`;
    previewIcon.style.color = gift.isSuper ? '#ffd700' : '#e91e63';
}

function markGiftAsSent(gift, recipient) {
    const gifts = JSON.parse(localStorage.getItem('gifts') || '[]');
    const user = JSON.parse(localStorage.getItem('userData') || '{}');
    const idx = gifts.findIndex(g => g.id === gift.id);
    const updated = { ...gift, status: 'sent', senderId: user.id, senderName: user.firstName || 'ユーザー', sentAt: new Date().toISOString(), recipientName: recipient.name || null, recipientEmail: recipient.email || null };
    if (idx >= 0) gifts[idx] = updated; else gifts.push(updated);
    localStorage.setItem('gifts', JSON.stringify(gifts));
    // update user stats
    const u = { ...user, giftsSent: (user.giftsSent || 0) + 1 };
    localStorage.setItem('userData', JSON.stringify(u));
}

// Helpers
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

// Notification helper
function showNotification(message, type = 'info') {
    if (typeof window.showNotification === 'function') {
        window.showNotification(message, type);
    } else if (window.app && typeof window.app.showNotification === 'function') {
        window.app.showNotification(message, type);
    }
} 