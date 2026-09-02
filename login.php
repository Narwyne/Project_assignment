<?php
session_start();
require "config.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["role"] = $user["role"];
        $_SESSION["username"] = $user["username"];
        header("Location: " . ($user["role"] === "buyer" ? "buyer.php" : "seller.php"));
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="Styles/login.css">
</head>
<body>
<div class="card">
  <div class="brand">🛍️</div>
  <h2>Welcome back</h2>
  <p class="subtitle">Log in to your account</p>
  <?php if ($error) echo "<p class='error'>$error</p>"; ?>
  <form method="POST">
    <input name="username" placeholder="Username" required autofocus>
    <input name="password" type="password" placeholder="Password" required>
    <button type="submit">Login</button>
  </form>
  <a href="register.php">No account? Register</a>
</div>
</body>
</html>
