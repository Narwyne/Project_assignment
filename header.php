<?php
require_once __DIR__ . "/avatar_helper.php";
$navAvatar = $_SESSION["avatar"] ?? null;
if ($navAvatar === null && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $navAvatar = $stmt->fetchColumn() ?: "";
    $_SESSION["avatar"] = $navAvatar;
}
$role = $_SESSION["role"];
$currentPage = basename($_SERVER["PHP_SELF"]);
?>
<link rel="stylesheet" href="Styles/header.css">
<div class="nav">
  <div class="nav-brand">
    <span class="nav-logo">🛍️</span>
    <span class="nav-title">HanapMo</span>
  </div>
  <div class="nav-user">
    <span class="nav-role"><?= htmlspecialchars(ucfirst($_SESSION["role"])) ?></span>
    <a href="logout.php" class="nav-logout">Logout</a>
  </div>
</div>
<div class="nav-secondary">
  <?php if ($role === "admin"): ?>
    <a href="admin.php" class="<?= $currentPage === "admin.php" ? "active" : "" ?>">📂 Categories</a>
    <a href="profile.php" class="<?= $currentPage === "profile.php" ? "active" : "" ?>">👤 Profile</a>
  <?php else: ?>
    <?php $dashboardPage = $role === "buyer" ? "buyer.php" : "seller.php"; ?>
    <a href="<?= $dashboardPage ?>" class="<?= $currentPage === $dashboardPage ? "active" : "" ?>">🏠 Dashboard</a>
    <a href="feed.php" class="<?= $currentPage === "feed.php" ? "active" : "" ?>">📰 Feed</a>
    <a href="notifications.php" class="<?= $currentPage === "notifications.php" ? "active" : "" ?>">🔔 Notifications</a>
    <a href="profile.php" class="<?= $currentPage === "profile.php" ? "active" : "" ?>">👤 Profile</a>
  <?php endif; ?>
</div>
