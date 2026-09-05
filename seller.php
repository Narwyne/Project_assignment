<?php
session_start();
require "config.php";
require "avatar_helper.php";
if (($_SESSION["role"] ?? "") !== "seller") { header("Location: login.php"); exit; }
$seller_id = $_SESSION["user_id"];

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
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Seller Feed</title>
  <link rel="stylesheet" href="Styles/DFNP.css">
</head>
<body class="wide">
<div class="page">
  <?php include "header.php"; ?>

  <div class="section-head">
    <h2>Buyer requests</h2>
    <span class="count-pill"><?= count($posts) ?></span>
  </div>

  <form method="GET" class="search-bar">
    <input type="text" name="q" placeholder="Search requests..." value="<?= htmlspecialchars($q) ?>">
    <select name="category_id" onchange="this.form.submit()">
      <option value="">All categories</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $category_id === (int)$c['id'] ? "selected" : "" ?>><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Search</button>
    <?php if ($q !== "" || $category_id): ?><a href="seller.php" class="btn-small btn-muted">Clear</a><?php endif; ?>
  </form>

  <?php if (!$posts): ?>
    <p><?= $q !== "" ? "No matching requests." : "No requests posted yet." ?></p>
  <?php endif; ?>

  <?php foreach ($posts as $post): ?>
    <div class="post-card">
      <div class="post-card-top">
        <a class="post-author" href="view_profile.php?id=<?= $post['buyer_id'] ?>">
          <?php render_avatar($post["buyer_name"], $post["buyer_avatar"], 32); ?>
          <span class="post-author-name"><?= htmlspecialchars($post["buyer_name"]) ?></span>
        </a>
        <span class="post-date"><?= date("M j", strtotime($post["created_at"])) ?></span>
      </div>

      <?php if ($post['category_name']): $catClass = 'tag-c' . (((int)$post['category_id']) % 5); ?>
        <span class="tag <?= $catClass ?>"><?= htmlspecialchars($post['category_name']) ?></span>
      <?php endif; ?>
      <h3><?= htmlspecialchars($post["title"]) ?></h3>
      <p><?= nl2br(htmlspecialchars($post["description"])) ?></p>
      <a class="btn-small" href="chat.php?post_id=<?= $post['id'] ?>&with=<?= $post['buyer_id'] ?>">Message buyer</a>
    </div>
  <?php endforeach; ?>
</div>
</body>
</html>
