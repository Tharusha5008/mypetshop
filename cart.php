<?php
require_once 'includes/db.php';
$pageTitle = 'Your Cart — Mealtime';

$orderSuccess = false;
$orderId = null;
$checkoutError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $cartJson = $_POST['cart_data'] ?? '[]';
    $cartItems = json_decode($cartJson, true);

    $fullName   = trim($_POST['full_name'] ?? '');
    $address1   = trim($_POST['address'] ?? '');
    $city       = trim($_POST['city'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');
    $email      = trim($_POST['email'] ?? '');

    if (empty($cartItems) || !is_array($cartItems)) {
        $checkoutError = 'Your cart is empty.';
    } elseif ($fullName === '' || $address1 === '' || $city === '' || $postalCode === '' || $email === '') {
        $checkoutError = 'Please fill in all delivery details.';
    } else {
        // Re-fetch real prices from the database for every product in the cart.
        // Never trust prices sent from the browser — they could be tampered with.
        $verifiedItems = [];
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $productId = (int)($item['id'] ?? 0);
            $qty = max(1, (int)($item['qty'] ?? 1));

            $stmt = mysqli_prepare($conn, "SELECT product_id, name, price FROM products WHERE product_id = ? AND is_active = 1");
            mysqli_stmt_bind_param($stmt, 'i', $productId);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $product = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);

            if ($product) {
                $lineTotal = $product['price'] * $qty;
                $subtotal += $lineTotal;
                $verifiedItems[] = [
                    'product_id' => $product['product_id'],
                    'qty' => $qty,
                    'unit_price' => $product['price'],
                    'line_total' => $lineTotal,
                ];
            }
        }

        if (empty($verifiedItems)) {
            $checkoutError = 'None of the items in your cart could be found. Please refresh and try again.';
        } else {
            $delivery = $subtotal < 50 ? 4.99 : 0.00;
            $total = $subtotal + $delivery;

            // If logged in, attach the order to the customer. Otherwise, walk through
            // a lightweight guest path: find-or-create a customer row by email so the
            // order still satisfies the NOT NULL customer_id foreign key in schema.sql.
            $customerId = $_SESSION['customer_id'] ?? null;

            if (!$customerId) {
                $find = mysqli_prepare($conn, "SELECT customer_id FROM customers WHERE email = ?");
                mysqli_stmt_bind_param($find, 's', $email);
                mysqli_stmt_execute($find);
                $foundResult = mysqli_stmt_get_result($find);
                $found = mysqli_fetch_assoc($foundResult);
                mysqli_stmt_close($find);

                if ($found) {
                    $customerId = $found['customer_id'];
                } else {
                    $guestPasswordHash = password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);
                    $insertCust = mysqli_prepare($conn,
                        "INSERT INTO customers (full_name, email, password_hash) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($insertCust, 'sss', $fullName, $email, $guestPasswordHash);
                    mysqli_stmt_execute($insertCust);
                    $customerId = mysqli_insert_id($conn);
                    mysqli_stmt_close($insertCust);
                }
            }

            // Insert the delivery address.
            $insertAddr = mysqli_prepare($conn,
                "INSERT INTO addresses (customer_id, label, address_line1, city, postal_code, country)
                 VALUES (?, 'Checkout', ?, ?, ?, 'Sri Lanka')");
            mysqli_stmt_bind_param($insertAddr, 'isss', $customerId, $address1, $city, $postalCode);
            mysqli_stmt_execute($insertAddr);
            $addressId = mysqli_insert_id($conn);
            mysqli_stmt_close($insertAddr);

            // Insert the order.
            $insertOrder = mysqli_prepare($conn,
                "INSERT INTO orders (customer_id, address_id, status, subtotal, delivery_fee, total)
                 VALUES (?, ?, 'paid', ?, ?, ?)");
            mysqli_stmt_bind_param($insertOrder, 'iiddd', $customerId, $addressId, $subtotal, $delivery, $total);
            mysqli_stmt_execute($insertOrder);
            $orderId = mysqli_insert_id($conn);
            mysqli_stmt_close($insertOrder);

            // Insert each order line item.
            $insertItem = mysqli_prepare($conn,
                "INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total)
                 VALUES (?, ?, ?, ?, ?)");
            foreach ($verifiedItems as $vi) {
                mysqli_stmt_bind_param($insertItem, 'iiidd',
                    $orderId, $vi['product_id'], $vi['qty'], $vi['unit_price'], $vi['line_total']);
                mysqli_stmt_execute($insertItem);
            }
            mysqli_stmt_close($insertItem);

            $orderSuccess = true;
        }
    }
}

include 'includes/header.php';
?>

<section class="page-hero">
  <div class="wrap">
    <div class="crumb"><a href="index.php">Home</a> / Cart</div>
    <h1>Your cart</h1>
    <p>Review what's in your bowl before checkout.</p>
  </div>
</section>

<section class="cart-page">
  <div class="wrap" id="cartWrap">
    <?php if ($orderSuccess): ?>
      <div class="cart-empty-state">
        <div class="icon">✅</div>
        <h3>Order #<?php echo htmlspecialchars($orderId); ?> placed!</h3>
        <p>Your pet's next meal is on its way. It's now saved in the database under the <code>orders</code> table.</p>
        <a href="index.php#catalog" class="btn btn-primary">Back to shop →</a>
      </div>
    <?php endif; ?>
    <!-- non-success state populated by JS reading localStorage -->
  </div>
</section>

<div class="modal-overlay" id="modalOverlay">
  <div class="modal" id="modalContent"></div>
</div>

<?php if ($checkoutError): ?>
<script>alert(<?php echo json_encode($checkoutError); ?>);</script>
<?php endif; ?>

<?php if ($orderSuccess): ?>
<script>clearCart();</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

<script>
const ORDER_SUCCESS = <?php echo $orderSuccess ? 'true' : 'false'; ?>;

function renderCartPage(){
  if(ORDER_SUCCESS) return; // success message is already rendered server-side
  const wrap = document.getElementById('cartWrap');
  const cart = getCart();

  if(cart.length === 0){
    wrap.innerHTML = `
      <div class="cart-empty-state">
        <div class="icon">🥣</div>
        <h3>Your cart is empty</h3>
        <p>Go fill a bowl — your pet is waiting.</p>
        <a href="index.php#catalog" class="btn btn-primary">Browse the menu →</a>
      </div>`;
    return;
  }

  const subtotal = cartSubtotal();
  const delivery = subtotal > 0 && subtotal < 50 ? 4.99 : 0;
  const total = subtotal + delivery;

  wrap.innerHTML = `
    <div class="cart-layout">
      <div>
        <table class="cart-table">
          <thead>
            <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>
          </thead>
          <tbody id="cartRows"></tbody>
        </table>
      </div>
      <div class="summary-card">
        <h3>Order summary</h3>
        <div class="row"><span>Subtotal</span><span>$${subtotal.toFixed(2)}</span></div>
        <div class="row"><span>Delivery</span><span>${delivery === 0 ? 'Free' : '$' + delivery.toFixed(2)}</span></div>
        <div class="row total"><span>Total</span><span>$${total.toFixed(2)}</span></div>
        <button class="btn btn-primary" id="checkoutBtn">Checkout →</button>
      </div>
    </div>`;

  const rows = document.getElementById('cartRows');
  rows.innerHTML = cart.map(i => `
    <tr>
      <td>
        <div class="cart-prod">
          <div class="art">${i.art}</div>
          <div>
            <h4>${i.name}</h4>
            <div class="meta">${i.meta}</div>
          </div>
        </div>
      </td>
      <td class="price">$${i.price.toFixed(2)}</td>
      <td>
        <div class="qty-ctrl">
          <button onclick="handleQty(${i.id},-1)" aria-label="Decrease quantity">−</button>
          <span>${i.qty}</span>
          <button onclick="handleQty(${i.id},1)" aria-label="Increase quantity">+</button>
        </div>
      </td>
      <td class="price">$${(i.price*i.qty).toFixed(2)}</td>
      <td><button class="cart-remove" onclick="handleRemove(${i.id})">Remove</button></td>
    </tr>`).join('');

  document.getElementById('checkoutBtn').addEventListener('click', openCheckout);
}

function handleQty(id, delta){
  changeCartQty(id, delta);
  renderCartPage();
}
function handleRemove(id){
  removeFromCart(id);
  renderCartPage();
}

function openModal(html){
  document.getElementById('modalContent').innerHTML = html;
  document.getElementById('modalOverlay').classList.add('open');
}
function closeModal(){
  document.getElementById('modalOverlay').classList.remove('open');
}
document.getElementById('modalOverlay').addEventListener('click', e => {
  if(e.target.id === 'modalOverlay') closeModal();
});

function openCheckout(){
  const subtotal = cartSubtotal();
  const delivery = subtotal > 0 && subtotal < 50 ? 4.99 : 0;
  const total = subtotal + delivery;
  const cartData = JSON.stringify(getCart());

  openModal(`
    <h3>Checkout</h3>
    <p class="sub">Final step — tell us where the food's headed. This submits to the server and saves a real order.</p>
    <form method="POST" action="cart.php">
      <input type="hidden" name="checkout" value="1">
      <input type="hidden" name="cart_data" value='${cartData.replace(/'/g, "&#39;")}'>
      <div class="field"><label>Full name</label><input type="text" name="full_name" required></div>
      <div class="field"><label>Delivery address</label><input type="text" name="address" required></div>
      <div class="modal-row2">
        <div class="field"><label>City</label><input type="text" name="city" required></div>
        <div class="field"><label>Postal code</label><input type="text" name="postal_code" required></div>
      </div>
      <div class="field"><label>Email</label><input type="email" name="email" required></div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:8px;">Place order — $${total.toFixed(2)} →</button>
    </form>
  `);
}

renderCartPage();
</script>
</body>
</html>
