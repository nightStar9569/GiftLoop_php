<?php $pageTitle = 'お問い合わせ - ギフトループ'; $pageDescription = 'ギフトループへのお問い合わせページ。サポートやご要望をお送りください。'; include __DIR__.'/includes/header.php'; ?>

<section class="contact-header" style="margin-top: 4.5rem;">
  <div class="container"><div class="contact-hero" style="text-align: center;"><h1><i class="fas fa-envelope-open-text"></i> お問い合わせ</h1><p>サポートが必要な場合やご意見・ご要望があればお知らせください。</p></div></div>
</section>

<section class="contact-content">
  <div class="container">
    <div class="contact-grid">
      <div class="form-card">
        <div class="form-header"><h2><i class="fas fa-headset"></i> お問い合わせフォーム</h2><p>通常1〜2営業日以内にご返信いたします。</p></div>
        <form id="contactForm">
          <div class="form-row"><div class="form-group"><label for="contactName">お名前</label><input type="text" id="contactName" placeholder="山田 太郎" required></div><div class="form-group"><label for="contactEmail">メールアドレス</label><input type="email" id="contactEmail" placeholder="example@example.com" required></div></div>
          <div class="form-group"><label for="contactSubject">件名</label><input type="text" id="contactSubject" placeholder="ご用件を簡潔にお書きください" required></div>
          <div class="form-group"><label for="contactMessage">内容</label><textarea id="contactMessage" rows="5" placeholder="お問い合わせ内容をご記入ください" required></textarea></div>
          <div class="form-actions" style="display:flex; justify-content:center;gap:1rem;"><button type="reset" class="btn-primary">クリア</button><button type="submit" class="btn-primary">送信</button></div>
        </form>
      </div>

      <div class="form-card">
        <div class="form-header"><h2><i class="fas fa-question-circle"></i> よくあるご質問</h2><p>お問い合わせの前にご確認ください</p></div>
        <div class="faq-list">
          <details><summary>ギフトを受け取れません</summary><p>ログイン状態とネットワーク状態をご確認のうえ、再度お試しください。</p></details>
          <details><summary>スーパープレゼントとは？</summary><p>特別な限定ギフトです。アプリ内でバッジ表示されます。</p></details>
          <details><summary>退会はできますか？</summary><p>マイページの設定からお問い合わせください。対応させていただきます。</p></details>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__.'/includes/footer.php'; ?>
<script>
document.getElementById('contactForm')?.addEventListener('submit', function(e){e.preventDefault(); if(window.showNotification){showNotification('お問い合わせを送信しました。ありがとうございます！','success');} this.reset();});
</script> 