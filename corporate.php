<?php $pageTitle = '企業管理 - ギフトループ'; $pageDescription = '企業管理ページ。ギフトオファー登録、掲載期間設定、当選番号管理、画像・説明の投稿を行います。'; include __DIR__.'/includes/header.php'; ?>

<section class="corp-header" style="margin-top: 4.5rem;">
  <div class="container"><div class="corp-hero" style="text-align: center;"><h1><i class="fas fa-building"></i> 企業管理</h1><p>ギフトオファー登録・掲載期間設定・当選番号管理</p></div></div>
</section>

<section class="corp-content">
  <div class="container">
    <div class="form-card">
      <div class="form-header"><h2><i class="fas fa-plus-circle"></i> ギフトオファーを登録</h2><p>画像・説明・カテゴリ・価格・掲載期間を設定します</p></div>
      <div class="form-row"><div class="form-group"><label for="offerName">名称</label><input type="text" id="offerName" placeholder="例: サマーフェス限定ギフト"></div><div class="form-group"><label for="offerCategory">カテゴリー</label><select id="offerCategory"><option value="food">食品・スイーツ</option><option value="fashion">ファッション</option><option value="beauty">美容・コスメ</option><option value="electronics">家電・電子機器</option><option value="books">書籍・文具</option><option value="hobby">趣味・スポーツ</option><option value="home">生活用品</option><option value="other">その他</option></select></div></div>
      <div class="form-row"><div class="form-group"><label for="offerPrice">価格 (¥)</label><input type="number" id="offerPrice" min="0" step="100" placeholder="2000"></div><div class="form-group"><label for="offerSuper">特別(SUPER)</label><select id="offerSuper"><option value="false">いいえ</option><option value="true">はい</option></select></div></div>
      <div class="form-row"><div class="form-group"><label for="offerStart">掲載開始</label><input type="datetime-local" id="offerStart"></div><div class="form-group"><label for="offerEnd">掲載終了</label><input type="datetime-local" id="offerEnd"></div></div>
      <div class="form-row"><div class="form-group" style="grid-column: 1 / -1;"><label for="offerImage">画像</label><input type="file" id="offerImage" accept="image/*"><div class="image-preview" id="offerImagePreview"></div></div></div>
      <div class="form-group"><label for="offerDescription">説明</label><textarea id="offerDescription" rows="4" placeholder="ギフトの説明を入力"></textarea></div>
      <div class="form-actions" style="justify-content: flex-end;"><button class="btn-primary" id="resetOffer"><i class="fas fa-rotate"></i> リセット</button><button class="btn-primary" id="saveOffer"><i class="fas fa-save"></i> 登録</button></div>
    </div>

    <div class="corp-toolbar admin-toolbar" style="justify-content: space-between;"><div class="input-wrapper"><i class="fas fa-search"></i><input type="text" id="corpSearch" placeholder="オファーを検索 (名称/カテゴリ/状態)"></div><div class="chip-group" id="corpStatusFilters"></div></div>

    <div id="offersList" class="admin-list"></div>
  </div>
</section>

<div id="numbersModal" class="modal" aria-hidden="true">
  <div class="modal-backdrop"></div>
  <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="numbersModalTitle">
    <div class="modal-header"><h3 id="numbersModalTitle"><i class="fas fa-hashtag"></i> 当選番号の管理</h3><button class="modal-close" id="closeNumbersModal" aria-label="閉じる"><i class="fas fa-times"></i></button></div>
    <div class="modal-body">
      <div class="numbers-actions" style="display:flex; flex-wrap:wrap; gap:.5rem; align-items:center;">
        <div class="input-wrapper"><i class="fas fa-plus"></i><input type="text" id="newNumber" placeholder="番号を追加 (例: 123456)"></div>
        <button class="btn-primary" id="addNumberBtn">追加</button>
        <div class="input-wrapper"><i class="fas fa-hashtag"></i><input type="number" id="genCount" min="1" step="1" placeholder="30"></div>
        <div class="input-wrapper"><i class="fas fa-ruler-horizontal"></i><input type="number" id="genLength" min="4" step="1" placeholder="8"></div>
        <button class="btn-primary" id="generateNumbersBtn">ランダム生成</button>
        <label class="btn-primary" for="importNumbersFile" style="cursor:pointer; display:inline-flex; align-items:center; gap:.5rem;"><i class="fas fa-file-import"></i> CSV取り込み</label>
        <input type="file" id="importNumbersFile" accept=".csv" style="display:none;" />
        <button class="btn-primary" id="exportNumbersBtn"><i class="fas fa-file-export"></i> CSV書き出し</button>
      </div>
      <div class="admin-table-wrapper" style="margin-top:.75rem;"><div style="max-height: 320px; overflow-y: auto;"><table class="admin-table" id="numbersTable"><thead><tr><th>番号</th><th>状態</th><th>操作</th></tr></thead><tbody></tbody></table></div></div>
    </div>
    <div class="modal-actions"><button class="btn-primary" id="closeNumbersBtn">閉じる</button></div>
  </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>
<script src="corporate.js"></script> 