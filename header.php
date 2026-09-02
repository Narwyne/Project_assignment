<div class="nav">
  <div class="nav-brand">
    <span class="nav-logo">🛍️</span>
    <span class="nav-title">MarketConnect</span>
  </div>
  <div class="nav-user">
    <span class="nav-hello">Hi, <?= htmlspecialchars($_SESSION["username"]) ?></span>
    <span class="nav-role"><?= htmlspecialchars(ucfirst($_SESSION["role"])) ?></span>
    <a href="logout.php" class="nav-logout">Logout</a>
  </div>
</div>
