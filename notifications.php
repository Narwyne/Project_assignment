<?php
session_start();
require "config.php";
require "notifications_helper.php";
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit; }

$my_id = $_SESSION["user_id"];
$notifications = get_recent_notifications($pdo, $my_id, 50);
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Notifications</title>
  <link rel="stylesheet" href="Styles/DFNP.css">
</head>
<body class="wide">
<div class="page">
  <?php include "header.php"; ?>

  <h2>Notifications</h2>

  <?php if (!$notifications): ?>
    <p><em>No notifications yet.</em></p>
  <?php endif; ?>

  <?php foreach ($notifications as $n): ?>
    <a class="post-card notif-row <?= $n['is_read'] ? '' : 'unread' ?>"
       href="chat.php?post_id=<?= $n['post_id'] ?>&with=<?= $n['from_user_id'] ?>">
      <span class="notif-msg"><?= htmlspecialchars($n["message"]) ?></span>
      <small class="notif-time"><?= time_ago($n["created_at"]) ?></small>
    </a>
  <?php endforeach; ?>
</div>
</body>
</html>