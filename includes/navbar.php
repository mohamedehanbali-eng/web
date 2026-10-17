<nav id="navbar">
  <div class="nav-container">
    <a href="index.php" class="nav-logo">AURUM</a>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="index.php#catalog">Collection</a></li>
      <li><a href="contact.php">Contact</a></li>
      <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_admin']): ?>
        <li><a href="admin.php">Admin</a></li>
      <?php endif; ?>
    </ul>
    <div class="nav-actions">
      <?php if (isset($_SESSION['user'])): ?>
        <span class="nav-user-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
        <a href="profile.php" class="btn-ghost">Profile</a>
        <a href="cart.php" class="btn-ghost">Cart</a>
        <a href="logout.php" class="btn-ghost">Sign Out</a>
      <?php else: ?>
        <a href="signin.php" class="btn-ghost">Sign In</a>
        <a href="signup.php" class="btn-gold">Sign Up</a>
      <?php endif; ?>
    </div>
    <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
  </div>
</nav>