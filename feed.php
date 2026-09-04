<?php
session_start();
require "config.php";
require "avatar_helper.php";
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit; }
$my_id = $_SESSION["user_id"];
$role  = $_SESSION["role"];

$q = trim($_GET["q"] ?? "");
$category_id = (int)($_GET["category_id"] ?? 0);

$where = [];
$params = [];
if ($q !== "") {
    $where[] = "(p.title LIKE ? OR p.description LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($category_id) {
    $where[] = "p.category_id = ?";
    $params[] = $category_id;
}
$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $pdo->prepare("
  SELECT p.*, u.username AS buyer_name, u.avatar AS buyer_avatar, c.name AS category_name
  FROM posts p
  JOIN users u ON u.id = p.buyer_id
  LEFT JOIN categories c ON c.id = p.category_id
  $whereSql
  ORDER BY p.created_at DESC
");
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$dashboardLink = $role === "buyer" ? "buyer.php" : "seller.php";
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Feed</title>
  <link rel="stylesheet" href="Styles/DFNP.css">
</head>
<body class="wide">
<div class="page">
  <?php include "header.php"; ?>

  <h2>All Requests</h2>
  <form method="GET" class="search-bar">
    <input type="text" name="q" placeholder="Search all requests..." value="<?= htmlspecialchars($q) ?>">
    <select name="category_id" onchange="this.form.submit()">
      <option value="">All categories</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $category_id === (int)$c['id'] ? "selected" : "" ?>><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Search</button>
    <?php if ($q !== "" || $category_id): ?><a href="feed.php" class="btn-small">Clear</a><?php endif; ?>
  </form>

  <?php if (!$posts): ?>
    <p><?= $q !== "" ? "No matching requests." : "No requests posted yet." ?></p>
  <?php endif; ?>

  <?php foreach ($posts as $post): ?>
    <?php $isMine = $role === "buyer" && (int)$post["buyer_id"] === (int)$my_id; ?>
    <div class="post-card">
      <?php if ($isMine): ?><span class="badge-mine">Your post</span><?php endif; ?>

      <a class="post-author" href="view_profile.php?id=<?= $post['buyer_id'] ?>">
        <?php render_avatar($post["buyer_name"], $post["buyer_avatar"], 32); ?>
        <div>
          <span class="post-author-name"><?= htmlspecialchars($post["buyer_name"]) ?></span>
        </div>
      </a>
      <span class="post-date"><?= $post["created_at"] ?></span>

      <?php if ($post['category_name']): ?><span class="tag"><?= htmlspecialchars($post['category_name']) ?></span><?php endif; ?>
      <h3><?= htmlspecialchars($post["title"]) ?></h3>
      <p><?= nl2br(htmlspecialchars($post["description"])) ?></p>

      <div class="post-actions">
        <?php if ($role === "seller"): ?>
          <a class="btn-small" href="chat.php?post_id=<?= $post['id'] ?>&with=<?= $post['buyer_id'] ?>">Message Buyer</a>
        <?php elseif ($isMine): ?>
          <a class="btn-small" href="buyer.php?edit=<?= $post['id'] ?>">Edit</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
</body>
</html>