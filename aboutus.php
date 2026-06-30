<?php
require_once 'includes/db.php';
$pageTitle = 'About Us — Mealtime';
include 'includes/header.php';
?>

<section class="page-hero">
  <div class="wrap">
    <div class="crumb"><a href="index.php">Home</a> / About</div>
    <div class="eyebrow"><span class="pip"></span>Our story</div>
    <h1>Started in one kitchen. Still cooks like one.</h1>
    <p>Mealtime began because one very picky beagle refused everything on the shelf. A few kitchen experiments later, we had a recipe — and then a shop full of them.</p>
  </div>
</section>

<section class="about" id="about" style="margin-top:0;">
  <div class="wrap">
    <div class="about-art">🥣</div>
    <div>
      <h2>Real food, no shortcuts</h2>
      <p>Every batch is small, every ingredient is named on the bag, and every recipe is built around a single idea: food your pet would choose for themselves, if they could read labels.</p>
      <p>We work with local farms for our meat and produce, cook in small batches every week, and ship within days of cooking — never months-old stock sitting in a warehouse.</p>
      <div class="about-stats">
        <div><strong>12k+</strong><span>happy bowls a month</span></div>
        <div><strong>40+</strong><span>recipes on the menu</span></div>
        <div><strong>0</strong><span>artificial fillers, ever</span></div>
      </div>
    </div>
  </div>
</section>

<section class="catalog" style="padding-top:30px;">
  <div class="wrap">
    <div class="section-head">
      <div>
        <h2>How we work</h2>
        <p>Three things we don't compromise on, no matter how big we grow.</p>
      </div>
    </div>
    <div class="grid">
      <div class="card">
        <div class="card-art">🌾</div>
        <h3>Real ingredients</h3>
        <div class="meta">Named on every label. Meat, vegetables, and grains you can recognize — never "meat meal" or mystery byproducts.</div>
      </div>
      <div class="card">
        <div class="card-art">🧑‍🍳</div>
        <h3>Small batches</h3>
        <div class="meta">Cooked weekly in small runs, not stockpiled. What you get was made days ago, not months ago.</div>
      </div>
      <div class="card">
        <div class="card-art">🩺</div>
        <h3>Vet-reviewed recipes</h3>
        <div class="meta">Every recipe is checked by a veterinary nutritionist before it ever reaches a bowl.</div>
      </div>
    </div>
  </div>
</section>

<section class="contact" style="padding-top:30px;">
  <div class="wrap" style="grid-template-columns:1fr;text-align:center;">
    <div style="margin:0 auto;">
      <h2>Want to know more?</h2>
      <p class="lede" style="margin:0 auto 26px;">We love talking about food almost as much as our customers' pets love eating it.</p>
      <a href="contact.php" class="btn btn-primary">Get in touch →</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
