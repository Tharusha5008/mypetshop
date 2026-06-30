<?php

$pageTitle = $pageTitle ?? 'Mealtime — Pet Food, Done Right';
$loggedIn = isset($_SESSION['customer_id']);
$customerName = $_SESSION['customer_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pageTitle); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header>
  <div class="nav-inner">
    <a href="index.php" class="logo"><span class="bowl-dot">🐾</span>Mealtime</a>
    <nav class="links">
      <a href="index.php#catalog">Shop</a>
      <a href="aboutus.php">About</a>
      <a href="contact.php">Contact</a>
      <a href="complaint.php">Complaints</a>
    </nav>
    <div class="nav-actions">
      <?php if ($loggedIn): ?>
        <span style="font-size:14px;font-weight:600;color:#52453a;">Hi, <?php echo htmlspecialchars($customerName); ?></span>
        <a href="logout.php" class="btn btn-ghost" style="padding:9px 18px;font-size:14px;">Log out</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-ghost" style="padding:9px 18px;font-size:14px;">Login</a>
      <?php endif; ?>
      <a href="cart.php" class="cart-btn">🛒 Cart <span class="cart-count" id="cartCount">0</span></a>
    </div>
  </div>
</header>
