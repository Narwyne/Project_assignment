<?php
session_start();
require "config.php";
if (($_SESSION["role"] ?? "") !== "seller") { header("Location: login.php"); exit; }
$seller_id = $_SESSION["user_id"];

$posts = $pdo->query("
  SELECT p.*, u.username AS buyer_name FROM posts p
  JOIN users u ON u.id = p.buyer_id
  ORDER BY p.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Seller Feed</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page">
  <?php include "partials/header.php"; ?>

  <h2>Buyer Requests</h2>
  <?php if (!$posts): ?>
    <p>No requests posted yet.</p>
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
