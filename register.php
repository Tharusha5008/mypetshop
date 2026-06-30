<?php
require_once 'includes/db.php';
$pageTitle = 'Register — Mealtime';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'Please fill in all required fields.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // Check for an existing account with this email
    if (empty($errors)) {
        $check = mysqli_prepare($conn, "SELECT customer_id FROM customers WHERE email = ?");
        mysqli_stmt_bind_param($check, 's', $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        if (mysqli_stmt_num_rows($check) > 0) {
            $errors[] = 'An account with that email already exists.';
        }
        mysqli_stmt_close($check);
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = mysqli_prepare($conn,
            "INSERT INTO customers (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $phone, $passwordHash);

        if (mysqli_stmt_execute($stmt)) {
            $success = true;
        } else {
            $errors[] = 'Something went wrong creating your account. Please try again.';
        }
        mysqli_stmt_close($stmt);
    }
}

include 'includes/header.php';
?>

<section class="auth-section">
  <div class="auth-card">
    <div class="bowl-dot" style="background:var(--sage);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;">🐾</div>
    <h1>Create your account</h1>
    <p class="sub">Join Mealtime to track orders and save your favorites.</p>

    <?php if (!empty($errors)): ?>
      <div class="form-error" style="display:block;">
        <?php foreach ($errors as $e) echo htmlspecialchars($e) . '<br>'; ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="form-success" style="display:block;">
        Account created! You can now <a href="login.php">log in</a>.
      </div>
    <?php else: ?>
      <form class="auth-form" method="POST" action="register.php">
        <div class="field">
          <label>Full name</label>
          <input type="text" name="name" placeholder="Your name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
        </div>
        <div class="field">
          <label>Email</label>
          <input type="email" name="email" placeholder="you@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        <div class="field">
          <label>Phone</label>
          <input type="tel" name="phone" placeholder="+94 7X XXX XXXX" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
        </div>
        <div class="auth-row2">
          <div class="field">
            <label>Password</label>
            <input type="password" name="password" placeholder="At least 6 characters" required minlength="6">
          </div>
          <div class="field">
            <label>Confirm password</label>
            <input type="password" name="confirm" placeholder="Repeat password" required minlength="6">
          </div>
        </div>
        <div class="checkbox-row">
          <input type="checkbox" required>
          <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
        </div>
        <button type="submit" class="btn btn-primary">Create account →</button>
      </form>
    <?php endif; ?>

    <p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
