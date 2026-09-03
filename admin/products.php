<?php
/**
 * Admin Products Management
 * BongoStore BD
 */
$adminPageTitle = "পণ্য ব্যবস্থাপনা (Products) - Arif Shikder IT Admin";
$adminHeaderTitle = "পণ্য তালিকা ও ইনভেন্টরি";
$activeTab = 'products';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db_connection();

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$delId]);
        set_flash('success', 'পণ্যটি সফলভাবে মুছে ফেলা হয়েছে।');
    } catch (Exception $e) {
        set_flash('danger', 'পণ্য মুছতে সমস্যা হয়েছে: ' . $e->getMessage());
    }
    header("Location: /admin/products.php");
    exit;
}

// Search and Category Filter
$searchQuery = trim($_GET['q'] ?? '');
$filterCategory = (int)($_GET['category_id'] ?? 0);

$where = ["1=1"];
$params = [];

if (!empty($searchQuery)) {
    $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

if ($filterCategory > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $filterCategory;
}

$whereSql = implode(' AND ', $where);

$products = [];
try {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE {$whereSql} ORDER BY p.id DESC");
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (Exception $e) {}

// Categories for filter
$categories = [];
try {
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {}
?>

<!-- Header Actions Row -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <!-- Search form -->
        <form action="/admin/products.php" method="GET" style="display: flex; gap: 8px;">
            <input type="text" name="q" class="form-control" placeholder="পণ্যের নাম বা SKU..." value="<?= htmlspecialchars($searchQuery) ?>" style="width: 220px; padding: 8px 12px; font-size: 0.88rem;">
            <select name="category_id" class="form-control" style="width: 170px; padding: 8px 12px; font-size: 0.88rem;">
                <option value="0">সকল ক্যাটাগরি</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $filterCategory === (int)$cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-buy-now" style="background: var(--secondary); color: #fff; padding: 8px 14px; font-size: 0.88rem;">
                <i class="fa-solid fa-filter"></i> খুঁজুন
            </button>
            <?php if (!empty($searchQuery) || $filterCategory > 0): ?>
                <a href="/admin/products.php" class="btn-remove-item" title="ফিল্টার রিসেট" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div>
        <a href="/admin/product-form.php" class="btn-checkout" style="padding: 9px 18px; font-size: 0.9rem; background: var(--primary);">
            <i class="fa-solid fa-plus"></i> নতুন পণ্য যোগ করুন
        </a>
    </div>
</div>

<!-- Products Table Card -->
<div class="admin-table-card">
    <div class="admin-table-header">
        <h4><i class="fa-solid fa-boxes-stacked text-emerald-600"></i> মোট <?= count($products) ?> টি পণ্য</h4>
    </div>

    <?php if (empty($products)): ?>
        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
            কোনো পণ্য খুঁজে পাওয়া যায়নি। <a href="/admin/product-form.php" style="color: var(--primary); font-weight: 700;">নতুন পণ্য যোগ করুন</a>।
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">ছবি</th>
                        <th>পণ্যের নাম ও SKU</th>
                        <th>ক্যাটাগরি</th>
                        <th>নিয়মিত মূল্য (৳)</th>
                        <th>ছাড় মূল্য (৳)</th>
                        <th>স্টক</th>
                        <th>ব্যাজ</th>
                        <th>স্ট্যাটাস</th>
                        <th style="text-align: right;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
                            </td>
                            <td>
                                <a href="/product.php?id=<?= $p['id'] ?>" target="_blank" style="font-weight: 700; color: var(--secondary); font-size: 0.92rem;">
                                    <?= htmlspecialchars($p['name']) ?>
                                </a>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    SKU: <strong><?= htmlspecialchars($p['sku'] ?? 'N/A') ?></strong>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: var(--text-main); font-weight: 500;">
                                    <?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?>
                                </span>
                            </td>
                            <td style="font-weight: 600; color: var(--secondary);">
                                <?= format_bdt($p['price']) ?>
                            </td>
                            <td>
                                <?php if ($p['discount_price'] && $p['discount_price'] > 0): ?>
                                    <strong style="color: var(--danger); font-size: 0.95rem;"><?= format_bdt($p['discount_price']) ?></strong>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int)$p['stock'] <= 5): ?>
                                    <span class="stock-pill out-stock" style="font-size: 0.75rem;"><?= $p['stock'] ?> (Low)</span>
                                <?php else: ?>
                                    <span class="stock-pill in-stock" style="font-size: 0.75rem;"><?= $p['stock'] ?> টি</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                    <?php if ($p['is_featured']): ?>
                                        <span class="badge-tag badge-bestseller" style="font-size: 0.65rem; padding: 2px 6px;">ফিচার্ড</span>
                                    <?php endif; ?>
                                    <?php if ($p['is_bestseller']): ?>
                                        <span class="badge-tag badge-sale" style="font-size: 0.65rem; padding: 2px 6px;">বেস্টসেলার</span>
                                    <?php endif; ?>
                                    <?php if ($p['is_new']): ?>
                                        <span class="badge-tag badge-new" style="font-size: 0.65rem; padding: 2px 6px;">নতুন</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($p['status'] === 'active'): ?>
                                    <span class="status-badge status-delivered" style="font-size: 0.75rem; padding: 3px 8px;">সক্রিয়</span>
                                <?php else: ?>
                                    <span class="status-badge status-cancelled" style="font-size: 0.75rem; padding: 3px 8px;">নিষ্ক্রিয়</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="/admin/product-form.php?id=<?= $p['id'] ?>" class="admin-action-btn btn-edit" title="সম্পাদনা">
                                    <i class="fa-solid fa-pen-to-square"></i> এডিট
                                </a>
                                <a href="/admin/products.php?action=delete&id=<?= $p['id'] ?>" class="admin-action-btn btn-delete" onclick="return confirm('আপনি কি নিশ্চিত এই পণ্যটি মুছে ফেলতে চান?');" title="মুছে ফেলুন">
                                    <i class="fa-solid fa-trash"></i> ডিলিট
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
