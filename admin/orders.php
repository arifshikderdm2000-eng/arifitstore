<?php
/**
 * Admin Orders Management
 * BongoStore BD
 */
$adminPageTitle = "অর্ডার তালিকা (Orders) - Arif Shikder IT Admin";
$adminHeaderTitle = "কাস্টমার অর্ডার ব্যবস্থাপনা";
$activeTab = 'orders';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db_connection();

// Filter by status and search
$statusFilter = trim($_GET['status'] ?? '');
$search = trim($_GET['q'] ?? '');

$where = ["1=1"];
$params = [];

if (!empty($statusFilter)) {
    $where[] = "order_status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $where[] = "(order_number LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereSql = implode(' AND ', $where);

// Fetch orders
$orders = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE {$whereSql} ORDER BY id DESC");
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
} catch (Exception $e) {}

// Count orders for status pills
$counts = ['all' => 0, 'pending' => 0, 'processing' => 0, 'shipped' => 0, 'delivered' => 0, 'cancelled' => 0];
try {
    $cStmt = $pdo->query("SELECT order_status, COUNT(*) as cnt FROM orders GROUP BY order_status");
    $rawCounts = $cStmt->fetchAll();
    foreach ($rawCounts as $rc) {
        $st = $rc['order_status'];
        if (isset($counts[$st])) {
            $counts[$st] = (int)$rc['cnt'];
        }
        $counts['all'] += (int)$rc['cnt'];
    }
} catch (Exception $e) {}
?>

<!-- Filter Status Tabs -->
<div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px;">
    <a href="/admin/orders.php" class="btn-buy-now <?= empty($statusFilter) ? '' : '' ?>" style="background: <?= empty($statusFilter) ? 'var(--secondary)' : '#ffffff' ?>; color: <?= empty($statusFilter) ? '#ffffff' : '#334155' ?>; border: 1px solid var(--border-color); padding: 8px 16px; font-size: 0.85rem;">
        সকল অর্ডার (<?= $counts['all'] ?>)
    </a>
    <a href="/admin/orders.php?status=pending" class="btn-buy-now" style="background: <?= $statusFilter === 'pending' ? '#d97706' : '#ffffff' ?>; color: <?= $statusFilter === 'pending' ? '#ffffff' : '#334155' ?>; border: 1px solid var(--border-color); padding: 8px 16px; font-size: 0.85rem;">
        <i class="fa-solid fa-clock"></i> অপেক্ষমান (<?= $counts['pending'] ?>)
    </a>
    <a href="/admin/orders.php?status=processing" class="btn-buy-now" style="background: <?= $statusFilter === 'processing' ? '#2563eb' : '#ffffff' ?>; color: <?= $statusFilter === 'processing' ? '#ffffff' : '#334155' ?>; border: 1px solid var(--border-color); padding: 8px 16px; font-size: 0.85rem;">
        <i class="fa-solid fa-spinner"></i> প্রসেসিং (<?= $counts['processing'] ?>)
    </a>
    <a href="/admin/orders.php?status=shipped" class="btn-buy-now" style="background: <?= $statusFilter === 'shipped' ? '#9333ea' : '#ffffff' ?>; color: <?= $statusFilter === 'shipped' ? '#ffffff' : '#334155' ?>; border: 1px solid var(--border-color); padding: 8px 16px; font-size: 0.85rem;">
        <i class="fa-solid fa-truck-fast"></i> শিপড (<?= $counts['shipped'] ?>)
    </a>
    <a href="/admin/orders.php?status=delivered" class="btn-buy-now" style="background: <?= $statusFilter === 'delivered' ? '#047857' : '#ffffff' ?>; color: <?= $statusFilter === 'delivered' ? '#ffffff' : '#334155' ?>; border: 1px solid var(--border-color); padding: 8px 16px; font-size: 0.85rem;">
        <i class="fa-solid fa-check"></i> সম্পন্ন (<?= $counts['delivered'] ?>)
    </a>
    <a href="/admin/orders.php?status=cancelled" class="btn-buy-now" style="background: <?= $statusFilter === 'cancelled' ? '#ef4444' : '#ffffff' ?>; color: <?= $statusFilter === 'cancelled' ? '#ffffff' : '#334155' ?>; border: 1px solid var(--border-color); padding: 8px 16px; font-size: 0.85rem;">
        <i class="fa-solid fa-ban"></i> বাতিল (<?= $counts['cancelled'] ?>)
    </a>
</div>

<!-- Search Bar -->
<div style="background: #ffffff; padding: 14px 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <form action="/admin/orders.php" method="GET" style="display: flex; gap: 8px; width: 100%; max-width: 460px;">
        <?php if (!empty($statusFilter)): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
        <?php endif; ?>
        <input type="text" name="q" class="form-control" placeholder="অর্ডার আইডি, গ্রাহকের নাম বা মোবাইল নম্বর..." value="<?= htmlspecialchars($search) ?>" style="padding: 8px 14px; font-size: 0.88rem;">
        <button type="submit" class="btn-buy-now" style="background: var(--secondary); color: #fff; padding: 8px 16px;">খুঁজুন</button>
        <?php if (!empty($search)): ?>
            <a href="/admin/orders.php<?= !empty($statusFilter) ? '?status=' . $statusFilter : '' ?>" class="btn-remove-item" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid var(--border-color); border-radius: 6px;">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        <?php endif; ?>
    </form>

    <div style="font-size: 0.85rem; color: var(--text-muted);">
        মোট অর্ডার: <strong><?= count($orders) ?></strong> টি
    </div>
</div>

<!-- Orders Table -->
<div class="admin-table-card">
    <?php if (empty($orders)): ?>
        <div style="padding: 50px 20px; text-align: center; color: var(--text-muted);">
            <i class="fa-solid fa-receipt" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 12px;"></i>
            <h4>কোনো অর্ডার পাওয়া যায়নি</h4>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>অর্ডার নম্বর</th>
                        <th>তারিখ</th>
                        <th>গ্রাহকের নাম ও ফোন</th>
                        <th>ডেলিভারি ঠিকানা ও জেলা</th>
                        <th>মূল্য (৳ BDT)</th>
                        <th>পেমেন্ট</th>
                        <th>অর্ডার স্ট্যাটাস</th>
                        <th style="text-align: right;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>
                                <a href="/admin/order-details.php?id=<?= $o['id'] ?>" style="font-weight: 700; color: var(--primary); font-size: 0.95rem;">
                                    <?= htmlspecialchars($o['order_number']) ?>
                                </a>
                            </td>
                            <td style="font-size: 0.78rem; color: var(--text-muted); white-space: nowrap;">
                                <?= date('d M Y', strtotime($o['created_at'])) ?><br>
                                <?= date('h:i A', strtotime($o['created_at'])) ?>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--secondary);"><?= htmlspecialchars($o['customer_name']) ?></div>
                                <div style="font-size: 0.82rem; color: #047857; font-weight: 600;">
                                    <i class="fa-solid fa-phone"></i> <?= htmlspecialchars($o['customer_phone']) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.82rem; max-width: 220px; line-height: 1.3;">
                                    <?= htmlspecialchars(mb_substr($o['delivery_address'], 0, 45, 'UTF-8')) ?>...
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                    জেলা: <strong><?= htmlspecialchars($o['district']) ?></strong> (<?= htmlspecialchars($o['delivery_area']) ?>)
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 800; color: var(--secondary); font-size: 1rem;">
                                    <?= format_bdt($o['total_amount']) ?>
                                </div>
                                <div style="font-size: 0.72rem; color: var(--text-muted);">
                                    চার্জ: ৳<?= (int)$o['delivery_charge'] ?>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 0.78rem; font-weight: 600; display: block; color: #065f46;">
                                    <?= strtoupper($o['payment_method']) === 'COD' ? 'ক্যাশ অন ডেলিভারি' : htmlspecialchars($o['payment_method']) ?>
                                </span>
                                <span class="stock-pill <?= $o['payment_status'] === 'paid' ? 'in-stock' : 'out-stock' ?>" style="font-size: 0.68rem; padding: 1px 6px;">
                                    <?= $o['payment_status'] === 'paid' ? 'পরিশোধিত' : 'অপরিশোধিত' ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $o['order_status'] ?>">
                                    <?= get_status_label_bn($o['order_status']) ?>
                                </span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="/admin/order-details.php?id=<?= $o['id'] ?>" class="admin-action-btn btn-view">
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
