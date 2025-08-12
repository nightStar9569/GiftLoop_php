<?php $pageTitle = '管理者ダッシュボード - ギフトループ'; $pageDescription = 'ギフト投稿管理、会社ギフト管理、ユーザー監視を行う管理者ページ。'; include __DIR__.'/includes/header.php'; ?>

<section class="admin-header" style="margin-top: 4.5rem;">
  <div class="container"><div class="admin-hero" style="text-align: center;"><h1><i class="fas fa-shield-alt"></i> 管理者ダッシュボード</h1><p>ギフト投稿管理・会社ギフト管理・ユーザー監視</p></div></div>
</section>

<section class="admin-content">
  <div class="container">
    <div class="admin-tabs"><button class="admin-tab active" data-tab="gifts"><i class="fas fa-boxes"></i> ギフト管理</button><button class="admin-tab" data-tab="corporate"><i class="fas fa-building"></i> 会社ギフト</button><button class="admin-tab" data-tab="users"><i class="fas fa-users"></i> ユーザー監視</button></div>

    <div id="tab-gifts" class="admin-panel active">
      <div class="admin-toolbar" style="margin-block: 2.5rem;">
        <div class="input-wrapper"><input type="text" id="giftSearch" placeholder="名前/カテゴリー"></div>
        <div class="chip-group" id="giftStatusFilters" aria-label="ステータスで絞り込む"></div>
        <div class="chip-group" id="giftCategoryFilters" aria-label="カテゴリーで絞り込む" style="margin-block: 1.5rem;"></div>
        <div class="sort-control"><label for="giftSort">並び替え</label><select id="giftSort"><option value="recent">新着</option><option value="price_desc">価格（高い順）</option><option value="price_asc">価格（安い順）</option><option value="name_asc">名前（A→Z）</option></select></div>
      </div>
      <div id="giftsList" class="admin-list" style="margin-block: 2.5rem;"></div>
    </div>

    <div id="tab-corporate" class="admin-panel">
      <div class="form-card">
        <div class="form-header"><h2><i class="fas fa-plus-circle"></i> 会社ギフトを追加</h2><p>企業向けギフトテンプレートを作成します</p></div>
        <div class="form-row"><div class="form-group"><label for="corpName">名称</label><input type="text" id="corpName" placeholder="例: 御中元セット"></div><div class="form-group"><label for="corpCategory">カテゴリー</label><select id="corpCategory"><option value="food">食品・スイーツ</option><option value="fashion">ファッション</option><option value="beauty">美容・コスメ</option><option value="electronics">家電・電子機器</option><option value="books">書籍・文具</option><option value="hobby">趣味・スポーツ</option><option value="home">生活用品</option><option value="other">その他</option></select></div></div>
        <div class="form-row"><div class="form-group"><label for="corpPrice">価格 (¥)</label><input type="number" id="corpPrice" min="0" step="100" placeholder="1000"></div><div class="form-group"><label for="corpQuantity">数量</label><input type="number" id="corpQuantity" min="1" step="1" placeholder="50"></div></div>
        <div class="form-actions" style="justify-content: flex-end;"><button id="addCorporateGift" class="btn-primary"><i class="fas fa-save"></i> 追加</button></div>
      </div>
      <div class="admin-list" id="corporateGiftsList"></div>
    </div>

    <div id="tab-users" class="admin-panel">
      <div class="admin-toolbar" style="justify-content: space-between;"><div class="stats" id="userStats"></div><div class="admin-actions"><button id="exportData" class="btn-primary"><i class="fas fa-file-export"></i> データ書き出し</button><button id="refreshData" class="btn-primary"><i class="fas fa-rotate"></i> 更新</button></div></div>
      <div class="admin-table-wrapper"><table class="admin-table" id="usersTable"><thead><tr><th>ユーザー</th><th>メール</th><th>送信数</th><th>受信数</th><th>最終活動</th></tr></thead><tbody></tbody></table></div>
    </div>
  </div>
</section>

<?php include __DIR__.'/includes/footer.php'; ?>
<script src="admin.js"></script> 