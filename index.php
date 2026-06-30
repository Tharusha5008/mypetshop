<?php
require_once 'includes/db.php';

$pageTitle = 'Mealtime — Pet Food, Done Right';

// Pull active products with their category slug, straight from the database.
$sql = "SELECT p.product_id, p.name, p.description, p.price, p.life_stage,
               p.weight_kg, p.is_grain_free, c.slug AS category_slug
        FROM products p
        JOIN categories c ON c.category_id = p.category_id
        WHERE p.is_active = 1
        ORDER BY c.slug, p.name";
$result = mysqli_query($conn, $sql);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

// Simple emoji per category, just for visual variety since we don't have real photos yet.
function categoryEmoji($slug) {
    return match ($slug) {
        'dog' => '🐶',
        'cat' => '🐱',
        'small-pet' => '🐹',
        default => '🍽️',
    };
}

include 'includes/header.php';
?>

<section class="hero">
  <div class="hero-inner">
    <div>
      <div class="eyebrow"><span class="pip"></span>Fresh batches, every week</div>
      <h1>Good food fills <em>every</em> bowl in the house.</h1>
      <p class="lede">Real ingredients for dogs, cats, and the small ones too. No fillers, no mystery meat — just meals worth wagging for.</p>
      <div class="hero-ctas">
        <a href="#catalog" class="btn btn-primary">Browse the menu →</a>
        <a href="aboutus.php" class="btn btn-ghost">Our story</a>
      </div>
    </div>
    <div class="bowl-stage">
      <div class="bowl-rim"></div>
      <div class="bowl-ring"></div>
      <div class="kibble-piece" style="width:34px;height:30px;top:30%;left:32%;"></div>
      <div class="kibble-piece" style="width:26px;height:24px;top:48%;left:55%;"></div>
      <div class="kibble-piece" style="width:30px;height:28px;top:60%;left:30%;"></div>
      <div class="kibble-piece" style="width:22px;height:20px;top:35%;left:60%;"></div>
      <div class="float-tag" style="top:6%;left:-8%;"><span class="pip" style="background:var(--sage);"></span>Grain-free options</div>
      <div class="float-tag" style="bottom:8%;right:-10%;animation-delay:1.2s;"><span class="pip" style="background:var(--accent);"></span>Vet-approved</div>
    </div>
  </div>
  <svg class="curve-divider" viewBox="0 0 1440 80" preserveAspectRatio="none" style="height:60px;">
    <path d="M0,0 C480,80 960,80 1440,0 L1440,80 L0,80 Z" fill="#1F3D2B"/>
  </svg>
</section>

<div class="trust">
  <div class="wrap">
    <span>🚚 Free delivery over $50</span>
    <span>🌾 No artificial fillers</span>
    <span>🐶 Made for every life stage</span>
    <span>↩️ Easy returns, no fuss</span>
  </div>
</div>

<section class="catalog" id="catalog">
  <div class="wrap">
    <div class="section-head">
      <div>
        <h2>What's on the menu</h2>
        <p>Pick a bowl, any bowl. Everything's made from ingredients you can actually pronounce. Live from our database — <?php echo count($products); ?> products in stock.</p>
      </div>
    </div>
    <div class="tabs" id="tabs">
      <button class="tab active" data-cat="all">All</button>
      <button class="tab" data-cat="dog">Dog</button>
      <button class="tab" data-cat="cat">Cat</button>
      <button class="tab" data-cat="small-pet">Small Pet</button>
    </div>
    <div class="grid" id="productGrid">
      <?php if (empty($products)): ?>
        <p style="color:#6b5d4f;">No products found. Make sure <code>schema.sql</code> has been imported into the <code>mealtime_shop</code> database.</p>
      <?php else: ?>
        <?php foreach ($products as $p): ?>
          <div class="card" data-cat="<?php echo htmlspecialchars($p['category_slug']); ?>">
            <?php if ($p['is_grain_free']): ?><span class="card-tag">Grain-free</span><?php endif; ?>
            <div class="card-art"><?php echo categoryEmoji($p['category_slug']); ?></div>
            <h3><?php echo htmlspecialchars($p['name']); ?></h3>
            <div class="meta"><?php echo htmlspecialchars(ucfirst($p['life_stage'])); ?> · <?php echo htmlspecialchars($p['weight_kg']); ?>kg bag</div>
            <div class="card-foot">
              <span class="price">$<?php echo number_format($p['price'], 2); ?></span>
              <button class="add-btn"
                onclick='addToCart(<?php echo json_encode([
                  "id" => (int)$p["product_id"],
                  "name" => $p["name"],
                  "meta" => ucfirst($p["life_stage"]) . " · " . $p["weight_kg"] . "kg bag",
                  "price" => (float)$p["price"],
                  "art" => categoryEmoji($p["category_slug"])
                ], JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                aria-label="Add <?php echo htmlspecialchars($p['name']); ?> to cart">+</button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
document.getElementById('tabs').addEventListener('click', e => {
  const tab = e.target.closest('.tab');
  if(!tab) return;
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  tab.classList.add('active');
  const filter = tab.dataset.cat;
  document.querySelectorAll('#productGrid .card').forEach(card => {
    card.style.display = (filter === 'all' || card.dataset.cat === filter) ? '' : 'none';
  });
});
</script>

</body>
</html>
