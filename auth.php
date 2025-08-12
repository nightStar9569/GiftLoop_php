<?php $pageTitle = 'ログイン・会員登録 - ギフトループ';
$pageDescription = 'ギフトループにログインまたは会員登録して、贈り物の川を楽しもう。';
include __DIR__ . '/includes/header.php'; ?>

<section class="auth-section">
  <div class="container">
    <div class="auth-container">
      <div class="auth-tabs">
        <button class="auth-tab active" data-tab="login"><i class="fas fa-sign-in-alt"></i> ログイン</button>
        <button class="auth-tab" data-tab="register"><i class="fas fa-user-plus"></i> 会員登録</button>
      </div>

      <div class="auth-form active" id="login-form">
        <div class="form-header">
          <h2>ログイン</h2>
          <p>ギフトループ ー アカウントにログインしてください</p>
        </div>
        <form class="login-form">
          <div class="form-group">
            <label for="login-email">メールアドレス</label>
            <div class="input-wrapper"><input type="email" id="login-email" name="email" required></div>
          </div>
          <div class="form-group">
            <label for="login-password">パスワード</label>
            <div class="input-wrapper">
              <input type="password" id="login-password" name="password" required>
              <button type="button" class="password-toggle"><i class="fas fa-eye"></i></button>
            </div>
          </div>
          <div class="form-options">
            <label class="checkbox-label"><input type="checkbox" name="remember"><span
                class="checkmark"></span>ログイン状態を保持</label>
            <a href="#" class="forgot-password">パスワードを忘れた方</a>
          </div>
          <button type="submit" class="btn-auth"><i class="fas fa-sign-in-alt"></i> ログイン</button>
        </form>
        <div class="social-login">
          <div class="divider" role="separator" aria-label="または" style="text-align: center;margin-bottom: 20px;"><span>または</span></div>
          <div class="social-buttons">
            <button class="btn-social btn-google"><i class="fab fa-google"></i> Googleでログイン</button>
          </div>
        </div>
      </div>

      <div class="auth-form" id="register-form">
        <div class="form-header">
          <h2>会員登録</h2>
          <p>新しいギフトループ ー アカウントを作成してください</p>
        </div>
        <form class="register-form">
          <div class="form-row">
            <div class="form-group"><label for="register-firstname">姓</label>
              <div class="input-wrapper"><input type="text" id="register-firstname" name="firstname" required></div>
            </div>
            <div class="form-group"><label for="register-lastname">名</label>
              <div class="input-wrapper"><input type="text" id="register-lastname" name="lastname" required></div>
            </div>
          </div>
          <div class="form-group"><label for="register-email">メールアドレス</label>
            <div class="input-wrapper"><input type="email" id="register-email" name="email" required></div>
          </div>
          <div class="form-group"><label for="register-password">パスワード</label>
            <div class="input-wrapper"><input type="password" id="register-password" name="password" required><button
                type="button" class="password-toggle"><i class="fas fa-eye"></i></button></div>
          </div>
          <div class="form-group"><label for="register-confirm-password">パスワード（確認）</label>
            <div class="input-wrapper"><input type="password" id="register-confirm-password" name="confirm-password"
                required><button type="button" class="password-toggle"><i class="fas fa-eye"></i></button></div>
          </div>
          <div class="form-group"><label for="register-birthdate">生年月日</label>
            <div class="input-wrapper"><input type="date" id="register-birthdate" name="birthdate" required></div>
          </div>
          <div class="form-options"><label class="checkbox-label"><input type="checkbox" name="terms" required><span
                class="checkmark"></span><a href="terms.php" target="_blank">利用規約</a>と<a href="privacy.php"
                target="_blank">プライバシーポリシー</a>に同意します</label></div>
          <button type="submit" class="btn-auth"><i class="fas fa-user-plus"></i> 会員登録</button>
        </form>
        <div class="social-register">
          <div class="divider" role="separator" aria-label="または" style="text-align: center;margin-block: 20px;"><span>または</span></div>
          <div class="social-buttons">
            <button class="btn-social btn-google"><i class="fab fa-google"></i> Googleで登録</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php $pageScripts = ['auth.js'];
include __DIR__ . '/includes/footer.php'; ?>