<?php $pageTitle = '贈り物を送る - ギフトループ'; $pageDescription = '特別な贈り物を選んで、贈り物の川に流しましょう。'; include __DIR__.'/includes/header.php'; ?>

<section class="send-gift-header" style="margin-top: 4.5rem;">
  <div class="container">
    <div class="send-gift-hero" style="text-align: center;">
      <h1>贈り物を送る</h1>
      <p>特別な贈り物を選んで、贈り物の川に流しましょう</p>
    </div>
  </div>
</section>

<section class="send-gift-content">
  <div class="container">
    <div class="send-gift-grid" style="display:grid; grid-template-columns: 2fr 1.5fr; gap:2rem; align-items:start;">
      <div class="form-card">
        <div class="form-header"><h2><i class="fas fa-box"></i> 所有している贈り物</h2><p>送る贈り物を選択してください</p></div>
        <div class="form-group"><label class="sr-only" for="ownedGiftsSearch">贈り物を検索</label><div class="input-wrapper"><input type="text" id="ownedGiftsSearch" placeholder="贈り物を検索 (名前/カテゴリー)"></div></div>
        <div class="owned-gifts-toolbar" style="display:flex; gap:1rem; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom: .5rem;">
          <div id="ownedGiftsFilters" class="chip-group" aria-label="カテゴリーで絞り込む" role="group"></div>
          <div class="sort-control" style="display:flex; align-items:center; gap:.5rem;"><label for="ownedGiftsSort" style="color:#6b7280; font-size:.9rem;">並び替え</label><select id="ownedGiftsSort" style="padding:.6rem .9rem; border:1px solid #e5e7eb; border-radius:10px; background:#fff;"><option value="recommended">おすすめ</option><option value="latest">新着</option><option value="price_asc">価格（安い順）</option><option value="price_desc">価格（高い順）</option><option value="name_asc">名前（A→Z）</option></select></div>
        </div>
        <div id="ownedGiftsList" style="display:flex; flex-direction:column; gap:0.75rem; max-height: 420px; overflow:auto;"></div>
      </div>

      <div style="display:flex; flex-direction:column; gap:2rem;">
        <div class="form-card">
          <div class="form-header"><h2><i class="fas fa-user-friends"></i> 受取人の選択</h2><p>名前 または メールアドレスを入力してください</p></div>
          <div class="form-group"><label for="recipientName">名前 (任意)</label><div class="input-wrapper"><input type="text" id="recipientName" placeholder="例: 山田 太郎" autocomplete="name"></div></div>
          <div class="form-group"><label for="recipientEmail">メールアドレス (任意)</label><div class="input-wrapper"><input type="email" id="recipientEmail" placeholder="example@example.com" autocomplete="email"></div></div>
          <div class="form-group" id="contactSuggestions" style="display:none;"></div>
        </div>

        <div class="form-card">
          <div class="form-header"><h2><i class="fas fa-paper-plane"></i> 確認して送信</h2><p>内容を確認して送信してください</p></div>
          <div class="preview-card" style="display:flex; gap:1rem; align-items:center;">
            <div class="preview-gift-icon"><i class="fas fa-gift" id="previewIcon"></i></div>
            <div class="preview-content"><h4 id="previewName">贈り物が未選択です</h4><div class="preview-price"><span>¥<span id="previewPrice">0</span></span></div><div class="preview-category"><span id="previewCategory">カテゴリー</span></div></div>
          </div>
          <div class="recipient-summary" id="recipientSummary"></div>
          <div class="form-actions" style="margin-top:1rem; display:flex; gap:.75rem;"><button type="button" class="btn-primary" onclick="window.history.back()">キャンセル</button><button id="sendSelectedGiftBtn" type="button" class="btn-primary" disabled><i class="fas fa-paper-plane"></i> 贈り物を送る</button></div>
        </div>
      </div>
    </div>
  </div>
</section>

<div id="sendConfirmModal" class="modal" aria-hidden="true">
  <div class="modal-backdrop" data-dismiss="modal"></div>
  <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-header"><h3 id="modalTitle"><i class="fas fa-paper-plane"></i> 贈り物を送信しますか？</h3><button class="modal-close" id="closeModalBtn" aria-label="閉じる"><i class="fas fa-times"></i></button></div>
    <div class="modal-body"><div class="modal-row"><span>贈り物</span><strong id="modalGiftName">-</strong></div><div class="modal-row"><span>受取人</span><strong id="modalRecipient">-</strong></div></div>
    <div class="modal-actions"><button class="btn-primary" id="cancelModalBtn">キャンセル</button><button class="btn-primary" id="confirmSendBtn"><i class="fas fa-paper-plane"></i> 送信する</button></div>
  </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>
<script src="send-gift.js"></script> 