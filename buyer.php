<?php
session_start();
require "config.php";
if (($_SESSION["role"] ?? "") !== "buyer") { header("Location: login.php"); exit; }
$buyer_id = $_SESSION["user_id"];

// create post
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "add") === "add") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category_id = (int)($_POST["category_id"] ?? 0) ?: null;
    if ($title && $description) {
        $stmt = $pdo->prepare("INSERT INTO posts (buyer_id, title, description, category_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$buyer_id, $title, $description, $category_id]);
        header("Location: buyer.php");
        exit;
    }
}

// edit post
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "edit") {
    $id = (int)$_POST["id"];
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category_id = (int)($_POST["category_id"] ?? 0) ?: null;
    if ($title && $description) {
        $stmt = $pdo->prepare("UPDATE posts SET title=?, description=?, category_id=? WHERE id=? AND buyer_id=?");
        $stmt->execute([$title, $description, $category_id, $id, $buyer_id]);
    }
    header("Location: buyer.php");
    exit;
}

// delete post
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete") {
    $id = (int)$_POST["id"];
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id=? AND buyer_id=?");
    $stmt->execute([$id, $buyer_id]);
    header("Location: buyer.php");
    exit;
}

// search/filter own posts
$q = trim($_GET["q"] ?? "");
$editId = (int)($_GET["edit"] ?? 0);

if ($q !== "") {
    $posts = $pdo->prepare("SELECT p.*, c.name AS category_name FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE p.buyer_id=? AND (p.title LIKE ? OR p.description LIKE ?) ORDER BY p.created_at DESC");
    $like = "%$q%";
    $posts->execute([$buyer_id, $like, $like]);
} else {
    $posts = $pdo->prepare("SELECT p.*, c.name AS category_name FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE p.buyer_id=? ORDER BY p.created_at DESC");
    $posts->execute([$buyer_id]);
}
$posts = $posts->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$repliesStmt = $pdo->prepare("
  SELECT DISTINCT m.sender_id, u.username FROM messages m
  JOIN users u ON u.id = m.sender_id
  WHERE m.post_id = ? AND m.receiver_id = ?
");
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Buyer Dashboard</title>
  <link rel="stylesheet" href="Styles/DFNP.css">
</head>
<body class="wide">
<div class="page">
  <?php include "header.php"; ?>

  <h2>Post What You're Looking For</h2>
  <form method="POST" class="post-form">
    <input type="hidden" name="action" value="add">
    <input name="title" placeholder="e.g. Looking for iPhone 13, budget 15k" required>
    <select name="category_id">
      <option value="">No category</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <textarea name="description" placeholder="Add more details..." required></textarea>
    <button type="submit">Post Request</button>
  </form>

  <h2>My Requests</h2>
  <form method="GET" class="search-bar">
    <input type="text" name="q" placeholder="Search my requests..." value="<?= htmlspecialchars($q) ?>">
    <button type="submit">Search</button>
    <?php if ($q !== ""): ?><a href="buyer.php" class="btn-small">Clear</a><?php endif; ?>
  </form>

  <?php if (!$posts): ?>
    <p><?= $q !== "" ? "No matching requests." : "You haven't posted anything yet." ?></p>
  <?php endif; ?>

  <?php foreach ($posts as $post): ?>
    <div class="post-card">
      <?php if ($editId === (int)$post["id"]): ?>
        <form method="POST">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="id" value="<?= $post['id'] ?>">
          <input name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
          <select name="category_id">
            <option value="">No category</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>" <?= (int)$post['category_id'] === (int)$c['id'] ? "selected" : "" ?>><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <textarea name="description" required><?= htmlspecialchars($post['description']) ?></textarea>
          <button type="submit">Save</button>
          <a href="buyer.php" class="btn-small">Cancel</a>
        </form>
      <?php else: ?>
        <?php if ($post['category_name']): ?><span class="tag"><?= htmlspecialchars($post['category_name']) ?></span><?php endif; ?>
        <h3><?= htmlspecialchars($post["title"]) ?></h3>
        <p><?= nl2br(htmlspecialchars($post["description"])) ?></p>
        <small><?= $post["created_at"] ?></small>

        <div class="post-actions">
          <a class="btn-small" href="buyer.php?edit=<?= $post['id'] ?>">Edit</a>
          <form method="POST" onsubmit="return confirm('Delete this request?');" style="display:inline">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $post['id'] ?>">
            <button type="submit" class="btn-danger">Delete</button>
          </form>
        </div>

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
                  <a href="view_profile.php?id=<?= $s['sender_id'] ?>"><?= htmlspecialchars($s["username"]) ?></a>
                  <a class="btn-small" href="chat.php?post_id=<?= $post['id'] ?>&with=<?= $s['sender_id'] ?>">Reply</a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <em>No responses yet.</em>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
</body>
</html>
