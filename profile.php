<?php
session_start();
require "config.php";
require "avatar_helper.php";
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit; }
$user_id = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bio = trim($_POST["bio"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $avatarPath = $user["avatar"];

    if (!empty($_FILES["avatar"]["name"])) {
        $file = $_FILES["avatar"];
        $allowed = ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            $error = "Upload failed. Please try again.";
        } elseif ($file["size"] > 2 * 1024 * 1024) {
            $error = "Image must be under 2MB.";
        } else {
            $info = @getimagesize($file["tmp_name"]);
            $mime = $info["mime"] ?? "";
            if (!$info || !isset($allowed[$mime])) {
                $error = "Please upload a JPG, PNG, or WEBP image.";
            } else {
                $ext = $allowed[$mime];
                $dir = __DIR__ . "/uploads/avatars";
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $filename = "u{$user_id}_" . bin2hex(random_bytes(6)) . "." . $ext;
                if (move_uploaded_file($file["tmp_name"], "$dir/$filename")) {
                    if ($avatarPath && file_exists(__DIR__ . "/" . $avatarPath)) {
                        @unlink(__DIR__ . "/" . $avatarPath);
                    }
                    $avatarPath = "uploads/avatars/$filename";
                } else {
                    $error = "Could not save the image.";
                }
            }
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("UPDATE users SET bio=?, phone=?, location=?, avatar=? WHERE id=?");
        $stmt->execute([$bio, $phone, $location, $avatarPath, $user_id]);
        $_SESSION["avatar"] = $avatarPath;
        header("Location: profile.php?saved=1");
        exit;
    }
}

$backLink = $_SESSION["role"] === "buyer" ? "buyer.php" : "seller.php";
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile</title>
  <link rel="stylesheet" href="Styles/DFNP.css">
  <link rel="stylesheet" href="Styles/profile.css">
</head>
<body class="wide">
<div class="page">
  <?php include "header.php"; ?>

  <h2>My profile</h2>

  <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
  <?php if (isset($_GET["saved"])): ?><p class="success">Profile updated.</p><?php endif; ?>

  <div class="profile-card">
    <form method="POST" enctype="multipart/form-data">
      <div class="avatar-row">
        <?php render_avatar($user["username"], $user["avatar"], 84); ?>
        <div class="avatar-upload">
          <label class="btn-small file-label">
            Change photo
            <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp" hidden>
          </label>
          <span class="hint">JPG, PNG or WEBP, max 2MB</span>
        </div>
      </div>

      <div class="profile-static-row">
        <div>
          <span class="static-label">Username</span>
          <span class="static-value"><?= htmlspecialchars($user["username"]) ?></span>
        </div>
        <div>
          <span class="static-label">Role</span>
          <span class="nav-role"><?= htmlspecialchars(ucfirst($user["role"])) ?></span>
        </div>
      </div>

      <label>Bio</label>
      <textarea name="bio" placeholder="Tell people a bit about yourself..."><?= htmlspecialchars($user["bio"] ?? "") ?></textarea>

      <div class="profile-grid">
        <div>
          <label>Phone</label>
          <input name="phone" placeholder="e.g. 0917 123 4567" value="<?= htmlspecialchars($user["phone"] ?? "") ?>">
        </div>
        <div>
          <label>Location</label>
          <input name="location" placeholder="e.g. Lapu-Lapu City" value="<?= htmlspecialchars($user["location"] ?? "") ?>">
        </div>
      </div>

      <button type="submit" class="btn-cta btn-cta-block">Save changes</button>
    </form>
  </div>

  <a class="btn-small btn-muted" href="view_profile.php?id=<?= $user_id ?>">Preview my public profile</a>
</div>
<script>
  document.querySelector('input[type="file"]').addEventListener('change', function () {
    if (this.files[0]) this.closest('form').submit();
  });
</script>
</body>
</html>
