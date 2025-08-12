// Products are now loaded from backend
let products = [];

// Cart state
let cart = [];

// DOM elements
const productsGrid = document.getElementById('productsGrid');
const cartSidebar = document.getElementById('cartSidebar');
const cartItems = document.getElementById('cartItems');
const cartTotal = document.getElementById('cartTotal');
const cartCount = document.getElementById('cartCount');
const cartToggle = document.getElementById('cartToggle');
const closeCart = document.getElementById('closeCart');
const checkoutBtn = document.getElementById('checkoutBtn');

// Filter elements
const priceFilter = document.getElementById('priceFilter');
const categoryFilter = document.getElementById('categoryFilter');
const sortFilter = document.getElementById('sortFilter');

// Initialize page
document.addEventListener('DOMContentLoaded', async function() {
    try {
        const res = await giftApi.products();
        products = res.items || [];
    } catch (e) {
        products = [];
        if (typeof showNotification === 'function') {
            showNotification('商品一覧の読み込みに失敗しました。', 'error');
        }
    }
    displayProducts(products);
    setupEventListeners();
    updateCartDisplay();
});

// Setup event listeners
function setupEventListeners() {
    // Cart toggle
    cartToggle.addEventListener('click', toggleCart);
    closeCart.addEventListener('click', toggleCart);
    
    // Checkout
    checkoutBtn.addEventListener('click', checkout);
    
    // Filters
    priceFilter.addEventListener('change', filterProducts);
    categoryFilter.addEventListener('change', filterProducts);
    sortFilter.addEventListener('change', filterProducts);
    
    // Close cart when clicking outside
    document.addEventListener('click', function(e) {
        if (!cartSidebar.contains(e.target) && !cartToggle.contains(e.target)) {
            cartSidebar.classList.remove('open');
        }
    });
}

// Display products
function displayProducts(productsToShow) {
    productsGrid.innerHTML = '';
    
    productsToShow.forEach(product => {
        const productCard = createProductCard(product);
        productsGrid.appendChild(productCard);
    });
}

// Create product card
function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'product-card';
    
    card.innerHTML = `
        <div class="product-image">
            <i class="${product.icon}"></i>
        </div>
        <div class="product-info">
            <h3 class="product-name">${product.name}</h3>
            <p class="product-description">${product.description}</p>
            <div class="product-price">¥${product.price.toLocaleString()}</div>
            <button class="add-to-cart" data-product-id="${product.id}">
                カートに追加
            </button>
        </div>
    `;
    
    // Add to cart event
    const addButton = card.querySelector('.add-to-cart');
    addButton.addEventListener('click', () => addToCart(product));
    
    return card;
}

// Add to cart
function addToCart(product) {
    const existingItem = cart.find(item => item.id === product.id);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            ...product,
            quantity: 1
        });
    }
    
    updateCartDisplay();
    showNotification(`${product.name}をカートに追加しました！`, 'success');
}

// Update cart display
function updateCartDisplay() {
    // Update cart items
    cartItems.innerHTML = '';
    
    cart.forEach(item => {
        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        
        cartItem.innerHTML = `
            <div class="cart-item-image">
                <i class="${item.icon}"></i>
            </div>
            <div class="cart-item-info">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-price">¥${item.price.toLocaleString()} × ${item.quantity}</div>
            </div>
            <button class="remove-item" data-product-id="${item.id}" aria-label="${item.name}をカートから削除" title="削除">
                <i class="fas fa-times" aria-hidden="true"></i>
                <span class="sr-only">${item.name}を削除</span>
            </button>
        `;
        
        // Remove item event
        const removeButton = cartItem.querySelector('.remove-item');
        removeButton.addEventListener('click', () => removeFromCart(item.id));
        
        cartItems.appendChild(cartItem);
    });
    
    // Update cart total
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    cartTotal.textContent = `¥${total.toLocaleString()}`;
    
    // Update cart count
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = count;
}

// Remove from cart
function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCartDisplay();
    showNotification('商品をカートから削除しました', 'info');
}

// Toggle cart
function toggleCart() {
    cartSidebar.classList.toggle('open');
}

// Filter products
function filterProducts() {
    let filteredProducts = [...products];
    
    // Price filter
    const priceValue = priceFilter.value;
    if (priceValue) {
        const maxPrice = parseInt(priceValue);
        if (maxPrice === 1000) {
            filteredProducts = filteredProducts.filter(p => p.price <= 1000);
        } else if (maxPrice === 3000) {
            filteredProducts = filteredProducts.filter(p => p.price > 1000 && p.price <= 3000);
        } else if (maxPrice === 5000) {
            filteredProducts = filteredProducts.filter(p => p.price > 3000 && p.price <= 5000);
        } else if (maxPrice === 10000) {
            filteredProducts = filteredProducts.filter(p => p.price > 5000 && p.price <= 10000);
        } else if (maxPrice === 10001) {
            filteredProducts = filteredProducts.filter(p => p.price > 10000);
        }
    }
    
    // Category filter
    const categoryValue = categoryFilter.value;
    if (categoryValue) {
        filteredProducts = filteredProducts.filter(p => p.category === categoryValue);
    }
    
    // Sort
    const sortValue = sortFilter.value;
    if (sortValue === 'price-low') {
        filteredProducts.sort((a, b) => a.price - b.price);
    } else if (sortValue === 'price-high') {
        filteredProducts.sort((a, b) => b.price - a.price);
    } else if (sortValue === 'new') {
        filteredProducts.sort((a, b) => b.id - a.id);
    }
    
    displayProducts(filteredProducts);
}

// Checkout
async function checkout() {
    if (cart.length === 0) {
        showNotification('カートが空です', 'error');
        return;
    }
    try {
        showNotification('購入処理中...', 'info');
        const res = await giftApi.checkout(cart.map(({ id, price, quantity }) => ({ id, price, quantity })));
        showNotification('購入が完了しました！贈り物が川に流されます。', 'success');
        cart = [];
        updateCartDisplay();
        toggleCart();
    } catch (e) {
        showNotification(e.message || '購入に失敗しました。', 'error');
    }
}

// uses global showNotification from js/notify.js 