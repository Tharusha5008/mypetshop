<?php
require_once 'includes/db.php';
$pageTitle = 'Contact Us — Mealtime';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $message);
        if (mysqli_stmt_execute($stmt)) {
            $success = true;
        } else {
            $error = 'Something went wrong sending your message. Please try again.';
        }
        mysqli_stmt_close($stmt);
    }
}

include 'includes/header.php';
?>

<section class="page-hero">
  <div class="wrap">
    <div class="crumb"><a href="index.php">Home</a> / Contact</div>
    <div class="eyebrow"><span class="pip"></span>We'd love to hear from you</div>
    <h1>Questions before mealtime?</h1>
    <p>Ask us anything — allergies, portion sizes, delivery timing, or just to talk about your pet. We answer every message within one business day.</p>
  </div>
</section>

<section class="contact">
  <div class="wrap">
    <div>
      <h2>Get in touch</h2>
      <p class="lede">Pick whichever way is easiest for you. We're real people on the other end, promise.</p>
      <div class="contact-detail"><span class="ico">📍</span><span>14 Harbor Lane, Colombo, Sri Lanka</span></div>
      <div class="contact-detail"><span class="ico">✉️</span><span>hello@mealtime.shop</span></div>
      <div class="contact-detail"><span class="ico">📞</span><span>+94 11 234 5678</span></div>
      <div class="contact-detail"><span class="ico">🕑</span><span>Mon–Sat, 9am–6pm</span></div>

      <div style="margin-top:36px;">
        <h4 style="font-size:13px;text-transform:uppercase;letter-spacing:0.05em;color:#8a7c6c;margin-bottom:14px;">Frequently asked</h4>
        <div class="timeline-step">
          <div class="dot" style="background:var(--accent);">?</div>
          <div><h5>Do you ship nationwide?</h5><p>Yes — free delivery on orders over $50, flat $4.99 fee under that.</p></div>
        </div>
        <div class="timeline-step">
          <div class="dot" style="background:var(--accent);">?</div>
          <div><h5>Can I change my order after placing it?</h5><p>Contact us within 2 hours of ordering and we'll do our best to adjust it.</p></div>
        </div>
      </div>
    </div>

    <form class="contact-form" method="POST" action="contact.php">
      <?php if ($success): ?>
        <div class="form-success" style="display:block;">Thanks! Your message has been saved — we'll reply within a day.</div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="form-error" style="display:block;"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <div class="field"><label>Name</label><input type="text" name="name" required placeholder="Your name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"></div>
      <div class="field"><label>Email</label><input type="email" name="email" required placeholder="you@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"></div>
      <div class="field"><label>Message</label><textarea name="message" rows="5" required placeholder="Tell us about your pet..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea></div>
      <button type="submit" class="btn btn-primary">Send message →</button>
    </form>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
