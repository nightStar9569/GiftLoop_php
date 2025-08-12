// River functionality
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in
    const isLoggedIn = localStorage.getItem('isLoggedIn');
    if (isLoggedIn !== 'true') {
        // Redirect to login if not authenticated
        window.location.href = 'auth.php';
        return;
    }

    // Initialize river
    initializeRiver();
    
    // Load user data
    loadUserData();
    
    // Initialize controls
    initializeControls();
    
    // Start gift generation
    startGiftGeneration();
    
    // Initialize modal
    initializeModal();
    
    // Load recent gifts
    loadRecentGifts();
});

// Sample gift data
const giftData = [
    {
        id: 1,
        name: "高級チョコレートセット",
        description: "ベルギー産の高級チョコレート。贅沢な味わいをお楽しみください。",
        price: 3000,
        category: "食品",
        icon: "fas fa-cookie-bite",
        rarity: "common"
    },
    {
        id: 2,
        name: "アロマキャンドル",
        description: "リラックス効果のあるラベンダーの香り。癒しの時間をお届けします。",
        price: 1500,
        category: "生活用品",
        icon: "fas fa-fire",
        rarity: "common"
    },
    {
        id: 3,
        name: "スーパープレゼント - 高級腕時計",
        description: "特別な贈り物！高級ブランドの腕時計です。",
        price: 50000,
        category: "アクセサリー",
        icon: "fas fa-crown",
        rarity: "super"
    },
    {
        id: 4,
        name: "ハンドクリーム",
        description: "保湿効果抜群のハンドクリーム。乾燥からお肌を守ります。",
        price: 800,
        category: "美容",
        icon: "fas fa-spa",
        rarity: "common"
    },
    {
        id: 5,
        name: "本 - ベストセラー小説",
        description: "話題のベストセラー小説。読書の時間をお楽しみください。",
        price: 1200,
        category: "書籍",
        icon: "fas fa-book",
        rarity: "common"
    },
    {
        id: 6,
        name: "スーパープレゼント - 高級ワイン",
        description: "特別な贈り物！フランス産の高級ワインです。",
        price: 15000,
        category: "飲料",
        icon: "fas fa-crown",
        rarity: "super"
    },
    {
        id: 7,
        name: "ポーチ",
        description: "実用的で可愛いポーチ。小物整理に最適です。",
        price: 2000,
        category: "ファッション",
        icon: "fas fa-shopping-bag",
        rarity: "common"
    },
    {
        id: 8,
        name: "スーパープレゼント - 高級香水",
        description: "特別な贈り物！フランス産の高級香水です。",
        price: 25000,
        category: "美容",
        icon: "fas fa-crown",
        rarity: "super"
    }
];

let currentGifts = [];
let userStats = {
    receiveRights: 5,
    receivedGifts: 0,
    totalValue: 0
};

// Initialize river
function initializeRiver() {
    const container = document.getElementById('giftFlowContainer');
    if (!container) return;
    
    // Create river background effect
    createRiverEffect();
    
    // Generate initial gifts
    generateGifts();
}

// Create river background effect
function createRiverEffect() {
    const riverBackground = document.querySelector('.river-background-animated');
    if (riverBackground) {
        // Add wave animation
        riverBackground.style.background = `
            linear-gradient(45deg, 
                rgba(64, 156, 255, 0.1) 25%, 
                transparent 25%, 
                transparent 50%, 
                rgba(64, 156, 255, 0.1) 50%, 
                rgba(64, 156, 255, 0.1) 75%, 
                transparent 75%, 
                transparent
            )
        `;
        riverBackground.style.backgroundSize = '60px 60px';
        riverBackground.style.animation = 'wave 3s linear infinite';
    }
}

// Generate gifts in the river
function generateGifts() {
    const container = document.getElementById('giftFlowContainer');
    if (!container) return;
    
    // Clear existing gifts
    container.innerHTML = '';
    currentGifts = [];
    
    // Generate 5-8 gifts
    const giftCount = Math.floor(Math.random() * 4) + 5;
    
    for (let i = 0; i < giftCount; i++) {
        const gift = createGiftElement();
        container.appendChild(gift);
        currentGifts.push(gift);
    }
}

// Create individual gift element
function createGiftElement() {
    const gift = giftData[Math.floor(Math.random() * giftData.length)];
    const giftElement = document.createElement('div');
    
    giftElement.className = `floating-gift ${gift.rarity}`;
    giftElement.style.cssText = `
        position: absolute;
        left: ${Math.random() * 80 + 10}%;
        animation-delay: ${Math.random() * 10}s;
        animation-duration: ${15 + Math.random() * 10}s;
    `;
    
    giftElement.innerHTML = `
        <div class="gift-content">
            <i class="${gift.icon}"></i>
            <span class="gift-price">¥${gift.price.toLocaleString()}</span>
        </div>
    `;
    
    // Add click event
    giftElement.addEventListener('click', function() {
        showGiftModal(gift);
    });
    
    // Add touch event for mobile
    giftElement.addEventListener('touchstart', function(e) {
        e.preventDefault();
        showGiftModal(gift);
    });
    
    return giftElement;
}

// Show gift modal
function showGiftModal(gift) {
    const modal = document.getElementById('giftModal');
    const modalGiftIcon = document.getElementById('modalGiftIcon');
    const modalGiftName = document.getElementById('modalGiftName');
    const modalGiftDescription = document.getElementById('modalGiftDescription');
    const modalGiftPrice = document.getElementById('modalGiftPrice');
    const modalGiftCategory = document.getElementById('modalGiftCategory');
    
    if (modal && modalGiftIcon && modalGiftName && modalGiftDescription && modalGiftPrice && modalGiftCategory) {
        modalGiftIcon.className = gift.icon;
        modalGiftName.textContent = gift.name;
        modalGiftDescription.textContent = gift.description;
        modalGiftPrice.textContent = gift.price.toLocaleString();
        modalGiftCategory.textContent = gift.category;
        
        // Add super gift styling
        if (gift.rarity === 'super') {
            modalGiftIcon.style.color = '#ffd700';
            modalGiftName.style.color = '#ffd700';
        } else {
            modalGiftIcon.style.color = '#e91e63';
            modalGiftName.style.color = '#333';
        }
        
        modal.classList.add('show');
    }
}

// Initialize modal functionality
function initializeModal() {
    const modal = document.getElementById('giftModal');
    const closeModal = document.getElementById('closeModal');
    const acceptGift = document.getElementById('acceptGift');
    const rejectGift = document.getElementById('rejectGift');
    
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            modal.classList.remove('show');
        });
    }
    
    if (acceptGift) {
        acceptGift.addEventListener('click', function() {
            acceptGiftAction();
        });
    }
    
    if (rejectGift) {
        rejectGift.addEventListener('click', function() {
            rejectGiftAction();
        });
    }
    
    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    }
}

// Accept gift action
function acceptGiftAction() {
    const modalGiftName = document.getElementById('modalGiftName');
    const modalGiftPrice = document.getElementById('modalGiftPrice');
    
    if (userStats.receiveRights > 0) {
        const giftName = modalGiftName.textContent;
        const giftPrice = parseInt(modalGiftPrice.textContent.replace(/,/g, ''));
        
        // Update user stats
        userStats.receiveRights--;
        userStats.receivedGifts++;
        userStats.totalValue += giftPrice;
        
        // Update display
        updateUserStats();
        
        // Add to recent gifts
        addToRecentGifts(giftName, giftPrice);
        
        // Show success notification
        showNotification(`${giftName}を受け取りました！`, 'success');
        
        // Close modal
        document.getElementById('giftModal').classList.remove('show');
        
        // Remove gift from river
        removeGiftFromRiver();
        
        // Check if rights are depleted
        if (userStats.receiveRights <= 0) {
            showNotification('受け取り権利がなくなりました。新しい贈り物を流すと権利が復活します。', 'info');
        }
    } else {
        showNotification('受け取り権利がありません。', 'error');
    }
}

// Reject gift action
function rejectGiftAction() {
    const modalGiftName = document.getElementById('modalGiftName');
    const giftName = modalGiftName.textContent;
    
    // Lose one receive right
    userStats.receiveRights--;
    updateUserStats();
    
    // Show notification
    showNotification(`${giftName}を拒否しました。受け取り権利が1つ減りました。`, 'info');
    
    // Close modal
    document.getElementById('giftModal').classList.remove('show');
    
    // Remove gift from river
    removeGiftFromRiver();
    
    // Check if rights are depleted
    if (userStats.receiveRights <= 0) {
        showNotification('受け取り権利がなくなりました。新しい贈り物を流すと権利が復活します。', 'info');
    }
}

// Remove gift from river
function removeGiftFromRiver() {
    const container = document.getElementById('giftFlowContainer');
    if (container && container.children.length > 0) {
        const randomIndex = Math.floor(Math.random() * container.children.length);
        const giftToRemove = container.children[randomIndex];
        
        // Add fade out animation
        giftToRemove.style.transition = 'opacity 0.5s ease';
        giftToRemove.style.opacity = '0';
        
        setTimeout(() => {
            if (giftToRemove.parentNode) {
                giftToRemove.parentNode.removeChild(giftToRemove);
            }
        }, 500);
    }
}

// Load user data
function loadUserData() {
    const userData = JSON.parse(localStorage.getItem('userData') || '{}');
    
    // Load user stats from localStorage or use defaults
    const savedStats = localStorage.getItem('riverStats');
    if (savedStats) {
        userStats = JSON.parse(savedStats);
    } else {
        // Initialize with user data if available
        userStats = {
            receiveRights: userData.receiveRights || 5,
            receivedGifts: userData.giftsReceived || 0,
            totalValue: userData.totalValue || 0
        };
    }
    
    updateUserStats();
}

// Update user stats display
function updateUserStats() {
    const receiveRightsElement = document.getElementById('receiveRights');
    const receivedGiftsElement = document.getElementById('receivedGifts');
    const totalValueElement = document.getElementById('totalValue');
    
    if (receiveRightsElement) {
        receiveRightsElement.textContent = userStats.receiveRights;
    }
    
    if (receivedGiftsElement) {
        receivedGiftsElement.textContent = userStats.receivedGifts;
    }
    
    if (totalValueElement) {
        totalValueElement.textContent = userStats.totalValue.toLocaleString();
    }
    
    // Save to localStorage
    localStorage.setItem('riverStats', JSON.stringify(userStats));
    
    // Update user data
    const userData = JSON.parse(localStorage.getItem('userData') || '{}');
    userData.receiveRights = userStats.receiveRights;
    userData.giftsReceived = userStats.receivedGifts;
    userData.totalValue = userStats.totalValue;
    localStorage.setItem('userData', JSON.stringify(userData));
}

// Initialize controls
function initializeControls() {
    const refreshButton = document.getElementById('refreshRiver');
    const shopButton = document.getElementById('shopButton');
    
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            generateGifts();
            showNotification('川を更新しました！', 'info');
        });
    }
    
    if (shopButton) {
        shopButton.addEventListener('click', function() {
            window.location.href = 'shop.php';
        });
    }
}

// Start gift generation
function startGiftGeneration() {
    // Generate new gifts every 30 seconds
    setInterval(() => {
        if (currentGifts.length < 3) {
            const container = document.getElementById('giftFlowContainer');
            if (container) {
                const gift = createGiftElement();
                container.appendChild(gift);
                currentGifts.push(gift);
            }
        }
    }, 30000);
}

// Load recent gifts
function loadRecentGifts() {
    const recentGiftsGrid = document.getElementById('recentGiftsGrid');
    if (!recentGiftsGrid) return;
    
    const recentGifts = JSON.parse(localStorage.getItem('recentGifts') || '[]');
    
    if (recentGifts.length === 0) {
        recentGiftsGrid.innerHTML = `
            <div class="no-recent-gifts">
                <i class="fas fa-gift"></i>
                <p>まだ受け取った贈り物がありません</p>
                <p>川を流れる贈り物をクリックして受け取ってみましょう！</p>
            </div>
        `;
        return;
    }
    
    recentGiftsGrid.innerHTML = '';
    
    recentGifts.slice(0, 6).forEach(gift => {
        const giftCard = document.createElement('div');
        giftCard.className = 'recent-gift-card';
        giftCard.innerHTML = `
            <div class="gift-icon">
                <i class="fas fa-gift"></i>
            </div>
            <div class="gift-info">
                <h4>${gift.name}</h4>
                <p class="gift-price">¥${gift.price.toLocaleString()}</p>
                <p class="gift-date">${gift.date}</p>
            </div>
        `;
        recentGiftsGrid.appendChild(giftCard);
    });
}

// Add gift to recent gifts
function addToRecentGifts(name, price) {
    const recentGifts = JSON.parse(localStorage.getItem('recentGifts') || '[]');
    
    const newGift = {
        name: name,
        price: price,
        date: new Date().toLocaleDateString('ja-JP')
    };
    
    recentGifts.unshift(newGift);
    
    // Keep only last 10 gifts
    if (recentGifts.length > 10) {
        recentGifts.splice(10);
    }
    
    localStorage.setItem('recentGifts', JSON.stringify(recentGifts));
    
    // Reload recent gifts display
    loadRecentGifts();
}

// uses global showNotification from js/notify.js 