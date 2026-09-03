<?php
/**
 * Shopping Cart Page & Cart API Handler
 * BongoStore BD
 */
require_once __DIR__ . '/includes/functions.php';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
          || (isset($_POST['ajax']) && $_POST['ajax'] == 1);

// -------------------------------------------------------------
// POST / GET Action Handling (Add, Update, Remove, Clear, Coupon)
// -------------------------------------------------------------
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($action) {
    if ($action === 'add') {
        $pid = (int)($_POST['product_id'] ?? $_GET['id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? $_GET['qty'] ?? 1);
        if ($pid > 0) {
            add_to_cart($pid, $qty);
            if ($isAjax) {
                echo json_encode([
                    'success' => true,
                    'message' => 'কার্টে যোগ করা হয়েছে',
                    'cart_count' => get_cart_count()
                ]);
                exit;
            }
            set_flash('success', 'পণ্যটি কার্টে সফলভাবে যোগ করা হয়েছে!');
        }
    } elseif ($action === 'update') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 1);
        update_cart($pid, $qty);
        if ($isAjax) {
            echo json_encode([
                'success' => true,
                'cart_count' => get_cart_count()
            ]);
            exit;
        }
        set_flash('success', 'কার্ট আপডেট করা হয়েছে!');
    } elseif ($action === 'remove') {
        $pid = (int)($_POST['product_id'] ?? $_GET['id'] ?? 0);
        remove_from_cart($pid);
        if ($isAjax) {
            echo json_encode([
                'success' => true,
                'cart_count' => get_cart_count()
            ]);
            exit;
        }
        set_flash('info', 'পণ্যটি কার্ট থেকে সরানো হয়েছে।');
    } elseif ($action === 'clear') {
        clear_cart();
        set_flash('info', 'কার্ট খালি করা হয়েছে।');
    } elseif ($action === 'apply_coupon') {
        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        $coupons = get_available_coupons();
        $cartData = get_cart_details();

        if (isset($coupons[$code])) {
            $coupon = $coupons[$code];
            if ($cartData['subtotal'] >= $coupon['min_order']) {
                $_SESSION['applied_coupon'] = [
                    'code' => $code,
                    'discount' => $coupon['discount'],
                    'label' => $coupon['label']
                ];
                set_flash('success', 'কুপন সফলভাবে প্রয়োগ হয়েছে: ' . $coupon['label']);
            } else {
                set_flash('warning', "এই কুপন ব্যবহার করতে ন্যূনতম " . format_bdt($coupon['min_order']) . " টাকার অর্ডার করতে হবে।");
            }
        } else {
            set_flash('danger', 'ভুল বা মেয়াদোত্তীর্ণ কুপন কোড!');
        }
    } elseif ($action === 'remove_coupon') {
        unset($_SESSION['applied_coupon']);
        set_flash('info', 'কুপন সরানো হয়েছে।');
    }

    if (!$isAjax) {
        header("Location: /cart.php");
        exit;
    }
}

// Prepare Cart View Data
$cartData = get_cart_details();
$items = $cartData['items'];
$subtotal = $cartData['subtotal'];

// Delivery fee calculation (Default: Inside Dhaka ৳70)
$deliveryArea = $_SESSION['delivery_area'] ?? 'Inside Dhaka';
if (isset($_GET['area'])) {
    $deliveryArea = ($_GET['area'] === 'outside') ? 'Outside Dhaka' : 'Inside Dhaka';
    $_SESSION['delivery_area'] = $deliveryArea;
}
$deliveryCharge = ($deliveryArea === 'Outside Dhaka') ? 130 : 70;

// Discount calculation
$discountAmount = 0;
$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
if ($appliedCoupon && $subtotal > 0) {
    $discountAmount = min($subtotal, (float)$appliedCoupon['discount']);
}

$grandTotal = max(0, $subtotal + $deliveryCharge - $discountAmount);

$pageTitle = "শপিং কার্ট - " . STORE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <div style="padding: 16px 0 6px;">
        <h1 style="font-size: 1.65rem; margin-bottom: 4px;">
            <i class="fa-solid fa-cart-shopping text-emerald-600"></i> আপনার শপিং কার্ট
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">কার্টের পণ্য যাচাই করুন এবং অর্ডার কনফার্ম করতে চেকআউট করুন।</p>
    </div>

    <?php if (empty($items)): ?>
        <!-- Empty Cart View -->
        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 60px 20px; text-align: center; margin: 20px 0 40px;">
            <i class="fa-solid fa-cart-arrow-down" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 18px;"></i>
            <h2 style="font-size: 1.45rem; margin-bottom: 10px;">আপনার শপিং কার্ট বর্তমানে খালি!</h2>
            <p style="color: var(--text-muted); max-width: 480px; margin: 0 auto 24px;">আপনি এখনও কোনো পণ্য কার্টে যুক্ত করেননি। আমাদের বিশেষ অফার ও আকর্ষণীয় পণ্যগুলো দেখে নিন।</p>
            <a href="/shop.php" class="hero-cta-btn" style="background: var(--primary); color: #fff;">
                <i class="fa-solid fa-bag-shopping"></i> শপিং শুরু করুন
            </a>
        </div>
    <?php else: ?>
        <!-- Full Cart Layout -->
        <div class="cart-layout">
            <!-- Left: Items Table -->
            <div class="cart-table-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    <span style="font-weight: 700; font-size: 1.05rem;">আইটেম তালিকা (<?= count($items) ?> টি)</span>
                    <a href="/cart.php?action=clear" onclick="return confirm('আপনি কি নিশ্চিত পুরো কার্ট খালি করতে চান?');" style="color: var(--danger); font-size: 0.85rem; font-weight: 600;">
                        <i class="fa-solid fa-trash-can"></i> কার্ট খালি করুন
                    </a>
                </div>

                <div style="overflow-x: auto;">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>পণ্য</th>
                                <th>মূল্য (৳)</th>
                                <th style="text-align: center;">পরিমাণ</th>
                                <th style="text-align: right;">সাবটোটাল (৳)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <?php $p = $item['product']; ?>
                                <tr>
                                    <td>
                                        <div class="cart-product-cell">
                                            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="cart-thumb">
                                            <div>
                                                <a href="/product.php?id=<?= $p['id'] ?>" style="font-weight: 700; color: var(--secondary); font-size: 0.95rem;">
                                                    <?= htmlspecialchars($p['name']) ?>
                                                </a>
                                                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                                    <?= htmlspecialchars($p['category_name'] ?? '') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-weight: 600; color: var(--primary);">
                                        <?= format_bdt($item['unit_price']) ?>
                                    </td>
                                    <td>
                                        <form action="/cart.php" method="POST" class="qty-stepper" style="margin: 0 auto;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                            <button type="button" class="qty-btn qty-minus" onclick="this.nextElementSibling.stepDown(); this.form.submit();">-</button>
                                            <input type="number" name="quantity" class="qty-input" value="<?= $item['quantity'] ?>" min="1" max="<?= $p['stock'] ?>" onchange="this.form.submit()">
                                            <button type="button" class="qty-btn qty-plus" onclick="this.previousElementSibling.stepUp(); this.form.submit();">+</button>
                                        </form>
                                    </td>
                                    <td style="text-align: right; font-weight: 700; font-size: 1.05rem; color: var(--secondary);">
                                        <?= format_bdt($item['subtotal']) ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="/cart.php?action=remove&id=<?= $p['id'] ?>" class="btn-remove-item" title="সরিয়ে দিন">
                                            <i class="fa-solid fa-xmark"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <a href="/shop.php" style="color: var(--primary); font-weight: 600; font-size: 0.92rem;">
                        <i class="fa-solid fa-arrow-left"></i> আরও পণ্য কিনুন
                    </a>
                </div>
            </div>

            <!-- Right: Cart Summary Card -->
            <div class="cart-summary-card">
                <h3 style="font-size: 1.25rem; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    অর্ডারের সংক্ষিপ্ত বিবরণ
                </h3>

                <!-- Subtotal in ৳ -->
                <div class="summary-row">
                    <span>পণ্যের মোট মূল্য (Subtotal):</span>
                    <strong style="color: var(--secondary);"><?= format_bdt($subtotal) ?></strong>
                </div>

                <!-- Delivery Area Radio Switcher -->
                <div style="padding: 12px 0; border-bottom: 1px solid var(--border-light);">
                    <span style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 8px;">ডেলিভারি এরিয়া নির্বাচন করুন:</span>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.88rem; cursor: pointer;">
                            <input type="radio" name="delivery_area_select" value="inside" <?= $deliveryArea === 'Inside Dhaka' ? 'checked' : '' ?> onchange="location.href='/cart.php?area=inside'">
                            <span>ঢাকা সিটির ভেতরে (৳৭০)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.88rem; cursor: pointer;">
                            <input type="radio" name="delivery_area_select" value="outside" <?= $deliveryArea === 'Outside Dhaka' ? 'checked' : '' ?> onchange="location.href='/cart.php?area=outside'">
                            <span>ঢাকা সিটির বাইরে (৳১৩০)</span>
                        </label>
                    </div>
                </div>

                <!-- Delivery Fee in ৳ -->
                <div class="summary-row">
                    <span>ডেলিভারি চার্জ:</span>
                    <strong style="color: var(--primary);"><?= format_bdt($deliveryCharge) ?></strong>
                </div>

                <!-- Coupon Code Form & Discount in ৳ -->
                <div style="padding: 14px 0; border-bottom: 1px solid var(--border-light);">
                    <?php if ($appliedCoupon): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; background: #ecfdf5; border: 1px dashed #059669; padding: 8px 12px; border-radius: var(--radius-sm);">
                            <div>
                                <div style="font-weight: 700; color: #065f46; font-size: 0.88rem;"><?= htmlspecialchars($appliedCoupon['label']) ?></div>
                                <div style="font-size: 0.75rem; color: #047857;">কোড: <?= htmlspecialchars($appliedCoupon['code']) ?></div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <strong style="color: var(--danger);">-<?= format_bdt($discountAmount) ?></strong>
                                <a href="/cart.php?action=remove_coupon" style="color: var(--danger); font-size: 0.8rem;" title="কুপন মুছুন"><i class="fa-solid fa-xmark"></i></a>
                            </div>
                        </div>
                    <?php else: ?>
                        <form action="/cart.php" method="POST" style="display: flex; gap: 6px;">
                            <input type="hidden" name="action" value="apply_coupon">
                            <input type="text" name="coupon_code" class="form-control" placeholder="কুপন কোড (e.g. EID2026)" style="text-transform: uppercase; font-size: 0.85rem; padding: 8px 10px;" required>
                            <button type="submit" class="btn-buy-now" style="background: var(--secondary); color: #fff; padding: 8px 14px; font-size: 0.85rem;">প্রয়োগ</button>
                        </form>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">
                            কুপন ট্রাই করুন: <strong>EID2026</strong> (৳১৫০ ছাড়) অথবা <strong>BONGO100</strong> (৳১০০ ছাড়)
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Grand Total in ৳ BDT -->
                <div class="summary-row total">
                    <span>সর্বমোট মূল্য (Grand Total):</span>
                    <span style="color: var(--primary);"><?= format_bdt($grandTotal) ?></span>
                </div>

                <!-- Proceed to Checkout -->
                <a href="/checkout.php" class="btn-checkout" id="cart-checkout-btn">
                    অর্ডার করতে এগিয়ে যান <i class="fa-solid fa-arrow-right"></i>
                </a>

                <div style="margin-top: 14px; text-align: center; font-size: 0.8rem; color: var(--text-muted);">
                    <i class="fa-solid fa-shield-check text-emerald-600"></i> ১০০% নিরাপদ ক্যাশ অন ডেলিভারি
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
