// My Page functionality
document.addEventListener('DOMContentLoaded', async function() {
    // Verify session with backend first (supports OAuth redirects)
    try {
        const me = await (window.giftApi?.me?.());
        if (me && me.user) {
            localStorage.setItem('userData', JSON.stringify(me.user));
            localStorage.setItem('isLoggedIn', 'true');
        } else {
            window.location.href = 'auth.php';
            return;
        }
    } catch (_) {
        // Fallback to localStorage if backend is unreachable
        const isLoggedIn = localStorage.getItem('isLoggedIn');
        if (isLoggedIn !== 'true') {
            window.location.href = 'auth.php';
            return;
        }
    }

    // Show user menu and hide login button (only if not already done by App.js)
    const userMenu = document.querySelector('.user-menu');
    const loginBtn = document.getElementById('login-btn');
    if (userMenu && userMenu.style.display === 'none') {
        userMenu.style.display = 'block';
    }
    if (loginBtn && loginBtn.style.display !== 'none') {
        loginBtn.style.display = 'none';
    }

    // Load user data
    try {
        const me = await (window.giftApi?.me?.() || Promise.resolve({ user: JSON.parse(localStorage.getItem('userData')||'{}') }));
        if (me && me.user) {
            localStorage.setItem('userData', JSON.stringify(me.user));
        }
    } catch(_) {}
    loadUserData();
    
    // Initialize tabs
    initializeTabs();
    
    // Initialize user menu (only if not already done by App.js)
    initializeUserMenu();
    
    // Load gift history
    loadGiftHistory();
    
    // Initialize forms
    initializeForms();
    
    // Initialize logout (ensure it works even if App.js already set it up)
    initializeLogout();
});

// Load and display user data
function loadUserData() {
    const userData = JSON.parse(localStorage.getItem('userData') || '{}');
    
    // Update profile information
    const profileName = document.querySelector('.profile-name');
    const profileEmail = document.querySelector('.profile-email');
    const userName = document.querySelector('.user-name');
    
    if (profileName) {
        profileName.textContent = `${userData.firstName || 'ユーザー'} ${userData.lastName || '名'}`;
    }
    
    if (profileEmail) {
        profileEmail.textContent = userData.email || 'user@example.com';
    }
    
    if (userName) {
        userName.textContent = userData.firstName || 'ユーザー';
    }
    
    // Update statistics
    const pointsElement = document.getElementById('points');
    const giftsReceivedElement = document.getElementById('gifts-received');
    const giftsSentElement = document.getElementById('gifts-sent');
    const corpGiftsReceivedElement = document.getElementById('corp-gifts-received');

    // Determine points by authority (membership)
    const level = (userData.membershipLevel || 'basic').toLowerCase();
    const levelPoints = level === 'business' ? 1500 : level === 'premium' ? 1000 : 500;
    userData.points = levelPoints;
    localStorage.setItem('userData', JSON.stringify(userData));
    
    if (pointsElement) {
        pointsElement.textContent = levelPoints.toLocaleString();
    }
    
    if (giftsReceivedElement) {
        giftsReceivedElement.textContent = userData.giftsReceived || 0;
    }
    
    if (giftsSentElement) {
        giftsSentElement.textContent = userData.giftsSent || 0;
    }

    if (corpGiftsReceivedElement) {
        corpGiftsReceivedElement.textContent = userData.corporateGiftsReceived || 0;
    }
    
    // Update membership level badge
    const membershipBadge = document.querySelector('.membership-badge span');
    const levelText = {
        'basic': 'ベーシック会員',
        'premium': 'プレミアム会員',
        'business': 'ビジネス会員'
    };
    if (membershipBadge) {
        membershipBadge.textContent = levelText[level] || 'ベーシック会員';
    }

    // Update current plan card in membership tab
    const currentPlanNameEl = document.querySelector('.plan-card.current .plan-header h3');
    const currentPlanPriceEl = document.querySelector('.plan-card.current .plan-header .plan-price');
    if (currentPlanNameEl) currentPlanNameEl.textContent = (level === 'premium' ? 'プレミアム' : level === 'business' ? 'ビジネス' : 'ベーシック');
    if (currentPlanPriceEl) {
        const priceDisplay = (level === 'premium' || level === 'business') ? `¥<span>/月</span>` : '¥0<span>/月</span>';
        currentPlanPriceEl.innerHTML = priceDisplay;
    }
    // Update current plan features list
    const currentPlanFeaturesEl = document.querySelector('.plan-card.current .plan-features');
    if (currentPlanFeaturesEl) {
        const featuresByLevel = {
            basic: [
                { text: '基本的な贈り物機能', ok: true },
                { text: '月5回の受け取り権利', ok: true },
                { text: '標準的な商品選択', ok: true },
                { text: 'スーパープレゼント対象外', ok: false }
            ],
            premium: [
                { text: '全ての贈り物機能', ok: true },
                { text: '無制限の受け取り権利', ok: true },
                { text: '高級商品選択', ok: true },
                { text: 'スーパープレゼント対象', ok: true }
            ],
            business: [
                { text: '全ての機能', ok: true },
                { text: '優先サポート', ok: true },
                { text: 'カスタム贈り物', ok: true },
                { text: '分析レポート', ok: true }
            ]
        };
        const features = featuresByLevel[level] || featuresByLevel.basic;
        currentPlanFeaturesEl.innerHTML = features.map(f => `
            <li><i class="fas ${f.ok ? 'fa-check' : 'fa-times'}"></i> ${f.text}</li>
        `).join('');
    }
    
    // Populate profile form
    populateProfileForm(userData);
}

// Initialize tab switching
function initializeTabs() {
    const tabs = document.querySelectorAll('.mypage-tab');
    const tabContents = document.querySelectorAll('.mypage-tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and contents
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(`${targetTab}-content`).classList.add('active');
        });
    });
}

// Initialize user menu dropdown
function initializeUserMenu() {
    const userMenu = document.querySelector('.user-menu');
    const userDropdown = document.querySelector('.user-dropdown');
    const userProfile = document.querySelector('.user-profile');
    
    if (userMenu && userDropdown) {
        // Only prevent default behavior when clicking on the user profile button
        if (userProfile) {
            // Remove any existing event listeners to avoid conflicts
            const newUserProfile = userProfile.cloneNode(true);
            userProfile.parentNode.replaceChild(newUserProfile, userProfile);
            
            newUserProfile.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });
    }
}

// Load gift history
function loadGiftHistory() {
    const giftsGrid = document.getElementById('gifts-grid');
    if (!giftsGrid) return;
    
    // Sample gift data (in a real app, this would come from an API)
    const gifts = [
        {
            id: 1,
            type: 'received',
            name: '高級チョコレートセット',
            price: 3000,
            date: '2024-01-15',
            status: 'delivered'
        },
        {
            id: 2,
            type: 'sent',
            name: 'アロマキャンドル',
            price: 1500,
            date: '2024-01-10',
            status: 'delivered'
        },
        {
            id: 3,
            type: 'received',
            name: 'スーパープレゼント - 高級腕時計',
            price: 50000,
            date: '2024-01-05',
            status: 'delivered',
            isSuper: true
        }
    ];
    
    displayGifts(gifts);
    
    // Handle gift filter
    const giftFilter = document.getElementById('gift-filter');
    if (giftFilter) {
        giftFilter.addEventListener('change', function() {
            const filterValue = this.value;
            const filteredGifts = filterValue === 'all' ? 
                gifts : 
                gifts.filter(gift => gift.type === filterValue);
            displayGifts(filteredGifts);
        });
    }
}

// Display gifts in the grid
function displayGifts(gifts) {
    const giftsGrid = document.getElementById('gifts-grid');
    if (!giftsGrid) return;
    
    giftsGrid.innerHTML = '';
    
    if (gifts.length === 0) {
        giftsGrid.innerHTML = `
            <div class="no-gifts">
                <i class="fas fa-gift"></i>
                <p>まだ贈り物の履歴がありません</p>
                <a href="river.php" class="btn-primary">贈り物の川に行く</a>
            </div>
        `;
        return;
    }
    
    gifts.forEach(gift => {
        const giftCard = document.createElement('div');
        giftCard.className = 'gift-card';
        giftCard.innerHTML = `
            <div class="gift-header ${gift.isSuper ? 'super' : ''}">
                <div class="gift-icon">
                    <i class="fas ${gift.isSuper ? 'fa-crown' : 'fa-gift'}"></i>
                </div>
                <div class="gift-type">
                    <span class="type-badge ${gift.type}">
                        ${gift.type === 'received' ? '受け取り' : '送付'}
                    </span>
                </div>
            </div>
            <div class="gift-content">
                <h4>${gift.name}</h4>
                <p class="gift-price">¥${gift.price.toLocaleString()}</p>
                <p class="gift-date">${formatDate(gift.date)}</p>
                <div class="gift-status">
                    <span class="status-badge ${gift.status}">
                        ${getStatusText(gift.status)}
                    </span>
                </div>
            </div>
        `;
        giftsGrid.appendChild(giftCard);
    });
}

// Format date for display
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Get status text
function getStatusText(status) {
    const statusTexts = {
        'pending': '処理中',
        'shipped': '発送済み',
        'delivered': '配達済み',
        'cancelled': 'キャンセル'
    };
    return statusTexts[status] || status;
}

// Populate profile form with user data
function populateProfileForm(userData) {
    const firstNameInput = document.getElementById('profile-firstname');
    const lastNameInput = document.getElementById('profile-lastname');
    const emailInput = document.getElementById('profile-email');
    const birthDateInput = document.getElementById('profile-birthdate');
    
    if (firstNameInput) firstNameInput.value = userData.firstName || '';
    if (lastNameInput) lastNameInput.value = userData.lastName || '';
    if (emailInput) emailInput.value = userData.email || '';
    if (birthDateInput) birthDateInput.value = userData.birthDate || '';
}

// Initialize forms
function initializeForms() {
    // Profile form
    const profileForm = document.querySelector('.profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const userData = JSON.parse(localStorage.getItem('userData') || '{}');
            
            // Update user data
            userData.firstName = formData.get('firstname');
            userData.lastName = formData.get('lastname');
            userData.email = formData.get('email');
            userData.birthDate = formData.get('birthdate');
            
            // Save to localStorage
            localStorage.setItem('userData', JSON.stringify(userData));
            
            // Update display
            loadUserData();
            
            showNotification('プロフィールが更新されました。', 'success');
        });
    }
    
    // Password form
    const passwordForm = document.querySelector('.password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = this.querySelector('#current-password').value;
            const newPassword = this.querySelector('#new-password').value;
            const confirmPassword = this.querySelector('#confirm-new-password').value;
            
            if (!currentPassword || !newPassword || !confirmPassword) {
                showNotification('すべての項目を入力してください。', 'error');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showNotification('新しいパスワードが一致しません。', 'error');
                return;
            }
            
            if (newPassword.length < 8) {
                showNotification('パスワードは8文字以上で入力してください。', 'error');
                return;
            }
            
            showNotification('パスワードが変更されました。', 'success');
            this.reset();
        });
    }
    
    // Notification settings
    const notificationSettings = document.querySelector('.notification-settings');
    if (notificationSettings) {
        const saveButton = notificationSettings.nextElementSibling;
        if (saveButton) {
            saveButton.addEventListener('click', function() {
                showNotification('通知設定が保存されました。', 'success');
            });
        }
    }
}

// Initialize logout functionality
function initializeLogout() {
    // Function to initialize logout
    function setupLogout() {
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            // Add event listener (it's safe to add multiple, they won't conflict)
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Clear user data
                localStorage.removeItem('userData');
                localStorage.removeItem('isLoggedIn');
                localStorage.removeItem('rememberMe');
                
                // Show notification if function exists
                if (typeof showNotification === 'function') {
                    showNotification('ログアウトしました。', 'info');
                }
                
                // Redirect to home page
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1500);
            });
        } else {
            // Retry after a short delay
            setTimeout(setupLogout, 100);
        }
    }
    
    // Start the setup process
    setupLogout();
}

// Upgrade plan functionality
document.addEventListener('DOMContentLoaded', async function() {
    const upgradeButtons = document.querySelectorAll('.btn-upgrade');
    upgradeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const planName = this.closest('.plan-card').querySelector('h3').textContent;
            showNotification(`${planName}へのアップグレード機能は現在開発中です。`, 'info');
        });
    });
}); 