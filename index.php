<?php $pageTitle = 'ギフトループ - 贈り物のループを流そう';
$pageDescription = 'お互いに贈り物ができる新しいプラットフォーム。ランダムに流れてくる贈り物を受け取り、自分も贈り物を流して楽しもう。';
include __DIR__ . '/includes/header.php';
?>

<section id="home" class="hero">
  <div class="hero-container">
    <div class="hero-content" style="display: flex; flex-direction: column; align-items: center; text-align: center;">
      <h1 class="hero-title">贈り物の川を<br /><span class="highlight">流そう</span></h1>
      <p class="hero-description">ランダムに流れてくる贈り物を受け取り、自分も贈り物を流して楽しむ。新しい贈り物体験を始めましょう。</p>
      <div class="hero-buttons" style="display: flex; gap: 1rem; justify-content: center;">
        <button class="btn-primary" onclick="window.location.href='auth.php'">無料で始める</button>
      </div>
    </div>
    <div class="hero-visual">
      <div class="gift-river">
        <div class="river-flow">
          <div class="river-background-animated"></div>
          <div class="gift-flow-container" id="heroGiftFlowContainer"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="how-it-works" class="how-it-works">
  <div class="container">
    <h2 class="section-title">使い方</h2>
    <div class="steps-container">
      <div class="step">
        <div class="step-icon"><i class="fas fa-user-plus"></i></div>
        <h3>1. 会員登録</h3>
        <p>簡単な会員登録でギフトを受け取り、自分もギフトを流そう！プレゼントの川を一緒に楽しもう。</p>
      </div>
      <div class="step">
        <div class="step-icon"><i class="fas fa-gift"></i></div>
        <h3>2. 贈り物を流す</h3>
        <p>商品を選んで贈り物の川に流します。価格に応じて川の流れが決まります。</p>
      </div>
      <div class="step">
        <div class="step-icon"><i class="fas fa-water"></i></div>
        <h3>3. 川から受け取る</h3>
        <p>ランダムに流れてくる贈り物をクリックして受け取ります。</p>
      </div>
      <div class="step">
        <div class="step-icon"><i class="fas fa-sync-alt"></i></div>
        <h3>4. 循環する贈り物</h3>
        <p>受け取った価格に近い贈り物が川に流れてきます。</p>
      </div>
    </div>
  </div>
</section>

<section class="features">
  <div class="container">
    <h2 class="section-title">主な機能</h2>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-random"></i></div>
        <h3>ランダム贈り物</h3>
        <p>川を流れる贈り物は完全にランダム。毎回新しい驚きがあります。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-crown"></i></div>
        <h3>スーパープレゼント</h3>
        <p>会社からの特別な贈り物が時々流れてきます。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-exchange-alt"></i></div>
        <h3>権利システム</h3>
        <p>贈り物を拒否すると権利を失いますが、新しい贈り物を流すと復活します。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-shopping-cart"></i></div>
        <h3>ECショップ連携</h3>
        <p>受け取った贈り物は実際に商品として発送されます。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-users"></i></div>
        <h3>会員制</h3>
        <p>安全で信頼できる会員制システムで安心して利用できます。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
        <h3>レスポンシブ対応</h3>
        <p>スマートフォンやタブレットからも快適に利用できます。</p>
      </div>
    </div>
  </div>
</section>

<section class="demo">
  <div class="container">
    <h2 class="section-title">実際の体験</h2>
    <div class="demo-container">
      <div class="demo-visual hero-visual">
        <div class="gift-river">
          <div class="river-flow">
            <div class="river-background-animated"></div>
            <div class="gift-flow-container" id="demoGiftFlowContainer"></div>
          </div>
        </div>
      </div>
      <div class="demo-content">
        <h3>川を流れる贈り物</h3>
        <p>贈り物は川のように流れ、価格に応じて異なる流れを作ります。クリックして受け取ることで、実際の商品があなたのもとに届きます。</p>
        <ul class="demo-features">
          <li><i class="fas fa-check"></i> 価格に応じた流れ</li>
          <li><i class="fas fa-check"></i> ランダムな贈り物</li>
          <li><i class="fas fa-check"></i> スーパープレゼント</li>
          <li><i class="fas fa-check"></i> 権利システム</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section id="pricing" class="pricing">
  <div class="container">
    <h2 class="section-title">料金プラン</h2>
    <div class="pricing-grid">
      <div class="pricing-card">
        <div class="pricing-header">
          <h3>ベーシック</h3>
          <div class="price">¥0</div>
          <p>月額</p>
        </div>
        <ul class="pricing-features">
          <li><i class="fas fa-check"></i> 基本的な贈り物機能</li>
          <li><i class="fas fa-check"></i> 月5回の受け取り権利</li>
          <li><i class="fas fa-check"></i> 標準的な商品選択</li>
          <li><i class="fas fa-times"></i> スーパープレゼント対象外</li>
        </ul>
        <button class="btn-pricing">無料で始める</button>
      </div>
      <div class="pricing-card featured">
        <div class="pricing-header">
          <h3>プレミアム</h3>
          <div class="price">¥980</div>
          <p>月額</p>
        </div>
        <ul class="pricing-features">
          <li><i class="fas fa-check"></i> 全ての贈り物機能</li>
          <li><i class="fas fa-check"></i> 無制限の受け取り権利</li>
          <li><i class="fas fa-check"></i> 高級商品選択</li>
          <li><i class="fas fa-check"></i> スーパープレゼント対象</li>
        </ul>
        <button class="btn-pricing">プレミアムに登録</button>
      </div>
      <div class="pricing-card">
        <div class="pricing-header">
          <h3>ビジネス</h3>
          <div class="price">¥2,980</div>
          <p>月額</p>
        </div>
        <ul class="pricing-features">
          <li><i class="fas fa-check"></i> 全ての機能</li>
          <li><i class="fas fa-check"></i> 優先サポート</li>
          <li><i class="fas fa-check"></i> カスタム贈り物</li>
          <li><i class="fas fa-check"></i> 分析レポート</li>
        </ul>
        <button class="btn-pricing">ビジネスに登録</button>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="script.js"></script>