    <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-section">
            <div class="footer-logo">
              <i class="fas fa-gift"></i>
              <span>ギフトループ</span>
            </div>
            <p>贈り物の新しい体験を提供するプラットフォーム</p>
          </div>
          <div class="footer-section">
            <h4>サービス</h4>
            <ul>
              <li><a href="index.php">ホーム</a></li>
              <li><a href="shop.php">ショップ</a></li>
              <li><a href="river.php">贈り物の川</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h4>会社情報</h4>
            <ul>
              <li><a href="privacy.php">プライバシーポリシー</a></li>
              <li><a href="terms.php">利用規約</a></li>
              <li><a href="contact.php">お問い合わせ</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h4>フォローする</h4>
            <div class="social-links">
              <a href="#"><i class="fab fa-twitter"></i></a>
              <a href="#"><i class="fab fa-instagram"></i></a>
              <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2025 ギフトループ。 無断転載を禁じます。</p>
        </div>
      </div>
    </footer>
 
    <script src="js/notify.js"></script>
    <script src="js/api.js"></script>
    <script src="js/core/App.js"></script>
    <?php if (!empty($pageScripts) && is_array($pageScripts)) { foreach ($pageScripts as $scriptPath) { ?>
      <script src="<?php echo htmlspecialchars($scriptPath, ENT_QUOTES, 'UTF-8'); ?>"></script>
    <?php }} ?>
   </body>
 </html> 