<?php
require_once __DIR__ . "/avatar_helper.php";
require_once __DIR__ . "/notifications_helper.php";
$navAvatar = $_SESSION["avatar"] ?? null;
if ($navAvatar === null && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $navAvatar = $stmt->fetchColumn() ?: "";
    $_SESSION["avatar"] = $navAvatar;
}
$role = $_SESSION["role"];
$currentPage = basename($_SERVER["PHP_SELF"]);
$unreadCount = isset($pdo) ? get_unread_count($pdo, $_SESSION["user_id"]) : 0;

function nav_icon($name) {
    $paths = [
        "home"   => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9"/>',
        "grid"   => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        "bell"   => '<path d="M6 9a6 6 0 1 1 12 0c0 4 1.5 5.5 1.5 5.5H4.5S6 13 6 9Z"/><path d="M10 18.5a2 2 0 0 0 4 0"/>',
        "user"   => '<circle cx="12" cy="8" r="3.5"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/>',
        "folder" => '<path d="M3 6.5A1.5 1.5 0 0 1 4.5 5h4l2 2h9A1.5 1.5 0 0 1 21 8.5v9A1.5 1.5 0 0 1 19.5 19h-15A1.5 1.5 0 0 1 3 17.5Z"/>',
        "logout" => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
    ];
    return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? '') . '</svg>';
}
?>
<link rel="stylesheet" href="Styles/header.css">
<div class="nav">
  <div class="nav-brand">
    <span class="nav-logo">🛍️</span>
    <span class="nav-title">HanapMo</span>
  </div>
  <div class="nav-user">
    <span class="nav-hello">Hi, <?= htmlspecialchars($_SESSION["username"] ?? "") ?></span>
    <span class="nav-role"><?= htmlspecialchars(ucfirst($role)) ?></span>
    <a href="profile.php" class="nav-avatar-link"><?php render_avatar($_SESSION["username"] ?? "?", $navAvatar, 32); ?></a>
    <a href="logout.php" class="nav-icon-btn" title="Log out"><?= nav_icon("logout") ?></a>
  </div>
</div>
<div class="nav-secondary">
  <?php if ($role === "admin"): ?>
    <a href="admin.php" class="<?= $currentPage === "admin.php" ? "active" : "" ?>"><?= nav_icon("folder") ?> Categories</a>
    <a href="profile.php" class="<?= $currentPage === "profile.php" ? "active" : "" ?>"><?= nav_icon("user") ?> Profile</a>
  <?php else: ?>
    <?php $dashboardPage = $role === "buyer" ? "buyer.php" : "seller.php"; ?>
    <a href="<?= $dashboardPage ?>" class="<?= $currentPage === $dashboardPage ? "active" : "" ?>"><?= nav_icon("home") ?> Dashboard</a>
    <a href="feed.php" class="<?= $currentPage === "feed.php" ? "active" : "" ?>"><?= nav_icon("grid") ?> Feed</a>
    <a href="notifications.php" class="<?= $currentPage === "notifications.php" ? "active" : "" ?>">
      <?= nav_icon("bell") ?> Notifications<?php if ($unreadCount > 0): ?><span class="nav-badge"><?= $unreadCount > 9 ? "9+" : $unreadCount ?></span><?php endif; ?>
    </a>
    <a href="profile.php" class="<?= $currentPage === "profile.php" ? "active" : "" ?>"><?= nav_icon("user") ?> Profile</a>
  <?php endif; ?>
</div>
