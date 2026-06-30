<?php
require_once 'includes/db.php';
$pageTitle = 'Complaints — Mealtime';

$success = false;
$error = '';
$refNum = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $orderNo = trim($_POST['order_no'] ?? '');
    $type    = $_POST['ctype'] ?? 'other';
    $details = trim($_POST['details'] ?? '');

    $validTypes = ['quality', 'delivery', 'wrong', 'billing', 'other'];
    if (!in_array($type, $validTypes, true)) $type = 'other';

    if ($name === '' || $email === '' || $details === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
     
        $customerId = $_SESSION['customer_id'] ?? null;

        $stmt = mysqli_prepare($conn,
            "INSERT INTO complaints (customer_id, full_name, email, complaint_type, details, status)
             VALUES (?, ?, ?, ?, ?, 'open')");
        mysqli_stmt_bind_param($stmt, 'issss', $customerId, $name, $email, $type, $details);

        if (mysqli_stmt_execute($stmt)) {
            $refNum = mysqli_insert_id($conn);
            $success = true;
        } else {
            $error = 'Something went wrong submitting your complaint. Please try again.';
        }
        mysqli_stmt_close($stmt);
    }
}

$recent = [];
$recentResult = mysqli_query($conn,
    "SELECT complaint_id, complaint_type, status, submitted_at FROM complaints ORDER BY submitted_at DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($recentResult)) {
    $recent[] = $row;
}

function complaintTypeLabel($type) {
    return match ($type) {
        'quality' => 'Product quality',
        'delivery' => 'Late / missing delivery',
        'wrong' => 'Wrong item received',
        'billing' => 'Billing issue',
        default => 'Other',
    };
}

function statusBadgeClass($status) {
    return match ($status) {
        'resolved' => 'resolved',
        'in_progress' => 'progress',
        default => 'open',
    };
}

function statusLabel($status) {
    return match ($status) {
        'resolved' => 'Resolved',
        'in_progress' => 'In progress',
        default => 'Open',
    };
}

include 'includes/header.php';
?>

<section class="page-hero">
  <div class="wrap">
    <div class="crumb"><a href="index.php">Home</a> / Complaints</div>
    <div class="eyebrow"><span class="pip" style="background:var(--accent-dark);"></span>We take this seriously</div>
    <h1>Something went wrong? Tell us.</h1>
    <p>Wrong order, damaged bag, quality issue, or a delivery that never showed up — let us know and we'll make it right.</p>
  </div>
</section>

<section class="complaint-section">
  <div class="wrap">
    <div class="complaint-layout">
      <div class="complaint-form-card">
        <h3>File a complaint</h3>
        <p class="sub">Give us as much detail as you can — order number especially helps us move fast.</p>

        <?php if ($success): ?>
          <div class="form-success" style="display:block;">
            Your complaint has been submitted. Reference #<?php echo htmlspecialchars($refNum); ?> — we'll follow up by email within 24 hours.
          </div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="form-error" style="display:block;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="complaint.php" id="complaintForm">
          <div class="auth-row2">
            <div class="field"><label>Full name</label><input type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"></div>
            <div class="field"><label>Email</label><input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"></div>
          </div>
          <div class="field"><label>Order number (if applicable)</label><input type="text" name="order_no" placeholder="e.g. #10452" value="<?php echo htmlspecialchars($_POST['order_no'] ?? ''); ?>"></div>

          <div class="field" style="margin-bottom:18px;">
            <label style="margin-bottom:10px;">Complaint type</label>
            <div class="radio-group" id="complaintType">
              <label class="radio-chip checked"><input type="radio" name="ctype" value="quality" checked>Product quality</label>
              <label class="radio-chip"><input type="radio" name="ctype" value="delivery">Late / missing delivery</label>
              <label class="radio-chip"><input type="radio" name="ctype" value="wrong">Wrong item received</label>
              <label class="radio-chip"><input type="radio" name="ctype" value="billing">Billing issue</label>
              <label class="radio-chip"><input type="radio" name="ctype" value="other">Other</label>
            </div>
          </div>

          <div class="field"><label>Details</label><textarea name="details" rows="5" required placeholder="Tell us what happened..."><?php echo htmlspecialchars($_POST['details'] ?? ''); ?></textarea></div>
          <button type="submit" class="btn btn-primary">Submit complaint →</button>
        </form>
      </div>

      <div class="info-side">
        <h4>What happens next</h4>
        <div class="timeline-step">
          <div class="dot">1</div>
          <div><h5>We log it</h5><p>Your complaint gets a reference number immediately.</p></div>
        </div>
        <div class="timeline-step">
          <div class="dot">2</div>
          <div><h5>We review it</h5><p>Our team looks into the order and reaches out within 24 hours.</p></div>
        </div>
        <div class="timeline-step">
          <div class="dot">3</div>
          <div><h5>We resolve it</h5><p>Replacement, refund, or whatever fixes it — we follow through.</p></div>
        </div>

        <h4 style="margin-top:34px;">Recent complaints (live from database)</h4>
        <div class="complaint-list">
          <?php if (empty($recent)): ?>
            <p style="color:#8a7c6c;font-size:13px;">No complaints filed yet.</p>
          <?php else: ?>
            <?php foreach ($recent as $c): ?>
              <div class="complaint-item">
                <div>
                  <h5>#<?php echo htmlspecialchars($c['complaint_id']); ?> — <?php echo htmlspecialchars(complaintTypeLabel($c['complaint_type'])); ?></h5>
                  <span class="meta">Submitted <?php echo htmlspecialchars(date('M j, Y', strtotime($c['submitted_at']))); ?></span>
                </div>
                <span class="status-badge <?php echo statusBadgeClass($c['status']); ?>"><?php echo statusLabel($c['status']); ?></span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
document.getElementById('complaintType').addEventListener('click', e => {
  const chip = e.target.closest('.radio-chip');
  if(!chip) return;
  document.querySelectorAll('#complaintType .radio-chip').forEach(c => c.classList.remove('checked'));
  chip.classList.add('checked');
});
</script>

</body>
</html>
