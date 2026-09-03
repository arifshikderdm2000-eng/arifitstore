<?php
/**
 * Checkout Page
 * BongoStore BD - Complete Order Placement with Bangladesh Address, Districts, and COD
 */
require_once __DIR__ . '/includes/functions.php';

// Handle "Buy Now" shortcut
if (isset($_GET['buy_now'])) {
    $buyNowId = (int)$_GET['buy_now'];
    $buyNowQty = isset($_GET['qty']) ? max(1, (int)$_GET['qty']) : 1;
    if ($buyNowId > 0) {
        // If user already has items, just ensure this item is added
        add_to_cart($buyNowId, $buyNowQty);
    }
}

$cartData = get_cart_details();
$items = $cartData['items'];
$subtotal = $cartData['subtotal'];

if (empty($items)) {
    header("Location: /cart.php");
    exit;
}

$errors = [];
$customerName = '';
$customerPhone = '';
$customerAltPhone = '';
$customerEmail = '';
$deliveryAddress = '';
$selectedDistrict = 'Dhaka';
$deliveryArea = $_SESSION['delivery_area'] ?? 'Inside Dhaka';
$orderNotes = '';
$paymentMethod = 'cod';
$mobileTxId = '';

$districts = get_bangladesh_districts();

// Handle Order Placement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = sanitize($_POST['customer_name'] ?? '');
    $customerPhone = sanitize($_POST['customer_phone'] ?? '');
    $customerAltPhone = sanitize($_POST['customer_alt_phone'] ?? '');
    $customerEmail = sanitize($_POST['customer_email'] ?? '');
    $deliveryAddress = sanitize($_POST['delivery_address'] ?? '');
    $selectedDistrict = sanitize($_POST['district'] ?? 'Dhaka');
    $deliveryArea = sanitize($_POST['delivery_area'] ?? 'Inside Dhaka');
    $orderNotes = sanitize($_POST['order_notes'] ?? '');
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'cod');
    $mobileTxId = sanitize($_POST['mobile_tx_id'] ?? '');

    // Validation
    if (empty($customerName)) {
        $errors[] = 'আপনার পূর্ণ নাম লিখুন।';
    }

    if (empty($customerPhone) || !preg_match('/^(01[3-9]\d{8})$/', str_replace([' ', '-'], '', $customerPhone))) {
        $errors[] = 'সঠিক ১১ ডিজিটের বাংলাদেশী মোবাইল নম্বর দিন (যেমন: 01712345678)।';
    }

    if (empty($deliveryAddress)) {
        $errors[] = 'আপনার সম্পূর্ণ ডেলিভারি ঠিকানা লিখুন।';
    }

    // Recalculate Totals
    $deliveryCharge = ($deliveryArea === 'Outside Dhaka') ? 130 : 70;
    $discountAmount = 0;
    $appliedCoupon = $_SESSION['applied_coupon'] ?? null;
    if ($appliedCoupon && $subtotal > 0) {
        $discountAmount = min($subtotal, (float)$appliedCoupon['discount']);
    }
    $totalAmount = max(0, $subtotal + $deliveryCharge - $discountAmount);

    if (empty($errors)) {
        $pdo = get_db_connection();
        $orderNumber = generate_order_number();

        try {
            $pdo->beginTransaction();

            $dateStr = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    order_number, customer_name, customer_phone, customer_alt_phone, customer_email,
                    address, delivery_address, district, delivery_area, order_notes, payment_method,
                    subtotal, delivery_charge, discount_amount, total_amount, status, order_status, payment_status, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, 'Pending', 'pending', 'unpaid', ?
                )
            ");

            $stmt->execute([
                $orderNumber,
                $customerName,
                $customerPhone,
                $customerAltPhone,
                $customerEmail,
                $deliveryAddress,
                $deliveryAddress,
                $selectedDistrict,
                $deliveryArea,
                $orderNotes . (!empty($mobileTxId) ? " [TrxID: $mobileTxId]" : ""),
                $paymentMethod,
                $subtotal,
                $deliveryCharge,
                $discountAmount,
                $totalAmount,
                $dateStr
            ]);

            $orderId = $pdo->lastInsertId();

            // Insert order items
            $itemStmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, product_sku, product_price, unit_price, quantity, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $updateStockStmt = $pdo->prepare("UPDATE products SET stock = MAX(0, stock - ?) WHERE id = ?");

            foreach ($items as $item) {
                $p = $item['product'];
                $itemStmt->execute([
                    $orderId,
                    $p['id'],
                    $p['name'],
                    $p['sku'] ?? 'GEN-' . $p['id'],
                    $item['unit_price'],
                    $item['unit_price'],
                    $item['quantity'],
                    $item['subtotal']
                ]);

                // Deduct stock
                $updateStockStmt->execute([$item['quantity'], $p['id']]);
            }

            $pdo->commit();

            // Clear session cart and coupon
            clear_cart();
            unset($_SESSION['applied_coupon']);
            unset($_SESSION['delivery_area']);

            // Redirect to Order Success Page
            header("Location: /order-success.php?order_number=" . urlencode($orderNumber));
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'অর্ডার প্রক্রিয়াকরণে সমস্যা হয়েছে: ' . $e->getMessage();
        }
    }
}

// Initial Delivery Charge for UI
$deliveryCharge = ($deliveryArea === 'Outside Dhaka') ? 130 : 70;
$discountAmount = 0;
$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
if ($appliedCoupon && $subtotal > 0) {
    $discountAmount = min($subtotal, (float)$appliedCoupon['discount']);
}
$grandTotal = max(0, $subtotal + $deliveryCharge - $discountAmount);

$pageTitle = "অর্ডার চেকআউট - " . STORE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <div style="padding: 16px 0 10px;">
        <h1 style="font-size: 1.65rem; margin-bottom: 4px;">
            <i class="fa-solid fa-credit-card text-emerald-600"></i> অর্ডার চেকআউট (Order Checkout)
        </h1>
        <p style="color: var(--text-muted); font-size: 0.92rem;">অর্ডারটি নিশ্চিত করতে নিচের তথ্যগুলো সঠিকভাবে পূরণ করুন।</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/checkout.php" method="POST" id="checkout-order-form">
        <!-- Hidden inputs for total calculations -->
        <input type="hidden" id="cart-subtotal-val" value="<?= $subtotal ?>">
        <input type="hidden" id="cart-discount-val" value="<?= $discountAmount ?>">

        <div class="checkout-grid">
            <!-- Left: Delivery Address & Customer Details -->
            <div class="checkout-form-card">
                <h3 style="font-size: 1.2rem; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    <i class="fa-solid fa-user-tag text-emerald-600"></i> গ্রাহকের তথ্য ও ডেলিভারি ঠিকানা
                </h3>

                <div class="form-group">
                    <label class="form-label">আপনার পূর্ণ নাম <span class="required">*</span></label>
                    <input type="text" name="customer_name" class="form-control" placeholder="যেমন: মো: আরিফুল ইসলাম" value="<?= htmlspecialchars($customerName) ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">মোবাইল নম্বর <span class="required">*</span></label>
                        <input type="tel" name="customer_phone" class="form-control" placeholder="017XXXXXXXX" value="<?= htmlspecialchars($customerPhone) ?>" required>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">১১ ডিজিট (ক্যাশ অন ডেলিভারি ভেরিফিকেশনের জন্য)</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">বিকল্প মোবাইল নম্বর (ঐচ্ছিক)</label>
                        <input type="tel" name="customer_alt_phone" class="form-control" placeholder="01XXXXXXXXX" value="<?= htmlspecialchars($customerAltPhone) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ইমেইল ঠিকানা (ঐচ্ছিক)</label>
                    <input type="email" name="customer_email" class="form-control" placeholder="yourname@gmail.com" value="<?= htmlspecialchars($customerEmail) ?>">
                </div>

                <!-- Delivery Area Selection (Inside Dhaka ৳70 vs Outside Dhaka ৳130) -->
                <div class="form-group">
                    <label class="form-label">ডেলিভারি এরিয়া নির্বাচন করুন <span class="required">*</span></label>
                    <div class="radio-card-group">
                        <label class="radio-card <?= $deliveryArea === 'Inside Dhaka' ? 'selected' : '' ?>">
                            <input type="radio" name="delivery_area" value="Inside Dhaka" <?= $deliveryArea === 'Inside Dhaka' ? 'checked' : '' ?>>
                            <div>
                                <strong style="font-size: 0.95rem; color: var(--secondary);">ঢাকা সিটির ভেতরে</strong>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">ডেলিভারি চার্জ: <strong>৳৭০</strong> (২৪-৪৮ ঘণ্টা)</div>
                            </div>
                        </label>
                        <label class="radio-card <?= $deliveryArea === 'Outside Dhaka' ? 'selected' : '' ?>">
                            <input type="radio" name="delivery_area" value="Outside Dhaka" <?= $deliveryArea === 'Outside Dhaka' ? 'checked' : '' ?>>
                            <div>
                                <strong style="font-size: 0.95rem; color: var(--secondary);">ঢাকা সিটির বাইরে</strong>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">ডেলিভারি চার্জ: <strong>৳১৩০</strong> (৩-৫ দিন)</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">আপনার জেলা নির্বাচন করুন <span class="required">*</span></label>
                    <select name="district" class="form-control" required>
                        <?php foreach ($districts as $dist): ?>
                            <option value="<?= htmlspecialchars($dist) ?>" <?= $selectedDistrict === $dist ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dist) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">সম্পূর্ণ ডেলিভারি ঠিকানা <span class="required">*</span></label>
                    <textarea name="delivery_address" class="form-control" rows="3" placeholder="বাড়ি নং, রোড নং, এলাকা, থানা ও পোস্ট কোড..." required><?= htmlspecialchars($deliveryAddress) ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">অর্ডার সংক্রান্ত বিশেষ নোট (ঐচ্ছিক)</label>
                    <input type="text" name="order_notes" class="form-control" placeholder="যেমন: বিকেলে ডেলিভারি দিলে ভালো হয়..." value="<?= htmlspecialchars($orderNotes) ?>">
                </div>

                <!-- Payment Method Section -->
                <h3 style="font-size: 1.2rem; margin-top: 30px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    <i class="fa-solid fa-wallet text-emerald-600"></i> পেমেন্ট পদ্ধতি
                </h3>

                <div class="payment-methods-box">
                    <label class="payment-method-label selected">
                        <input type="radio" name="payment_method" value="cod" checked>
                        <div>
                            <strong style="color: #065f46;"><i class="fa-solid fa-hand-holding-dollar"></i> ক্যাশ অন ডেলিভারি (Cash on Delivery)</strong>
                            <div style="font-size: 0.82rem; color: #334155; margin-top: 2px;">
                                পণ্য হাতে পেয়ে রাইডারের কাছে সম্পূর্ণ মূল্য পরিশোধ করুন। ১০০% নিরাপদ ও সুবিধাজনক।
                            </div>
                        </div>
                    </label>

                    <label class="payment-method-label" style="opacity: 0.9;">
                        <input type="radio" name="payment_method" value="bKash">
                        <div>
                            <strong><i class="fa-solid fa-mobile-screen"></i> বিকাশ / নগদ (মোবাইল ব্যাংকিং)</strong>
                            <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 2px;">
                                মার্চেন্ট নাম্বার: <strong>01700-112233</strong> (সেন্ড মানি বা পেমেন্ট করে TrxID লিখে অর্ডার করুন)
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <div class="order-summary-box">
                <h3 style="font-size: 1.2rem; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    অর্ডার সামারি (<?= count($items) ?> টি পণ্য)
                </h3>

                <!-- Products Mini List -->
                <div style="max-height: 280px; overflow-y: auto; margin-bottom: 16px; padding-right: 4px;">
                    <?php foreach ($items as $item): ?>
                        <?php $p = $item['product']; ?>
                        <div class="checkout-product-item">
                            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="checkout-thumb">
                            <div style="flex: 1;">
                                <div style="font-size: 0.88rem; font-weight: 600; color: var(--secondary); line-height: 1.3;">
                                    <?= htmlspecialchars($p['name']) ?>
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                    পরিমাণ: <?= $item['quantity'] ?> × <?= format_bdt($item['unit_price']) ?>
                                </div>
                            </div>
                            <div style="font-weight: 700; font-size: 0.92rem; color: var(--secondary);">
                                <?= format_bdt($item['subtotal']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Price Breakdown in ৳ BDT -->
                <div class="summary-row">
                    <span>পণ্যের মূল্য (Subtotal):</span>
                    <strong><?= format_bdt($subtotal) ?></strong>
                </div>

                <div class="summary-row">
                    <span>ডেলিভারি চার্জ:</span>
                    <strong id="display-delivery-charge" style="color: var(--primary);">
                        <?= format_bdt($deliveryCharge) ?>
                    </strong>
                </div>

                <?php if ($discountAmount > 0): ?>
                    <div class="summary-row" style="color: var(--danger);">
                        <span>কুপন ছাড় (<?= htmlspecialchars($appliedCoupon['code'] ?? '') ?>):</span>
                        <strong>-<?= format_bdt($discountAmount) ?></strong>
                    </div>
                <?php endif; ?>

                <div class="summary-row total">
                    <span>সর্বমোট প্রদেয় বিল (Total):</span>
                    <span id="display-grand-total" style="color: var(--primary);">
                        <?= format_bdt($grandTotal) ?>
                    </span>
                </div>

                <button type="submit" class="btn-checkout" id="btn-place-order" style="margin-top: 20px;">
                    <i class="fa-solid fa-check-circle"></i> অর্ডার নিশ্চিত করুন (Confirm Order)
                </button>

                <div style="margin-top: 14px; text-align: center; font-size: 0.78rem; color: var(--text-muted); line-height: 1.4;">
                    <i class="fa-solid fa-lock text-emerald-600"></i> আপনার তথ্য সম্পূর্ণ সুরক্ষিত। অর্ডার করার পর আমাদের প্রতিনিধি আপনাকে কল করে ডেলিভারি নিশ্চিত করবেন।
                </div>
            </div>
        </div>
    </form>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
