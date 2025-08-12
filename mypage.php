<?php $pageTitle = 'マイページ - ギフトループ'; $pageDescription = 'ギフトループのマイページ。あなたの贈り物履歴やアカウント設定を管理できます。'; include __DIR__.'/includes/header.php'; ?>
<?php /* Content derived from mypage.php, unchanged except .php links are handled by the shared header/footer */ ?>

<section class="mypage-header">
  <div class="container">
    <div class="mypage-hero">
      <div class="user-profile-card">
        <div class="profile-avatar"><i class="fas fa-user-circle"></i></div>
        <div class="profile-info" style="text-align: center">
          <h1 class="profile-name" style="font-size: 2.2rem; word-break: break-word; max-width: 100%; overflow-wrap: break-word;">ユーザー名</h1>
          <p class="profile-email">user@example.com</p>
          <div class="membership-badge"><i class="fas fa-crown"></i><span>ベーシック会員</span></div>
        </div>
        <div class="profile-stats">
          <div class="stat-item"><span class="stat-number" id="points">500</span><span class="stat-label">ポイント</span></div>
          <div class="stat-item"><span class="stat-number" id="gifts-received">0</span><span class="stat-label">受け取り</span></div>
          <div class="stat-item"><span class="stat-number" id="gifts-sent">0</span><span class="stat-label">送付</span></div>
          <div class="stat-item"><span class="stat-number" id="corp-gifts-received">0</span><span class="stat-label">会社ギフト受け取り</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="mypage-content">
  <div class="container">
    <div class="mypage-tabs">
      <button class="mypage-tab active" data-tab="overview"><i class="fas fa-home"></i> 概要</button>
      <button class="mypage-tab" data-tab="gifts"><i class="fas fa-gift"></i> 贈り物履歴</button>
      <button class="mypage-tab" data-tab="settings"><i class="fas fa-cog"></i> 設定</button>
      <button class="mypage-tab" data-tab="membership"><i class="fas fa-crown"></i> 会員プラン</button>
    </div>

    <div class="mypage-tab-content active" id="overview-content">
      <div class="overview-grid">
        <div class="overview-card">
          <div class="card-header"><h3><i class="fas fa-chart-line"></i> 最近の活動</h3></div>
          <div class="activity-list">
            <div class="activity-item"><div class="activity-icon"><i class="fas fa-gift"></i></div><div class="activity-content"><p>新しい贈り物を受け取りました</p><span class="activity-time">2時間前</span></div></div>
            <div class="activity-item"><div class="activity-icon"><i class="fas fa-water"></i></div><div class="activity-content"><p>贈り物の川に参加しました</p><span class="activity-time">1日前</span></div></div>
            <div class="activity-item"><div class="activity-icon"><i class="fas fa-user-plus"></i></div><div class="activity-content"><p>ギフトループに参加しました</p><span class="activity-time">1週間前</span></div></div>
          </div>
        </div>

        <div class="overview-card">
          <div class="card-header"><h3><i class="fas fa-trophy"></i> 実績</h3></div>
          <div class="achievements-list">
            <div class="achievement-item"><div class="achievement-icon"><i class="fas fa-star"></i></div><div class="achievement-content"><h4>初回参加</h4><p>ギフトループに初めて参加しました</p></div></div>
            <div class="achievement-item locked"><div class="achievement-icon"><i class="fas fa-gift"></i></div><div class="achievement-content"><h4>贈り物マスター</h4><p>10個の贈り物を受け取る</p></div></div>
            <div class="achievement-item locked"><div class="achievement-icon"><i class="fas fa-crown"></i></div><div class="achievement-content"><h4>スーパープレゼント</h4><p>スーパープレゼントを受け取る</p></div></div>
          </div>
        </div>

        <div class="overview-card">
          <div class="card-header"><h3><i class="fas fa-bell"></i> お知らせ</h3></div>
          <div class="notifications-list">
            <div class="notification-item"><div class="notification-icon"><i class="fas fa-info-circle"></i></div><div class="notification-content"><p>新しい機能が追加されました</p><span class="notification-time">3日前</span></div></div>
            <div class="notification-item"><div class="notification-icon"><i class="fas fa-gift"></i></div><div class="notification-content"><p>特別な贈り物が川に流れています</p><span class="notification-time">1週間前</span></div></div>
          </div>
        </div>
      </div>
    </div>

    <div class="mypage-tab-content" id="gifts-content">
      <div class="gifts-section">
        <div class="gifts-header"><h2>贈り物履歴</h2><div class="gifts-filter"><select id="gift-filter"><option value="all">すべて</option><option value="received">受け取り</option><option value="sent">送付</option></select></div></div>
        <div class="gifts-grid" id="gifts-grid"></div>
      </div>
    </div>

    <div class="mypage-tab-content" id="settings-content">
      <div class="settings-section">
        <div class="settings-card"><h3>プロフィール設定</h3>
          <form class="profile-form" style="display: flex; flex-direction: column; justify-content: center;">
            <div class="form-row">
              <div class="form-group"><label for="profile-firstname">姓</label><input type="text" id="profile-firstname" name="firstname" /></div>
              <div class="form-group"><label for="profile-lastname">名</label><input type="text" id="profile-lastname" name="lastname" /></div>
            </div>
            <div class="form-group"><label for="profile-email">メールアドレス</label><input type="email" id="profile-email" name="email" /></div>
            <div class="form-group"><label for="profile-birthdate">生年月日</label><input type="date" id="profile-birthdate" name="birthdate" /></div>
            <button type="submit" class="btn-primary">プロフィールを更新</button>
          </form>
        </div>

        <div class="settings-card"><h3>パスワード変更</h3>
          <form class="password-form">
            <div class="form-group"><label for="current-password">現在のパスワード</label><input type="password" id="current-password" name="current-password" /></div>
            <div class="form-group"><label for="new-password">新しいパスワード</label><input type="password" id="new-password" name="new-password" /></div>
            <div class="form-group"><label for="confirm-new-password">新しいパスワード（確認）</label><input type="password" id="confirm-new-password" name="confirm-new-password" /></div>
            <button type="submit" class="btn-primary">パスワードを変更</button>
          </form>
        </div>

        <div class="settings-card"><h3>通知設定</h3>
          <div class="notification" style="display: flex;flex-direction: column;align-items: center;">
            <div class="notification-settings" style="display: flex; align-items: flex-start; flex-direction: column;">
              <label class="checkbox-label"><input type="checkbox" name="email-notifications" checked /><span class="checkmark"></span>メール通知を受け取る</label>
              <label class="checkbox-label"><input type="checkbox" name="gift-notifications" checked /><span class="checkmark"></span>新しい贈り物のお知らせ</label>
            </div>
          </div>
          <div class="saveSetting" style="text-align: center; display: flex; justify-content: center;"><button class="btn-primary">設定を保存</button></div>
        </div>
      </div>
    </div>

    <div class="mypage-tab-content" id="membership-content">
      <div class="membership-section">
        <div class="current-plan">
          <h2>現在のプラン</h2>
          <div class="plan-card current"><div class="plan-header"><h3>ベーシック</h3><div class="plan-price">¥0<span>/月</span></div></div>
            <ul class="plan-features">
              <li><i class="fas fa-check"></i> 基本的な贈り物機能</li>
              <li><i class="fas fa-check"></i> 月5回の受け取り権利</li>
              <li><i class="fas fa-check"></i> 標準的な商品選択</li>
              <li><i class="fas fa-times"></i> スーパープレゼント対象外</li>
            </ul>
          </div>
        </div>
        <div style="margin-top: 2rem; text-align: center">
          <button class="btn-primary" id="change-plan-btn" style="font-size: 1.1rem; padding: 0.8em 2.2em; margin-top: 1em">プランを変更する</button>
        </div>
        <script>document.addEventListener('DOMContentLoaded', function(){var b=document.getElementById('change-plan-btn'); if(b){b.addEventListener('click', function(){window.location.href='index.php#pricing';});}});</script>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__.'/includes/footer.php'; ?>
<script src="mypage.js"></script> 