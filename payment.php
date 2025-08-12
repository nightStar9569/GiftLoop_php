<?php $pageTitle = 'お支払い - ギフトループ'; include __DIR__.'/includes/header.php'; ?>

<section class="payment-section" style="padding: 120px 0 60px; background: #f8fafc;">
  <div class="container">
    <div class="payment-card" style="max-width: 700px; margin: 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden;">
      <div class="payment-header" style="padding: 1.25rem 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;"><h2 style="margin: 0; font-size: 1.25rem; text-align: center;">お支払い</h2></div>
      <div class="payment-content" style="padding: 1.5rem;">
        <div class="summary" style="display: flex; align-items: center; justify-content: space-between; background: #f9fafb; border: 1px solid #eef2f7; border-radius: 12px; padding: 1rem; margin-bottom: 1.25rem;">
          <div><div style="font-weight: 700; font-size: 1.1rem;">選択したプラン</div><div id="selectedPlanLabel" style="color: #6b7280; margin-top: 2px;">-</div></div>
          <div style="text-align: right;"><div style="font-size: 0.9rem; color: #6b7280;">お支払い金額</div><div id="selectedPriceLabel" style="font-weight: 800; font-size: 1.4rem; color: #111827;">¥0</div></div>
        </div>
        <form id="paymentForm" class="payment-form" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div style="grid-column: span 2;"><label style="display:block; font-weight:600; margin-bottom: 6px;">カード番号</label><input type="text" inputmode="numeric" autocomplete="cc-number" placeholder="4242 4242 4242 4242" style="width:100%; padding: 0.85rem 1rem; border: 2px solid #e5e7eb; border-radius: 10px;" required /></div>
          <div><label style="display:block; font-weight:600; margin-bottom: 6px;">有効期限 (MM/YY)</label><input type="text" inputmode="numeric" placeholder="12/34" style="width:100%; padding: 0.85rem 1rem; border: 2px solid #e5e7eb; border-radius: 10px;" required /></div>
          <div><label style="display:block; font-weight:600; margin-bottom: 6px;">CVC</label><input type="text" inputmode="numeric" placeholder="123" style="width:100%; padding: 0.85rem 1rem; border: 2px solid #e5e7eb; border-radius: 10px;" required /></div>
          <div style="grid-column: span 2;"><label style="display:block; font-weight:600; margin-bottom: 6px;">名義人</label><input type="text" placeholder="TARO YAMADA" style="width:100%; padding: 0.85rem 1rem; border: 2px solid #e5e7eb; border-radius: 10px;" required /></div>
          <div style="grid-column: span 2; display:flex; align-items:center; gap:.5rem;"><input id="agree" type="checkbox" required /><label for="agree" style="font-size: 0.9rem; color: #6b7280;">利用規約とプライバシーポリシーに同意します。</label></div>
          <div style="grid-column: span 2; margin-top: .5rem; display:flex; gap:.75rem; justify-content:center;"><button type="submit" class="btn-primary">支払う</button><button type="button" onclick="window.history.back();" class="btn-primary" style="text-align:center">戻る</button></div>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__.'/includes/footer.php'; ?>
<script src="payment.js"></script> 