<?php
if (!isset($pageTitle)) { $pageTitle = 'ギフトループ - 贈り物のループを流そう'; }
if (!isset($pageDescription)) { $pageDescription = 'お互いに贈り物ができる新しいプラットフォーム。ランダムに流れてくる贈り物を受け取り、自分も贈り物を流して楽しもう。'; }
$__cfg = @require __DIR__ . '/../api/config.php';
$__appBase = is_array($__cfg) && isset($__cfg['app']['base_path']) ? $__cfg['app']['base_path'] : '';
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>" />
    <link rel="stylesheet" href="./css/styles.css" />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script>window.APP_BASE = <?php echo json_encode($__appBase, JSON_UNESCAPED_SLASHES); ?>;</script>
  </head>
  <body>
    <nav class="navbar">
      <div class="nav-container">
        <a href="index.php" class="nav-logo" style="text-decoration-line: none;">
          <i class="fas fa-gift"></i>
          <span>ギフトループ</span>
        </a>
        <ul class="nav-menu">
          <li><a href="index.php" data-nav="home" title="ホーム">ホーム</a></li>
          <li><a href="shop.php" data-nav="shop" title="ショップ">ショップ</a></li>
          <li><a href="river.php" data-nav="river" title="ギフトリバー">ギフトリバー</a></li>
          <li><a href="corporate-river.php" data-nav="corporate-river" title="企業ギフト">企業ギフト</a></li>
          <li><a href="send-gift.php" data-nav="send-gift" title="ギフトを贈る">ギフトを贈る</a></li>
          <li><a href="receive-gift.php" data-nav="receive-gift" title="ギフトを受け取る">ギフトを受け取る</a></li>
          <li class="admin-nav" style="display: none"><a href="admin.php" data-nav="admin">管理</a></li>
          <li class="user-menu" style="display: none">
            <a href="mypage.php" class="user-profile">
              <i class="fas fa-user-circle"></i>
              <span class="user-name">ユーザー</span>
            </a>
            <div class="user-dropdown">
              <a href="mypage.php"><i class="fas fa-user"></i> マイページ</a>
              <a href="#" id="logout-btn"><i class="fas fa-sign-out-alt"></i> ログアウト</a>
            </div>
          </li>
          <li>
            <a href="auth.php" class="btn-login" id="login-btn" title="ログイン / 登録">ログイン/登録</a>
          </li>
        </ul>
        <div class="hamburger">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
    </nav> 