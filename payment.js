// Payment page logic
(function() {
  function getQueryParam(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
  }

  function formatYen(numStr) {
    const n = parseInt(numStr || '0', 10);
    return `¥${(isNaN(n) ? 0 : n).toLocaleString()}`;
  }

  document.addEventListener('DOMContentLoaded', () => {
    // Populate selection
    const planSlug = (getQueryParam('plan') || 'basic').toLowerCase();
    const price = getQueryParam('price') || '0';

    const planLabelMap = {
      basic: 'ベーシック',
      premium: 'プレミアム',
      business: 'ビジネス'
    };

    const selectedPlanLabel = document.getElementById('selectedPlanLabel');
    const selectedPriceLabel = document.getElementById('selectedPriceLabel');

    if (selectedPlanLabel) selectedPlanLabel.textContent = planLabelMap[planSlug] || 'ベーシック';
    if (selectedPriceLabel) selectedPriceLabel.textContent = formatYen(price);

    // Handle form submit (mock)
    const form = document.getElementById('paymentForm');
    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        // Require login before payment
        const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
        if (!isLoggedIn) {
          if (window.app && typeof window.app.showNotification === 'function') {
            window.app.showNotification('ログインが必要です。ログインページに移動します。', 'info');
          }
          setTimeout(() => {
            window.location.href = 'auth.php';
          }, 600);
          return;
        }
        // Very simple mock validation
        const inputs = form.querySelectorAll('input[required]');
        for (const input of inputs) {
          if (!input.value.trim()) {
            if (window.app && typeof window.app.showNotification === 'function') {
              window.app.showNotification('入力内容を確認してください。', 'error');
            }
            return;
          }
        }

        try {
          await giftApi.subscribe({ plan: planSlug, price: Number(price || '0') });
          if (window.app && typeof window.app.showNotification === 'function') {
            window.app.showNotification('お支払いが完了しました。ありがとうございます！', 'success');
          }
          window.location.href = 'mypage.php';
        } catch (e) {
          if (window.app && typeof window.app.showNotification === 'function') {
            window.app.showNotification(e.message || 'お支払いに失敗しました。', 'error');
          }
        }
      });
    }
  });
})(); 