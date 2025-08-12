<?php
require __DIR__ . '/bootstrap.php';

$items = [
    ['プレミアムチョコレートセット', '厳選された高級チョコレートの詰め合わせ', 2500, 'food', 'fas fa-cookie-bite'],
    ['アロマキャンドル', 'リラックスタイムを演出する香り高いキャンドル', 1800, 'home', 'fas fa-fire'],
    ['スキンケアセット', '肌を美しく保つ高品質なスキンケア商品', 4500, 'beauty', 'fas fa-spa'],
    ['ワイヤレスイヤホン', '高音質で快適なワイヤレスイヤホン', 8500, 'tech', 'fas fa-headphones'],
    ['手編みマフラー', '温かくておしゃれな手編みマフラー', 3200, 'fashion', 'fas fa-scarf'],
    ['パズルゲーム', '頭脳を鍛える楽しいパズルゲーム', 1500, 'hobby', 'fas fa-puzzle-piece'],
    ['オーガニックティー', '体に優しいオーガニックティーのセット', 2800, 'food', 'fas fa-mug-hot'],
    ['デスクライト', '目に優しいLEDデスクライト', 3800, 'home', 'fas fa-lightbulb'],
];

$pdo = db();
$pdo->exec('DELETE FROM products');
$stmt = $pdo->prepare('INSERT INTO products (name, description, price, category, icon) VALUES (?,?,?,?,?)');
foreach ($items as $it) {
    $stmt->execute($it);
}

echo "Seeded products: ".count($items)."\n"; 