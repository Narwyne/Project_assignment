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

$unreadCount = 0;
$notifications = [];
if ($role !== "admin" && isset($pdo)) {
    $unreadCount = get_unread_count($pdo, $_SESSION["user_id"]);
    $notifications = get_recent_notifications($pdo, $_SESSION["user_id"]);
}
?>
<div class="nav">
  <div class="nav-brand">
    <span class="nav-logo">🛍️</span>
    <span class="nav-title">MarketConnect</span>
    <div class="nav-links">
      <?php if ($role === "admin"): ?>
        <a href="admin.php" class="<?= $currentPage === "admin.php" ? "active" : "" ?>">Categories</a>
      <?php else: ?>
        <?php $dashboardPage = $role === "buyer" ? "buyer.php" : "seller.php"; ?>
        <a href="<?= $dashboardPage ?>" class="<?= $currentPage === $dashboardPage ? "active" : "" ?>">Dashboard</a>
        <a href="feed.php" class="<?= $currentPage === "feed.php" ? "active" : "" ?>">Feed</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="nav-user">
    <?php if ($role !== "admin"): ?>
      <div class="notif-wrap">
        <button type="button" class="notif-bell" onclick="toggleNotifDropdown(event)">
          🔔
          <?php if ($unreadCount > 0): ?><span class="notif-badge"><?= $unreadCount > 9 ? "9+" : $unreadCount ?></span><?php endif; ?>
        </button>
        <div class="notif-dropdown" id="notifDropdown">
          <?php if (!$notifications): ?>
            <div class="notif-empty">No notifications yet.</div>
          <?php else: ?>
            <?php foreach ($notifications as $n): ?>
              <a class="notif-item <?= $n["is_read"] ? "" : "unread" ?>" href="chat.php?post_id=<?= $n['post_id'] ?>&with=<?= $n['from_user_id'] ?>">
                <?php render_avatar($n["from_username"], null, 28); ?>
                <div class="notif-text">
                  <span><?= htmlspecialchars($n["message"]) ?></span>
                  <small><?= time_ago($n["created_at"]) ?></small>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
    <a href="profile.php" class="nav-profile-link">
      <?php render_avatar($_SESSION["username"], $navAvatar, 30); ?>
      <span class="nav-hello">Hi, <?= htmlspecialchars($_SESSION["username"]) ?></span>
    </a>
    <span class="nav-role"><?= htmlspecialchars(ucfirst($_SESSION["role"])) ?></span>
    <a href="logout.php" class="nav-logout">Logout</a>
  </div>
</div>
<script>
function toggleNotifDropdown(e) {
  e.stopPropagation();
  document.getElementById('notifDropdown').classList.toggle('open');
}
document.addEventListener('click', function (e) {
  var dd = document.getElementById('notifDropdown');
  if (dd && !e.target.closest('.notif-wrap')) dd.classList.remove('open');
});
</script>
