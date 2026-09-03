<?php
/**
 * Admin Dashboard
 * BongoStore BD
 */
$adminPageTitle = "ড্যাশবোর্ড - Arif Shikder IT Admin";
$adminHeaderTitle = "ওভারভিউ ও সেলস ড্যাশবোর্ড";
$activeTab = 'dashboard';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db_connection();

// 1. Total Sales Amount in ৳
$totalSales = 0;
try {
    // Only count delivered or non-cancelled orders
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE order_status != 'cancelled'");
    $totalSales = (float)$stmt->fetchColumn();
} catch (Exception $e) {}

// 2. Total Orders Count
$totalOrders = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $totalOrders = (int)$stmt->fetchColumn();
} catch (Exception $e) {}

// 3. Pending Orders Count
$pendingOrders = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'");
    $pendingOrders = (int)$stmt->fetchColumn();
} catch (Exception $e) {}

// 4. Total Products Count
$totalProducts = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $totalProducts = (int)$stmt->fetchColumn();
} catch (Exception $e) {}

// 5. Recent Orders
$recentOrders = [];
try {
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 6");
    $recentOrders = $stmt->fetchAll();
} catch (Exception $e) {}

// 6. Low stock products alert
$lowStockProducts = [];
try {
    $stmt = $pdo->query("SELECT * FROM products WHERE stock <= 10 ORDER BY stock ASC LIMIT 4");
    $lowStockProducts = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<!-- Metric Cards Grid -->
<div class="stat-cards-grid">
    <!-- Total Sales in ৳ -->
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">মোট বিক্রয় (Total Sales)</span>
            <div class="stat-value" style="color: var(--primary);"><?= format_bdt($totalSales) ?></div>
            <div style="font-size: 0.78rem; color: #10b981; margin-top: 4px;">
                <i class="fa-solid fa-arrow-trend-up"></i> সম্পন্ন ও প্রক্রিয়াধীন অর্ডার
            </div>
        </div>
        <div class="stat-icon-wrap" style="background: #ecfdf5; color: #047857;">
            <i class="fa-solid fa-bangladeshi-taka-sign"></i>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">মোট অর্ডার (Total Orders)</span>
            <div class="stat-value"><?= number_format($totalOrders) ?></div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px;">
                সর্বমোট সংগৃহীত কাস্টমার অর্ডার
            </div>
        </div>
        <div class="stat-icon-wrap" style="background: #eff6ff; color: #2563eb;">
            <i class="fa-solid fa-cart-shopping"></i>
        </div>
    </div>

    <!-- Pending Orders -->
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">অপেক্ষমান অর্ডার (Pending)</span>
            <div class="stat-value" style="color: #b45309;"><?= number_format($pendingOrders) ?></div>
            <div style="font-size: 0.78rem; color: #f59e0b; margin-top: 4px;">
                <i class="fa-solid fa-clock"></i> ফোন কনফার্মেশন প্রয়োজন
            </div>
        </div>
        <div class="stat-icon-wrap" style="background: #fffbeb; color: #d97706;">
            <i class="fa-solid fa-bell"></i>
        </div>
    </div>

    <!-- Total Products -->
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">মোট পণ্য (Active Products)</span>
            <div class="stat-value"><?= number_format($totalProducts) ?></div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px;">
                ইনভেন্টরি ক্যাটালগ
            </div>
        </div>
        <div class="stat-icon-wrap" style="background: #fdf2f8; color: #db2777;">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
    </div>
</div>

<!-- Quick Action Shortcuts -->
<div style="margin-bottom: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
    <a href="/admin/product-form.php" class="btn-checkout" style="padding: 10px 18px; font-size: 0.9rem; background: var(--primary);">
        <i class="fa-solid fa-plus-circle"></i> নতুন পণ্য যুক্ত করুন
    </a>
    <a href="/admin/orders.php?status=pending" class="btn-buy-now" style="padding: 10px 18px; font-size: 0.9rem; background: #d97706; color: #fff;">
        <i class="fa-solid fa-clock"></i> অপেক্ষমান অর্ডারসমূহ দেখুন (<?= $pendingOrders ?>)
    </a>
    <a href="/admin/categories.php" class="btn-buy-now" style="padding: 10px 18px; font-size: 0.9rem; background: #334155; color: #fff;">
        <i class="fa-solid fa-layer-group"></i> ক্যাটাগরি ম্যানেজমেন্ট
    </a>
</div>

<!-- Dashboard Dual Column: Recent Orders & Stock Alert -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 30px;">
    <!-- Recent Orders Table -->
    <div class="admin-table-card">
        <div class="admin-table-header">
            <h4><i class="fa-solid fa-clock-rotate-left text-emerald-600"></i> সাম্প্রতিক অর্ডারসমূহ (Recent Orders)</h4>
            <a href="/admin/orders.php" style="color: var(--primary); font-size: 0.85rem; font-weight: 700;">সবগুলো দেখুন &rarr;</a>
        </div>

        <?php if (empty($recentOrders)): ?>
            <div style="padding: 30px; text-align: center; color: var(--text-muted);">এখনও কোনো অর্ডার আসেনি।</div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>অর্ডার নং</th>
                            <th>গ্রাহক ও মোবাইল</th>
                            <th>জেলা</th>
                            <th>সর্বমোট (৳)</th>
                            <th>স্ট্যাটাস</th>
                            <th>অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $ord): ?>
                            <tr>
                                <td>
                                    <a href="/admin/order-details.php?id=<?= $ord['id'] ?>" style="font-weight: 700; color: var(--primary);">
                                        <?= htmlspecialchars($ord['order_number']) ?>
                                    </a>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);"><?= date('d M, h:i A', strtotime($ord['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 600;"><?= htmlspecialchars($ord['customer_name']) ?></div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($ord['customer_phone']) ?></div>
                                </td>
                                <td>
                                    <span style="font-size: 0.82rem;"><?= htmlspecialchars($ord['district']) ?></span>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);"><?= htmlspecialchars($ord['delivery_area']) ?></div>
                                </td>
                                <td style="font-weight: 700; color: var(--secondary);">
                                    <?= format_bdt($ord['total_amount']) ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $ord['order_status'] ?>">
                                        <?= get_status_label_bn($ord['order_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/admin/order-details.php?id=<?= $ord['id'] ?>" class="admin-action-btn btn-view" title="অর্ডার দেখুন">
                                        <i class="fa-solid fa-eye"></i> বিস্তারিত
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Inventory / Low Stock Alert Card -->
    <div class="admin-table-card" style="height: fit-content;">
        <div class="admin-table-header">
            <h4><i class="fa-solid fa-triangle-exclamation text-amber-500"></i> লো-স্টক সতর্কতা</h4>
            <a href="/admin/products.php" style="color: var(--primary); font-size: 0.85rem; font-weight: 700;">সব পণ্য</a>
        </div>

        <div style="padding: 16px;">
            <?php if (empty($lowStockProducts)): ?>
                <div style="text-align: center; color: var(--text-muted); padding: 20px 0;">সব পণ্যের পর্যাপ্ত স্টক রয়েছে।</div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($lowStockProducts as $lp): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid var(--border-light);">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="<?= htmlspecialchars($lp['image']) ?>" alt="" style="width: 40px; height: 40px; border-radius: 6px; object-fit: cover;">
                                <div>
                                    <div style="font-size: 0.85rem; font-weight: 600; max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= htmlspecialchars($lp['name']) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--primary); font-weight: 700;">
                                        <?= format_bdt($lp['price']) ?>
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <span class="stock-pill out-stock" style="font-size: 0.75rem; padding: 2px 8px;">
                                    <?= $lp['stock'] ?> টি বাকি
                                </span>
                                <div>
                                    <a href="/admin/product-form.php?id=<?= $lp['id'] ?>" style="font-size: 0.75rem; color: #2563eb; font-weight: 600;">রিস্টক</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media (max-width: 900px) {
    div[style*="grid-template-columns: 2fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
