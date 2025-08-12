<?php $pageTitle = '企業ギフトの川 - ギフトループ'; $pageDescription = '企業が提供するギフトをスロット抽選で受け取ろう。1日1回無料チャレンジ。'; include __DIR__.'/includes/header.php'; ?>

<section class="river-interface">
  <div class="river-container">
    <div class="user-stats">
      <div class="stat-item"><i class="fas fa-ticket"></i><span>チケット: <span id="ticketCount">-</span></span></div>
      <div class="stat-item"><i class="fas fa-calendar-day"></i><span>本日の無料: <span id="dailyFree">-</span></span></div>
    </div>

    <div class="river-main">
      <div class="river-flow-container">
        <div class="river-background-animated"></div>
        <div class="gift-flow-container" id="corpGiftFlow"></div>
      </div>
    </div>

    <div class="river-controls">
      <button class="btn-primary" id="drawNow"><i class="fas fa-random"></i> 今すぐ抽選</button>
    </div>
  </div>
</section>

<section class="recent-gifts" style="padding-top: 0;">
  <div class="container">
    <h2>現在の提供ギフト</h2>
    <div class="recent-gifts-grid" id="activeCorporateGifts"></div>
  </div>
</section>

<?php $pageScripts = ['js/pages/corporate-river.js']; include __DIR__ . '/includes/footer.php'; ?> 