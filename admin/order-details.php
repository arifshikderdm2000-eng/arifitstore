<?php
/**
 * Admin Order Details & Status Manager
 * BongoStore BD
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin_login();

$pdo = get_db_connection();
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    header("Location: /admin/orders.php");
    exit;
}

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order') {
    $newOrderStatus = sanitize($_POST['order_status'] ?? 'pending');
    $newPaymentStatus = sanitize($_POST['payment_status'] ?? 'unpaid');
    $adminNotes = sanitize($_POST['admin_notes'] ?? '');

    try {
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
        $stmt->execute([$newOrderStatus, $newPaymentStatus, $orderId]);
        set_flash('success', 'অর্ডারের স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে!');
    } catch (Exception $e) {
        set_flash('danger', 'স্ট্যাটাস আপডেট ব্যর্থ: ' . $e->getMessage());
    }
    header("Location: /admin/order-details.php?id=" . $orderId);
    exit;
}

// Fetch Order
$order = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
} catch (Exception $e) {}

if (!$order) {
    set_flash('danger', 'অর্ডারটি খুঁজে পাওয়া যায়নি।');
    header("Location: /admin/orders.php");
    exit;
}

// Fetch Items
$orderItems = [];
try {
    $stmt = $pdo->prepare("SELECT oi.*, p.image as current_image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll();
} catch (Exception $e) {}

$adminPageTitle = "অর্ডার বিবরণ: " . $order['order_number'] . " | Arif Shikder IT Admin";
$adminHeaderTitle = "অর্ডার বিবরণ ও ইনভয়েস";
$activeTab = 'orders';
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 1050px; margin: 0 auto;">
    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <a href="/admin/orders.php" style="color: var(--text-muted); font-size: 0.88rem;">
                <i class="fa-solid fa-arrow-left"></i> সকল অর্ডারে ফিরে যান
            </a>
            <h2 style="font-size: 1.45rem; color: var(--secondary); margin-top: 4px;">
                অর্ডার: <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                <span class="status-badge status-<?= $order['order_status'] ?>" style="font-size: 0.85rem; vertical-align: middle; margin-left: 8px;">
                    <?= get_status_label_bn($order['order_status']) ?>
                </span>
            </h2>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="button" class="btn-buy-now" onclick="window.print();" style="background: var(--secondary); color: #fff; padding: 9px 18px;">
                <i class="fa-solid fa-print"></i> ইনভয়েস প্রিন্ট
            </button>
            <a href="tel:<?= str_replace([' ', '-'], '', $order['customer_phone']) ?>" class="btn-checkout" style="background: #047857; padding: 9px 18px;">
                <i class="fa-solid fa-phone"></i> গ্রাহককে কল করুন
            </a>
        </div>
    </div>

    <!-- Main Grid: Info + Status Update Box -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 30px;">
        <!-- Left: Customer and Delivery Information -->
        <div class="admin-table-card" style="padding: 24px;">
            <h3 style="font-size: 1.15rem; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border-light);">
                <i class="fa-solid fa-address-card text-emerald-600"></i> গ্রাহকের বিস্তারিত তথ্য
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">পূর্ণ নাম:</div>
                    <div style="font-weight: 700; font-size: 1rem; color: var(--secondary);"><?= htmlspecialchars($order['customer_name']) ?></div>
                </div>

                <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">প্রাথমিক মোবাইল নম্বর:</div>
                    <div style="font-weight: 700; font-size: 1rem; color: #047857;">
                        <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>" style="color: inherit;">
                            <i class="fa-solid fa-phone"></i> <?= htmlspecialchars($order['customer_phone']) ?>
                        </a>
                    </div>
                </div>

                <?php if (!empty($order['customer_alt_phone'])): ?>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">বিকল্প মোবাইল:</div>
                        <div style="font-weight: 600; font-size: 0.95rem; color: var(--text-main);"><?= htmlspecialchars($order['customer_alt_phone']) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($order['customer_email'])): ?>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">ইমেইল:</div>
                        <div style="font-weight: 600; font-size: 0.95rem; color: var(--text-main);"><?= htmlspecialchars($order['customer_email']) ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div style="border-top: 1px solid var(--border-light); padding-top: 16px;">
                <h4 style="font-size: 0.95rem; margin-bottom: 8px; color: var(--secondary);">
                    <i class="fa-solid fa-location-dot text-emerald-600"></i> সম্পূর্ণ ডেলিভারি ঠিকানা
                </h4>
                <div style="background: #f8fafc; padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-light); font-size: 0.95rem; line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($order['delivery_address'])) ?>
                    <div style="margin-top: 8px; font-size: 0.85rem; color: var(--text-muted); border-top: 1px dashed var(--border-color); padding-top: 6px;">
                        জেলা: <strong><?= htmlspecialchars($order['district']) ?></strong> | এরিয়া: <strong><?= htmlspecialchars($order['delivery_area']) ?></strong> (ডেলিভারি চার্জ: <strong>৳<?= (int)$order['delivery_charge'] ?></strong>)
                    </div>
                </div>
            </div>

            <?php if (!empty($order['order_notes'])): ?>
                <div style="margin-top: 16px;">
                    <div style="font-size: 0.82rem; color: var(--text-muted);">গ্রাহকের বিশেষ নোট:</div>
                    <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 10px 14px; border-radius: var(--radius-md); font-size: 0.9rem; color: #92400e; margin-top: 4px;">
                        <?= nl2br(htmlspecialchars($order['order_notes'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Status Update Box -->
        <div class="admin-table-card" style="padding: 24px; height: fit-content;">
            <h3 style="font-size: 1.15rem; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border-light);">
                <i class="fa-solid fa-sliders text-emerald-600"></i> স্ট্যাটাস আপডেট
            </h3>

            <form action="/admin/order-details.php?id=<?= $orderId ?>" method="POST">
                <input type="hidden" name="action" value="update_order">

                <div class="form-group">
                    <label class="form-label">অর্ডার স্ট্যাটাস</label>
                    <select name="order_status" class="form-control" style="font-weight: 600;">
                        <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>অপেক্ষমান (Pending)</option>
                        <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : '' ?>>প্রসেসিং (Processing)</option>
                        <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>শিপড / কুরিয়ারে হস্তান্তর (Shipped)</option>
                        <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>ডেলিভারি সম্পন্ন (Delivered)</option>
                        <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>বাতিল (Cancelled)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">পেমেন্ট স্ট্যাটাস</label>
                    <select name="payment_status" class="form-control" style="font-weight: 600;">
                        <option value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'selected' : '' ?>>অপরিশোধিত (Unpaid - COD)</option>
                        <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>পরিশোধিত (Paid)</option>
                    </select>
                </div>

                <div style="background: #f8fafc; padding: 12px; border-radius: var(--radius-md); font-size: 0.85rem; margin-bottom: 16px;">
                    <div>পেমেন্ট মাধ্যম: <strong><?= strtoupper($order['payment_method']) === 'COD' ? 'ক্যাশ অন ডেলিভারি' : htmlspecialchars($order['payment_method']) ?></strong></div>
                    <div style="color: var(--text-muted); margin-top: 4px;">অর্ডার তারিখ: <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
                </div>

                <button type="submit" class="btn-checkout" style="width: 100%; justify-content: center; background: var(--primary);">
                    <i class="fa-solid fa-floppy-disk"></i> স্ট্যাটাস সংরক্ষণ করুন
                </button>
            </form>
        </div>
    </div>

    <!-- Ordered Items Table Card -->
    <div class="admin-table-card">
        <div class="admin-table-header">
            <h4><i class="fa-solid fa-boxes-packing text-emerald-600"></i> অর্ডারকৃত পণ্যসমূহ</h4>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ছবি</th>
                        <th>পণ্যের নাম</th>
                        <th>SKU</th>
                        <th style="text-align: right;">একক মূল্য (৳)</th>
                        <th style="text-align: center;">পরিমাণ</th>
                        <th style="text-align: right;">মোট মূল্য (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['current_image'])): ?>
                                    <img src="<?= htmlspecialchars($item['current_image']) ?>" alt="" style="width: 44px; height: 44px; border-radius: 6px; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 44px; height: 44px; background: #e2e8f0; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #94a3b8;"><i class="fa-solid fa-box"></i></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: var(--secondary); font-size: 0.95rem;"><?= htmlspecialchars($item['product_name']) ?></strong>
                            </td>
                            <td style="font-family: monospace; font-size: 0.85rem; color: var(--text-muted);">
                                <?= htmlspecialchars($item['product_sku'] ?? 'N/A') ?>
                            </td>
                            <td style="text-align: right; font-weight: 600;">
                                <?= format_bdt($item['unit_price']) ?>
                            </td>
                            <td style="text-align: center; font-weight: 700;">
                                <?= $item['quantity'] ?>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: var(--secondary);">
                                <?= format_bdt($item['subtotal']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: right; font-weight: 600;">পণ্যসমূহের মূল্য (Subtotal):</td>
                        <td style="text-align: right; font-weight: 700;"><?= format_bdt($order['subtotal']) ?></td>
                    </tr>
                    <tr>
                        <td colspan="5" style="text-align: right; font-weight: 600;">ডেলিভারি চার্জ (<?= htmlspecialchars($order['delivery_area']) ?>):</td>
                        <td style="text-align: right; font-weight: 700; color: var(--primary);"><?= format_bdt($order['delivery_charge']) ?></td>
                    </tr>
                    <?php if ((float)$order['discount_amount'] > 0): ?>
                        <tr>
                            <td colspan="5" style="text-align: right; font-weight: 600; color: var(--danger);">কুপন ডিসকাউন্ট:</td>
                            <td style="text-align: right; font-weight: 700; color: var(--danger);">-<?= format_bdt($order['discount_amount']) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr style="font-size: 1.15rem; background: #f1f5f9;">
                        <td colspan="5" style="text-align: right; font-weight: 800; color: var(--secondary);">সর্বমোট প্রদেয় বিল (Grand Total):</td>
                        <td style="text-align: right; font-weight: 800; color: var(--primary);"><?= format_bdt($order['total_amount']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
@media (max-width: 850px) {
    div[style*="grid-template-columns: 2fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
