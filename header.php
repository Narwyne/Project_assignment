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
<div class="nav">
  <div class="nav-brand">
    <span class="nav-logo">🛍️</span>
    <span class="nav-title">HanapMo</span>
  </div>
  <div class="nav-user">
    <a href="profile.php" class="nav-profile-link">
      <?php render_avatar($_SESSION["username"], $navAvatar, 30); ?>
      <span class="nav-hello">Hi, <?= htmlspecialchars($_SESSION["username"]) ?></span>
    </a>
    <span class="nav-role"><?= htmlspecialchars(ucfirst($_SESSION["role"])) ?></span>
    <a href="logout.php" class="nav-logout">Logout</a>
  </div>
</div>
<div class="nav-secondary">
  <?php if ($role === "admin"): ?>
    <a href="admin.php" class="<?= $currentPage === "admin.php" ? "active" : "" ?>">📂 Categories</a>
  <?php else: ?>
    <?php $dashboardPage = $role === "buyer" ? "buyer.php" : "seller.php"; ?>
    <a href="<?= $dashboardPage ?>" class="<?= $currentPage === $dashboardPage ? "active" : "" ?>">🏠 Dashboard</a>
    <a href="feed.php" class="<?= $currentPage === "feed.php" ? "active" : "" ?>">📰 Feed</a>
  <?php endif; ?>
</div>
