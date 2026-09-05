<?php
session_start();
require "config.php";
if (($_SESSION["role"] ?? "") !== "admin") { header("Location: login.php"); exit; }

$error = "";

// add category
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "add") {
    $name = trim($_POST["name"] ?? "");
    if ($name === "") {
        $error = "Category name can't be empty.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $error = "That category already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            header("Location: admin.php");
            exit;
        }
    }
}

// rename category
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "rename") {
    $id = (int)$_POST["id"];
    $name = trim($_POST["name"] ?? "");
    if ($name !== "") {
        $stmt = $pdo->prepare("UPDATE categories SET name=? WHERE id=?");
        $stmt->execute([$name, $id]);
    }
    header("Location: admin.php");
    exit;
}

// delete category
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete") {
    $id = (int)$_POST["id"];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id=?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}

$categories = $pdo->query("
  SELECT c.*, (SELECT COUNT(*) FROM posts p WHERE p.category_id = c.id) AS post_count
  FROM categories c ORDER BY c.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$editId = (int)($_GET["edit"] ?? 0);
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin · Categories</title>
  <link rel="stylesheet" href="Styles/DFNP.css">
  <link rel="stylesheet" href="Styles/admin.css">
</head>
<body class="wide">
<div class="page">
  <?php include "header.php"; ?>

  <div class="section-head">
    <h2>Manage categories</h2>
    <span class="count-pill"><?= count($categories) ?></span>
  </div>

  <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

  <form method="POST" class="post-form inline-form">
    <input type="hidden" name="action" value="add">
    <input name="name" placeholder="New category name" required>
    <button type="submit" class="btn-cta">Add</button>
  </form>

  <?php if (!$categories): ?>
    <p><em>No categories yet.</em></p>
  <?php endif; ?>

  <?php foreach ($categories as $cat): ?>
    <div class="post-card">
      <?php if ($editId === (int)$cat["id"]): ?>
        <form method="POST" class="inline-edit">
          <input type="hidden" name="action" value="rename">
          <input type="hidden" name="id" value="<?= $cat['id'] ?>">
          <input name="name" value="<?= htmlspecialchars($cat['name']) ?>" required>
          <button type="submit" class="btn-small">Save</button>
          <a href="admin.php" class="btn-small btn-muted">Cancel</a>
        </form>
      <?php else: ?>
        <div class="cat-row">
          <div>
            <strong><?= htmlspecialchars($cat["name"]) ?></strong>
            <span class="count-pill"><?= (int)$cat["post_count"] ?> post<?= $cat["post_count"] == 1 ? "" : "s" ?></span>
          </div>
          <div class="post-actions">
            <a class="btn-small" href="admin.php?edit=<?= $cat['id'] ?>">Rename</a>
            <form method="POST" onsubmit="return confirm('Delete this category? Posts using it will become uncategorized.');" style="display:inline">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $cat['id'] ?>">
              <button type="submit" class="btn-danger">Delete</button>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
</body>
</html>
