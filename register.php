<?php
require "config.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $role = $_POST["role"] ?? "";

    if (!$username || !$password || !in_array($role, ["buyer", "seller"])) {
        $error = "Please fill all fields and choose a role.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Username already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hash, $role]);
            header("Location: login.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
  <h2>Register</h2>
  <?php if ($error) echo "<p class='error'>$error</p>"; ?>
  <form method="POST">
    <input name="username" placeholder="Username" required>
    <input name="password" type="password" placeholder="Password" required>
    <div class="roles">
      <label><input type="radio" name="role" value="buyer" required> Buyer</label>
      <label><input type="radio" name="role" value="seller"> Seller</label>
    </div>
    <button type="submit">Register</button>
  </form>
  <a href="login.php">Already have an account? Login</a>
</div>
</body>
</html>