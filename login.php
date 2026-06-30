<?php
require_once 'includes/db.php';
$pageTitle = 'Login — Mealtime';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = mysqli_prepare($conn,
            "SELECT customer_id, full_name, password_hash FROM customers WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Success — store identity in the session.
            $_SESSION['customer_id']   = $user['customer_id'];
            $_SESSION['customer_name'] = $user['full_name'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Incorrect email or password.';
        }
    }
}

include 'includes/header.php';
?>

<section class="auth-section">
  <div class="auth-card">
    <div class="bowl-dot" style="background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;">🐾</div>
    <h1>Welcome back</h1>
    <p class="sub">Log in to track orders and manage your account.</p>

    <?php if ($error): ?>
      <div class="form-error" style="display:block;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form class="auth-form" method="POST" action="login.php">
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" placeholder="you@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
      </div>
      <div class="field">
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required minlength="6">
      </div>
      <div class="checkbox-row" style="justify-content:space-between;display:flex;align-items:center;">
        <span style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="remember"> Remember me</span>
        <a href="#" style="color:var(--accent);font-weight:600;">Forgot password?</a>
      </div>
      <button type="submit" class="btn btn-primary">Log in →</button>
    </form>

    <p class="auth-switch">Don't have an account? <a href="register.php">Create one</a></p>

    <div class="divider-text">Test account</div>
    <p style="font-size:12px;color:#8a7c6c;text-align:center;">
      The seed data includes <strong>amal.perera@example.com</strong>, but its password hash is a placeholder —
      register a new account to test login for real.
    </p>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
