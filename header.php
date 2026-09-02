<?php
require_once __DIR__ . "/avatar_helper.php";
$navAvatar = $_SESSION["avatar"] ?? null;
if ($navAvatar === null && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $navAvatar = $stmt->fetchColumn() ?: "";
    $_SESSION["avatar"] = $navAvatar;
}
$dashboardPage = $_SESSION["role"] === "buyer" ? "buyer.php" : "seller.php";
$currentPage = basename($_SERVER["PHP_SELF"]);
?>
<div class="nav">
  <div class="nav-brand">
    <span class="nav-logo">🛍️</span>
    <span class="nav-title">MarketConnect</span>
    <div class="nav-links">
      <a href="<?= $dashboardPage ?>" class="<?= $currentPage === $dashboardPage ? "active" : "" ?>">Dashboard</a>
      <a href="feed.php" class="<?= $currentPage === "feed.php" ? "active" : "" ?>">Feed</a>
    </div>
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
