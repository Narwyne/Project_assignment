<?php
session_start();
require "config.php";
require "notifications_helper.php";
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit; }

$my_id   = $_SESSION["user_id"];
$role    = $_SESSION["role"];
$post_id = (int)($_GET["post_id"] ?? 0);
$with    = (int)($_GET["with"] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) { die("Post not found."); }

if ($role === "buyer" && $post["buyer_id"] != $my_id) {
    die("Not authorized.");
}
if ($role === "seller") {
    $with = $post["buyer_id"];
}

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$with]);
$otherUser = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$otherUser) { die("User not found."); }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $msg = trim($_POST["message"] ?? "");
    if ($msg) {
        $stmt = $pdo->prepare("INSERT INTO messages (post_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$post_id, $my_id, $with, $msg]);
        create_notification(
            $pdo, $with, $my_id, $post_id,
            $_SESSION["username"] . " commented on \"" . $post["title"] . "\""
        );
        header("Location: chat.php?post_id=$post_id&with=$with");
        exit;
    }
}

// opening this thread clears any unread notifications for it
mark_thread_read($pdo, $my_id, $with, $post_id);

$stmt = $pdo->prepare("
  SELECT * FROM messages
  WHERE post_id = ? AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
  ORDER BY created_at ASC
");
$stmt->execute([$post_id, $my_id, $with, $with, $my_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$backLink = $role === "buyer" ? "buyer.php" : "seller.php";
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Chat</title>
  <link rel="stylesheet" href="Styles/DFNP.css">
  <link rel="stylesheet" href="Styles/chat.css">
</head>
<body class="wide">
<div class="page">
  <?php include "header.php"; ?>

  <a href="<?= $backLink ?>">&larr; Back</a>
  <h2><?= htmlspecialchars($post["title"]) ?></h2>
  <p>Chat with <a href="view_profile.php?id=<?= $with ?>"><strong><?= htmlspecialchars($otherUser["username"]) ?></strong></a></p>

  <div class="chat-box">
    <?php foreach ($messages as $m): ?>
      <div class="msg <?= $m["sender_id"] == $my_id ? "me" : "other" ?>">
        <?= htmlspecialchars($m["message"]) ?>
        <small><?= $m["created_at"] ?></small>
      </div>
    <?php endforeach; ?>
    <?php if (!$messages): ?><p><em>No messages yet. Say hello!</em></p><?php endif; ?>
  </div>

  <form method="POST" class="chat-form">
    <input name="message" placeholder="Type a message..." required autocomplete="off">
    <button type="submit">Send</button>
  </form>
</div>
</body>
</html>
