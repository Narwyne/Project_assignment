<?php
// Create a notification for $user_id, triggered by $from_user_id, about $post_id.
function create_notification($pdo, $user_id, $from_user_id, $post_id, $message) {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, from_user_id, post_id, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $from_user_id, $post_id, $message]);
}

function get_unread_count($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}

// Recent notifications for the bell dropdown, newest first.
function get_recent_notifications($pdo, $user_id, $limit = 8) {
    $stmt = $pdo->prepare("
      SELECT n.*, u.username AS from_username, p.title AS post_title
      FROM notifications n
      JOIN users u ON u.id = n.from_user_id
      JOIN posts p ON p.id = n.post_id
      WHERE n.user_id = ?
      ORDER BY n.created_at DESC
      LIMIT " . (int)$limit . "
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mark notifications from a specific thread (post + sender) as read for $user_id.
// Call this whenever the user opens that chat thread.
function mark_thread_read($pdo, $user_id, $from_user_id, $post_id) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND from_user_id = ? AND post_id = ?");
    $stmt->execute([$user_id, $from_user_id, $post_id]);
}

function time_ago($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return "just now";
    if ($diff < 3600) return floor($diff / 60) . "m ago";
    if ($diff < 86400) return floor($diff / 3600) . "h ago";
    if ($diff < 604800) return floor($diff / 86400) . "d ago";
    return date("M j", strtotime($datetime));
}
