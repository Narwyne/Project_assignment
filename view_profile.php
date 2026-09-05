<?php
session_start();
require "config.php";
require "avatar_helper.php";
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit; }

$id = (int)($_GET["id"] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$profileUser = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$profileUser) { die("User not found."); }

$isSelf = $profileUser["id"] == $_SESSION["user_id"];
$backLink = $_SESSION["role"] === "buyer" ? "buyer.php" : "seller.php";

$recentPosts = [];
if ($profileUser["role"] === "buyer") {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE buyer_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$id]);
    $recentPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($profileUser["username"]) ?>'s Profile</title>
  <link rel="stylesheet" href="Styles/DFNP.css">
  <link rel="stylesheet" href="Styles/profile.css">
</head>
<body class="wide">
<div class="page">
  <?php include "header.php"; ?>

  <a href="<?= $backLink ?>">&larr; Back</a>

  <div class="profile-card profile-view">
    <div class="profile-header">
      <?php render_avatar($profileUser["username"], $profileUser["avatar"], 76); ?>
      <div>
        <h2><?= htmlspecialchars($profileUser["username"]) ?></h2>
        <span class="nav-role"><?= htmlspecialchars(ucfirst($profileUser["role"])) ?></span>
      </div>
    </div>

    <?php if (!empty($profileUser["bio"])): ?>
      <p class="profile-bio"><?= nl2br(htmlspecialchars($profileUser["bio"])) ?></p>
    <?php else: ?>
      <p class="profile-bio muted"><em>No bio yet.</em></p>
    <?php endif; ?>

    <div class="profile-meta">
      <?php if (!empty($profileUser["location"])): ?>
        <span>📍 <?= htmlspecialchars($profileUser["location"]) ?></span>
      <?php endif; ?>
      <?php if (!empty($profileUser["phone"])): ?>
        <span>📞 <?= htmlspecialchars($profileUser["phone"]) ?></span>
      <?php endif; ?>
    </div>

    <?php if ($isSelf): ?>
      <a class="btn-small" href="profile.php">Edit my profile</a>
    <?php endif; ?>
  </div>

  <?php if ($profileUser["role"] === "buyer"): ?>
    <div class="section-head">
      <h2>Recent requests</h2>
      <span class="count-pill"><?= count($recentPosts) ?></span>
    </div>
    <?php if (!$recentPosts): ?>
      <p><em>No requests posted yet.</em></p>
    <?php endif; ?>
    <?php foreach ($recentPosts as $post): ?>
      <div class="post-card">
        <h3><?= htmlspecialchars($post["title"]) ?></h3>
        <p><?= nl2br(htmlspecialchars($post["description"])) ?></p>
        <small class="post-date"><?= date("M j, Y", strtotime($post["created_at"])) ?></small>
        <?php if (!$isSelf): ?>
          <div class="post-actions">
            <a class="btn-small" href="chat.php?post_id=<?= $post['id'] ?>&with=<?= $profileUser['id'] ?>">Message</a>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
</body>
</html>
