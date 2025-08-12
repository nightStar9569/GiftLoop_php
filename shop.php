<?php $pageTitle = 'Gift Shop - ギフトループ'; $pageDescription = '贈り物の川に流す商品を購入。様々な価格帯の商品から選んで、ランダムに流れる贈り物を楽しもう。'; include __DIR__.'/includes/header.php'; ?>

<section class="shop-hero">
  <div class="container">
    <h1>贈り物ショップ</h1>
    <p>川に流す贈り物を選んで、誰かの笑顔を作りましょう</p>
  </div>
</section>

<section class="filter-section">
  <div class="container">
    <div class="filter-controls">
      <div class="filter-group"><label>価格帯:</label><select id="priceFilter"><option value="">すべて</option><option value="1000">¥1,000以下</option><option value="3000">¥1,000-3,000</option><option value="5000">¥3,000-5,000</option><option value="10000">¥5,000-10,000</option><option value="10001">¥10,000以上</option></select></div>
      <div class="filter-group"><label>カテゴリー:</label><select id="categoryFilter"><option value="">すべて</option><option value="food">食品・スイーツ</option><option value="beauty">美容・コスメ</option><option value="fashion">ファッション</option><option value="tech">テクノロジー</option><option value="home">生活用品</option><option value="hobby">趣味・エンタメ</option></select></div>
      <div class="filter-group"><label>並び順:</label><select id="sortFilter"><option value="popular">人気順</option><option value="price-low">価格が安い順</option><option value="price-high">価格が高い順</option><option value="new">新着順</option></select></div>
    </div>
  </div>
</section>

<section class="products-section">
  <div class="container">
    <div class="products-grid" id="productsGrid"></div>
  </div>
</section>

<div class="cart-sidebar" id="cartSidebar">
  <div class="cart-header"><h3>カート</h3><button class="close-cart" id="closeCart"><i class="fas fa-times"></i></button></div>
  <div class="cart-items" id="cartItems"></div>
  <div class="cart-footer"><div class="cart-total"><span>合計:</span><span id="cartTotal">¥0</span></div><button class="btn-primary checkout-btn" id="checkoutBtn">川に流す</button></div>
</div>

<div class="cart-toggle" id="cartToggle"><i class="fas fa-shopping-cart"></i><span class="cart-count" id="cartCount">0</span></div>

<?php include __DIR__.'/includes/footer.php'; ?>
<script src="shop.js"></script> 