<?php $pageTitle = '贈り物を受け取る - ギフトループ'; $pageDescription = '流れてきた贈り物を確認して、受け取るか拒否するかを選択できます。'; include __DIR__.'/includes/header.php'; ?>

<section class="receive-gift-header" style="margin-top: 4.5rem;">
  <div class="container">
    <div class="receive-gift-hero" style="text-align: center;">
      <h1>贈り物を受け取る</h1>
      <p>流れてきた贈り物を確認して、受け取るか拒否するかを選択してください</p>
    </div>
  </div>
</section>

<section class="receive-gift-content">
  <div class="container">
    <div class="gift-details-card">
      <div class="gift-header">
        <div class="gift-icon"><i class="fas fa-gift" id="giftIcon"></i></div>
        <div class="gift-info"><h2 id="giftName">贈り物の名前</h2><p id="giftDescription">贈り物の説明がここに表示されます</p></div>
      </div>
      <div class="gift-details">
        <div class="detail-item"><span class="detail-label">価格:</span><span class="detail-value">¥<span id="giftPrice">0</span></span></div>
        <div class="detail-item"><span class="detail-label">カテゴリー:</span><span class="detail-value" id="giftCategory">カテゴリー</span></div>
        <div class="detail-item"><span class="detail-label">送信者:</span><span class="detail-value" id="giftSender">送信者名</span></div>
        <div class="detail-item"><span class="detail-label">送信日時:</span><span class="detail-value" id="giftDate">日時</span></div>
      </div>
      <div class="gift-actions">
        <button class="btn-primary" id="rejectGift"><i class="fas fa-times"></i> 拒否する</button>
        <button class="btn-primary" id="acceptGift"><i class="fas fa-heart"></i> 受け取る</button>
      </div>
    </div>

    <div class="gift-image-section" id="giftImageSection" style="display: none;">
      <h3>贈り物の画像</h3>
      <div class="gift-image-container"><img id="giftImage" src="" alt="贈り物の画像"></div>
    </div>

    <div class="similar-gifts">
      <h3>似たような贈り物</h3>
      <div class="similar-gifts-grid" id="similarGiftsGrid"></div>
    </div>
  </div>
</section>

<div id="acceptConfirmModal" class="modal" aria-hidden="true">
  <div class="modal-backdrop"></div>
  <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="acceptModalTitle">
    <div class="modal-header"><h3 id="acceptModalTitle"><i class="fas fa-heart"></i> この贈り物を受け取りますか？</h3><button class="modal-close" id="closeAcceptModal" aria-label="閉じる"><i class="fas fa-times"></i></button></div>
    <div class="modal-body"><div class="modal-row"><span>贈り物</span><strong id="acceptModalGiftName">-</strong></div><div class="modal-row"><span>送信者</span><strong id="acceptModalSender">-</strong></div></div>
    <div class="modal-actions"><button class="btn-primary" id="cancelAcceptBtn">キャンセル</button><button class="btn-primary" id="confirmAcceptBtn"><i class="fas fa-heart"></i> 受け取る</button></div>
  </div>
</div>

<div id="rejectConfirmModal" class="modal" aria-hidden="true">
  <div class="modal-backdrop"></div>
  <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="rejectModalTitle">
    <div class="modal-header"><h3 id="rejectModalTitle"><i class="fas fa-times"></i> この贈り物を拒否しますか？</h3><button class="modal-close" id="closeRejectModal" aria-label="閉じる"><i class="fas fa-times"></i></button></div>
    <div class="modal-body"><div class="modal-row"><span>贈り物</span><strong id="rejectModalGiftName">-</strong></div><div class="modal-row"><span>送信者</span><strong id="rejectModalSender">-</strong></div></div>
    <div class="modal-actions"><button class="btn-primary" id="cancelRejectBtn">キャンセル</button><button class="btn-primary" id="confirmRejectBtn"><i class="fas fa-times"></i> 拒否する</button></div>
  </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>
<script src="receive-gift.js"></script> 