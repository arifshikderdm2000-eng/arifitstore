<?php
/**
 * Order Success & Tracking Page
 * BongoStore BD
 */
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();
$orderNumber = isset($_GET['order_number']) ? trim($_GET['order_number']) : '';

if (empty($orderNumber)) {
    header("Location: /");
    exit;
}

// Fetch order
$order = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();
} catch (Exception $e) {}

if (!$order) {
    header("Location: /");
    exit;
}

// Fetch order items
$orderItems = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$order['id']]);
    $orderItems = $stmt->fetchAll();
} catch (Exception $e) {}

$pageTitle = "অর্ডার নিশ্চিতকরণ - " . $order['order_number'];
require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <div class="order-success-card">
        <!-- Success Icon -->
        <div class="success-icon-wrap">
            <i class="fa-solid fa-check"></i>
        </div>

        <h1 style="font-size: 1.8rem; margin-bottom: 8px; color: var(--secondary);">ধন্যবাদ! আপনার অর্ডারটি সফলভাবে গৃহীত হয়েছে</h1>
        <p style="color: var(--text-muted); max-width: 540px; margin: 0 auto 20px;">
            আমাদের কাস্টমার কেয়ার প্রতিনিধি শীঘ্রই আপনার সাথে <strong><?= htmlspecialchars($order['customer_phone']) ?></strong> নম্বরে যোগাযোগ করে অর্ডারটি কনফার্ম করবেন।
        </p>

        <!-- Tracking Badge -->
        <div class="order-number-badge">
            অর্ডার ট্র্যাকিং আইডি: <strong><?= htmlspecialchars($order['order_number']) ?></strong>
        </div>

        <!-- Status Pill -->
        <div style="margin-bottom: 30px;">
            <span class="status-badge status-<?= $order['order_status'] ?>" style="font-size: 0.95rem; padding: 8px 18px;">
                <i class="fa-solid fa-circle-dot"></i> স্ট্যাটাস: <?= get_status_label_bn($order['order_status']) ?>
            </span>
        </div>

        <!-- Order Information Summary Grid -->
        <div class="order-info-grid">
            <div class="order-info-box">
                <h5><i class="fa-solid fa-user text-emerald-600"></i> গ্রাহকের তথ্য</h5>
                <div style="font-weight: 700; color: var(--secondary); margin-bottom: 4px;"><?= htmlspecialchars($order['customer_name']) ?></div>
                <div style="color: var(--text-muted); font-size: 0.88rem;"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($order['customer_phone']) ?></div>
                <?php if (!empty($order['customer_alt_phone'])): ?>
                    <div style="color: var(--text-muted); font-size: 0.88rem;"><i class="fa-solid fa-phone-flip"></i> <?= htmlspecialchars($order['customer_alt_phone']) ?> (বিকল্প)</div>
                <?php endif; ?>
                <?php if (!empty($order['customer_email'])): ?>
                    <div style="color: var(--text-muted); font-size: 0.88rem;"><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($order['customer_email']) ?></div>
                <?php endif; ?>
            </div>

            <div class="order-info-box">
                <h5><i class="fa-solid fa-location-dot text-emerald-600"></i> ডেলিভারি ঠিকানা</h5>
                <div style="font-weight: 600; color: var(--text-main); margin-bottom: 4px;"><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></div>
                <div style="color: var(--text-muted); font-size: 0.88rem;">জেলা: <strong><?= htmlspecialchars($order['district']) ?></strong></div>
                <div style="color: var(--text-muted); font-size: 0.88rem;">এরিয়া: <?= htmlspecialchars($order['delivery_area']) ?></div>
            </div>

            <div class="order-info-box">
                <h5><i class="fa-solid fa-credit-card text-emerald-600"></i> পেমেন্ট মেথড</h5>
                <div style="font-weight: 700; color: #047857; margin-bottom: 4px;">
                    <i class="fa-solid fa-hand-holding-dollar"></i> <?= strtoupper($order['payment_method']) === 'COD' ? 'ক্যাশ অন ডেলিভারি (Cash on Delivery)' : htmlspecialchars($order['payment_method']) ?>
                </div>
                <div style="color: var(--text-muted); font-size: 0.85rem;">পেমেন্ট স্ট্যাটাস: <strong><?= $order['payment_status'] === 'paid' ? 'পরিশোধিত' : 'অপেক্ষমান (ডেলিভারিতে প্রদেয়)' ?></strong></div>
                <div style="color: var(--text-muted); font-size: 0.85rem;">তারিখ: <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
            </div>
        </div>

        <!-- Ordered Items Table -->
        <div style="margin-bottom: 30px; text-align: left;">
            <h4 style="font-size: 1.15rem; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 2px solid var(--primary-light);">
                অর্ডারকৃত পণ্যের তালিকা
            </h4>
            <div style="overflow-x: auto;">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>পণ্যের বিবরণ</th>
                            <th style="text-align: center;">পরিমাণ</th>
                            <th style="text-align: right;">একক মূল্য (৳)</th>
                            <th style="text-align: right;">মোট (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--secondary);"><?= htmlspecialchars($item['product_name']) ?></strong>
                                    <?php if (!empty($item['product_sku'])): ?>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">SKU: <?= htmlspecialchars($item['product_sku']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;"><?= $item['quantity'] ?></td>
                                <td style="text-align: right;"><?= format_bdt($item['unit_price']) ?></td>
                                <td style="text-align: right; font-weight: 700; color: var(--secondary);"><?= format_bdt($item['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right; font-weight: 600;">সাবটোটাল:</td>
                            <td style="text-align: right; font-weight: 700;"><?= format_bdt($order['subtotal']) ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: right; font-weight: 600;">ডেলিভারি চার্জ:</td>
                            <td style="text-align: right; font-weight: 700; color: var(--primary);"><?= format_bdt($order['delivery_charge']) ?></td>
                        </tr>
                        <?php if ((float)$order['discount_amount'] > 0): ?>
                            <tr>
                                <td colspan="3" style="text-align: right; font-weight: 600; color: var(--danger);">কুপন ছাড়:</td>
                                <td style="text-align: right; font-weight: 700; color: var(--danger);">-<?= format_bdt($order['discount_amount']) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr style="font-size: 1.15rem; background: #ecfdf5;">
                            <td colspan="3" style="text-align: right; font-weight: 800; color: var(--secondary);">সর্বমোট বিল (Total):</td>
                            <td style="text-align: right; font-weight: 800; color: var(--primary);"><?= format_bdt($order['total_amount']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Next Step Actions -->
        <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
            <button type="button" class="btn-buy-now" onclick="window.print();" style="background: var(--secondary); color: #fff; padding: 12px 24px;">
                <i class="fa-solid fa-print"></i> ইনভয়েস প্রিন্ট করুন
            </button>
            <a href="/shop.php" class="hero-cta-btn" style="background: var(--primary); color: #fff; padding: 12px 24px;">
                <i class="fa-solid fa-bag-shopping"></i> আরও শপিং করুন
            </a>
            <a href="tel:<?= str_replace([' ', '-'], '', STORE_PHONE) ?>" class="btn-add-cart" style="padding: 12px 24px; text-decoration: none;">
                <i class="fa-solid fa-headset"></i> কাস্টমার কেয়ারে কল দিন
            </a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
