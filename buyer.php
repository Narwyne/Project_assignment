<?php
session_start();
require "config.php";
if (($_SESSION["role"] ?? "") !== "buyer") { header("Location: login.php"); exit; }
$buyer_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    if ($title && $description) {
        $stmt = $pdo->prepare("INSERT INTO posts (buyer_id, title, description) VALUES (?, ?, ?)");
        $stmt->execute([$buyer_id, $title, $description]);
        header("Location: buyer.php");
        exit;
    }
}

$posts = $pdo->prepare("SELECT * FROM posts WHERE buyer_id = ? ORDER BY created_at DESC");
$posts->execute([$buyer_id]);
$posts = $posts->fetchAll(PDO::FETCH_ASSOC);

// sellers who messaged about each post
$repliesStmt = $pdo->prepare("
  SELECT DISTINCT m.sender_id, u.username FROM messages m
  JOIN users u ON u.id = m.sender_id
  WHERE m.post_id = ? AND m.receiver_id = ?
");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Buyer Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page">
  <?php include "partials/header.php"; ?>

  <h2>Post What You're Looking For</h2>
  <form method="POST" class="post-form">
    <input name="title" placeholder="e.g. Looking for iPhone 13, budget 15k" required>
    <textarea name="description" placeholder="Add more details..." required></textarea>
    <button type="submit">Post Request</button>
  </form>

  <h2>My Requests</h2>
  <?php if (!$posts): ?>
    <p>You haven't posted anything yet.</p>
  <?php endif; ?>

  <?php foreach ($posts as $post): ?>
    <div class="post-card">
      <h3><?= htmlspecialchars($post["title"]) ?></h3>
      <p><?= nl2br(htmlspecialchars($post["description"])) ?></p>
      <small><?= $post["created_at"] ?></small>

      <div class="replies">
        <?php
        $repliesStmt->execute([$post["id"], $buyer_id]);
        $sellers = $repliesStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php if ($sellers): ?>
          <strong>Sellers interested:</strong>
          <ul>
            <?php foreach ($sellers as $s): ?>
              <li>
                <?= htmlspecialchars($s["username"]) ?>
                <a class="btn-small" href="chat.php?post_id=<?= $post['id'] ?>&with=<?= $s['sender_id'] ?>">Reply</a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <em>No responses yet.</em>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
</body>
</html>
