<?php
session_start();
require "config.php";
if (($_SESSION["role"] ?? "") !== "seller") { header("Location: login.php"); exit; }
$seller_id = $_SESSION["user_id"];

$q = trim($_GET["q"] ?? "");

if ($q !== "") {
    $stmt = $pdo->prepare("
      SELECT p.*, u.username AS buyer_name FROM posts p
      JOIN users u ON u.id = p.buyer_id
      WHERE p.title LIKE ? OR p.description LIKE ?
      ORDER BY p.created_at DESC
    ");
    $like = "%$q%";
    $stmt->execute([$like, $like]);
} else {
    $stmt = $pdo->query("
      SELECT p.*, u.username AS buyer_name FROM posts p
      JOIN users u ON u.id = p.buyer_id
      ORDER BY p.created_at DESC
    ");
}
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Seller Feed</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="wide">
<div class="page">
  <?php include "header.php"; ?>

  <h2>Buyer Requests</h2>
  <form method="GET" class="search-bar">
    <input type="text" name="q" placeholder="Search requests..." value="<?= htmlspecialchars($q) ?>">
    <button type="submit">Search</button>
    <?php if ($q !== ""): ?><a href="seller.php" class="btn-small">Clear</a><?php endif; ?>
  </form>

  <?php if (!$posts): ?>
    <p><?= $q !== "" ? "No matching requests." : "No requests posted yet." ?></p>
  <?php endif; ?>

  <?php foreach ($posts as $post): ?>
    <div class="post-card">
      <h3><?= htmlspecialchars($post["title"]) ?></h3>
      <p><?= nl2br(htmlspecialchars($post["description"])) ?></p>
      <small>Posted by <?= htmlspecialchars($post["buyer_name"]) ?> · <?= $post["created_at"] ?></small><br>
      <a class="btn-small" href="chat.php?post_id=<?= $post['id'] ?>&with=<?= $post['buyer_id'] ?>">Message Buyer</a>
    </div>
  <?php endforeach; ?>
</div>
</body>
</html>
