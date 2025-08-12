<?php $pageTitle = '贈り物のループ - ギフトループ'; $pageDescription = '川を流れる贈り物を受け取ろう。ランダムに流れてくる贈り物をクリックして、実際の商品を受け取ることができます。'; include __DIR__.'/includes/header.php'; ?>

<section class="river-interface">
  <div class="river-container">
    <div class="user-stats">
      <div class="stat-item"><i class="fas fa-gift"></i><span>受け取り権利: <span id="receiveRights">5</span>回</span></div>
      <div class="stat-item"><i class="fas fa-crown"></i><span>受け取った贈り物: <span id="receivedGifts">0</span>個</span></div>
      <div class="stat-item"><i class="fas fa-coins"></i><span>総価値: ¥<span id="totalValue">0</span></span></div>
    </div>

    <div class="river-main">
      <div class="river-flow-container">
        <div class="river-background-animated"></div>
        <div class="gift-flow-container" id="giftFlowContainer"></div>
      </div>
    </div>

    <div class="river-controls">
      <button class="btn-primary" id="refreshRiver"><i class="fas fa-sync-alt"></i> 川を更新</button>
      <button class="btn-primary" id="shopButton"><i class="fas fa-shopping-cart"></i> 贈り物を買う</button>
    </div>
  </div>
</section>

<div class="modal" id="giftModal">
  <div class="modal-content">
    <div class="modal-header"><h3>贈り物の詳細</h3><button class="close-modal" id="closeModal"><i class="fas fa-times"></i></button></div>
    <div class="modal-body">
      <div class="gift-details">
        <div class="gift-image"><i class="fas fa-gift" id="modalGiftIcon"></i></div>
        <div class="gift-info"><h4 id="modalGiftName">贈り物の名前</h4><p id="modalGiftDescription">贈り物の説明がここに表示されます。</p><div class="gift-price"><span>価格: ¥<span id="modalGiftPrice">0</span></span></div><div class="gift-category"><span>カテゴリー: <span id="modalGiftCategory">-</span></span></div></div>
      </div>
    </div>
    <div class="modal-footer"><button class="btn-primary" id="rejectGift">拒否する</button><button class="btn-primary" id="acceptGift">受け取る</button></div>
  </div>
</div>

<section class="recent-gifts">
  <div class="container">
    <h2>最近受け取った贈り物</h2>
    <div class="recent-gifts-grid" id="recentGiftsGrid"></div>
  </div>
</section>

<?php include __DIR__.'/includes/footer.php'; ?>
<script src="river.js"></script> 