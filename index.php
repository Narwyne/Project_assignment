<?php
session_start();
if (isset($_SESSION["user_id"])) {
    $dest = $_SESSION["role"] === "buyer" ? "buyer.php" : ($_SESSION["role"] === "seller" ? "seller.php" : "admin.php");
    header("Location: $dest");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HanapMo — Post what you need. Get offers fast.</title>
  <link rel="stylesheet" href="Styles/landing.css">
</head>
<body>

  <div class="lp-nav">
    <div class="lp-brand"><span class="logo">🛍️</span> HanapMo</div>
    <div class="lp-nav-actions">
      <a class="lp-btn-ghost" href="login.php">Log in</a>
      <a class="lp-btn-solid" href="register.php">Sign up</a>
    </div>
  </div>

  <div class="lp-hero">
    <h1>Post what you're looking for. Let sellers come to you.</h1>
    <p>HanapMo flips the usual marketplace around — buyers post a request, sellers reply with offers, and you chat right on the platform to close the deal.</p>
    <div class="lp-hero-actions">
      <a class="lp-btn-lg solid" href="register.php">Get started free</a>
      <a class="lp-btn-lg outline" href="login.php">I already have an account</a>
    </div>
  </div>

  <div class="lp-preview">
    <div class="lp-preview-card">
      <div class="lp-fake-post">
        <div class="lp-fake-avatar">B</div>
        <div>
          <div class="lp-fake-title">Looking for iPhone 13, budget 7–10k</div>
          <div class="lp-fake-sub">Posted by buyer · 2m ago</div>
        </div>
        <span class="lp-fake-tag">Electronics</span>
      </div>
      <div class="lp-fake-post">
        <div class="lp-fake-avatar" style="background:#1a1a2e;">S</div>
        <div>
          <div class="lp-fake-title">Looking for a road bike</div>
          <div class="lp-fake-sub">3 sellers replied</div>
        </div>
        <span class="lp-fake-tag">Vehicles</span>
      </div>
    </div>
  </div>

  <div class="lp-section">
    <h2>How it works</h2>
    <div class="lp-steps">
      <div class="lp-step">
        <span class="icon">📝</span>
        <h3>Post a request</h3>
        <p>Buyers describe what they want and set a budget — no need to hunt through listings.</p>
      </div>
      <div class="lp-step">
        <span class="icon">🙋</span>
        <h3>Sellers respond</h3>
        <p>Sellers browse open requests by category and reach out to buyers who need what they have.</p>
      </div>
      <div class="lp-step">
        <span class="icon">💬</span>
        <h3>Chat and close</h3>
        <p>Message directly in-app, agree on price, and get notified the moment someone replies.</p>
      </div>
    </div>
  </div>

  <div class="lp-section">
    <h2>Built for both sides</h2>
    <div class="lp-roles">
      <div class="lp-role-card buyer">
        <span class="icon">🛒</span>
        <h3>Buying something?</h3>
        <p>Post what you need once. Compare replies from interested sellers and pick the best offer.</p>
        <a href="register.php">Post a request</a>
      </div>
      <div class="lp-role-card seller">
        <span class="icon">🏷️</span>
        <h3>Selling something?</h3>
        <p>Skip the guesswork. Browse real buyer requests by category and message people who are ready to buy.</p>
        <a href="register.php">Start selling</a>
      </div>
    </div>
  </div>

  <div class="lp-footer">
    © <?= date("Y") ?> HanapMo. Made for buyers and sellers who'd rather skip the small talk.
  </div>

</body>
</html>