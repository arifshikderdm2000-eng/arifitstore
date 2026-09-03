<?php
/**
 * Admin Categories Management
 * BongoStore BD
 */
$adminPageTitle = "ক্যাটাগরি ব্যবস্থাপনা - Arif Shikder IT Admin";
$adminHeaderTitle = "ক্যাটাগরি সমূহ ও নেভিগেশন";
$activeTab = 'categories';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db_connection();
$editingCategory = null;

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$delId]);
        set_flash('success', 'ক্যাটাগরি মুছে ফেলা হয়েছে।');
    } catch (Exception $e) {
        set_flash('danger', 'ক্যাটাগরি মুছতে সমস্যা হয়েছে: ' . $e->getMessage());
    }
    header("Location: /admin/categories.php");
    exit;
}

// Handle Edit Mode Load
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editingCategory = $stmt->fetch();
}

// Handle Form Submission (Add or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    $image = sanitize($_POST['image'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $editId = (int)($_POST['category_id'] ?? 0);

    if (empty($name)) {
        set_flash('danger', 'ক্যাটাগরির নাম লিখুন।');
    } else {
        if (empty($slug)) {
            $slug = slugify($name);
        }
        if (empty($image)) {
            $image = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80';
        }

        try {
            if ($editId > 0) {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, image = ?, description = ?, is_featured = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $image, $description, $isFeatured, $editId]);
                set_flash('success', 'ক্যাটাগরি সফলভাবে আপডেট করা হয়েছে!');
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, image, description, is_featured) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $image, $description, $isFeatured]);
                set_flash('success', 'নতুন ক্যাটাগরি তৈরি করা হয়েছে!');
            }
            header("Location: /admin/categories.php");
            exit;
        } catch (Exception $e) {
            set_flash('danger', 'ক্যাটাগরি সংরক্ষণ ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }
}

// Fetch all categories with count of products
$categories = [];
try {
    $stmt = $pdo->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.id ASC");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Left: Add / Edit Form -->
    <div class="admin-table-card" style="padding: 24px; height: fit-content;">
        <h3 style="font-size: 1.15rem; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-light);">
            <i class="fa-solid <?= $editingCategory ? 'fa-pen-to-square' : 'fa-plus-circle' ?> text-emerald-600"></i>
            <?= $editingCategory ? 'ক্যাটাগরি এডিট করুন' : 'নতুন ক্যাটাগরি যোগ করুন' ?>
        </h3>

        <form action="/admin/categories.php" method="POST">
            <?php if ($editingCategory): ?>
                <input type="hidden" name="category_id" value="<?= $editingCategory['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">ক্যাটাগরির নাম <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="যেমন: ঐতিহ্যবাহী পোশাক" value="<?= htmlspecialchars($editingCategory['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">স্লাগ / URL Identifier</label>
                <input type="text" name="slug" class="form-control" placeholder="যেমন: traditional-clothing" value="<?= htmlspecialchars($editingCategory['slug'] ?? '') ?>">
                <span style="font-size: 0.75rem; color: var(--text-muted);">খালি রাখলে নাম অনুযায়ী স্বয়ংক্রিয় তৈরি হবে।</span>
            </div>

            <div class="form-group">
                <label class="form-label">ছবির URL</label>
                <input type="url" name="image" class="form-control" placeholder="https://images.unsplash.com/..." value="<?= htmlspecialchars($editingCategory['image'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">বিবরণ</label>
                <textarea name="description" class="form-control" rows="2" placeholder="সংক্ষিপ্ত বিবরণ..."><?= htmlspecialchars($editingCategory['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 600;">
                    <input type="checkbox" name="is_featured" value="1" <?= (!empty($editingCategory) && $editingCategory['is_featured']) ? 'checked' : '' ?>>
                    <span>হোমপেজে ফিচার্ড হিসেবে প্রদর্শন করুন</span>
                </label>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn-checkout" style="background: var(--primary); padding: 9px 20px;">
                    <i class="fa-solid fa-check"></i> <?= $editingCategory ? 'আপডেট করুন' : 'যোগ করুন' ?>
                </button>
                <?php if ($editingCategory): ?>
                    <a href="/admin/categories.php" class="btn-buy-now" style="background: #e2e8f0; color: #334155; padding: 9px 14px;">
                        বাতিল
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Right: Categories List Table -->
    <div class="admin-table-card">
        <div class="admin-table-header">
            <h4><i class="fa-solid fa-layer-group text-emerald-600"></i> বর্তমান ক্যাটাগরি তালিকা (<?= count($categories) ?> টি)</h4>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ছবি</th>
                        <th>ক্যাটাগরির নাম</th>
                        <th>স্লাগ</th>
                        <th style="text-align: center;">পণ্য সংখ্যা</th>
                        <th>ফিচার্ড</th>
                        <th style="text-align: right;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($cat['image']) ?>" alt="" style="width: 44px; height: 44px; border-radius: 6px; object-fit: cover; border: 1px solid var(--border-color);">
                            </td>
                            <td>
                                <strong style="color: var(--secondary); font-size: 0.92rem;"><?= htmlspecialchars($cat['name']) ?></strong>
                            </td>
                            <td style="font-size: 0.8rem; color: var(--text-muted); font-family: monospace;">
                                <?= htmlspecialchars($cat['slug']) ?>
                            </td>
                            <td style="text-align: center;">
                                <a href="/admin/products.php?category_id=<?= $cat['id'] ?>" style="background: #f1f5f9; padding: 4px 10px; border-radius: 9999px; font-weight: 700; font-size: 0.8rem; color: var(--secondary);">
                                    <?= $cat['product_count'] ?> টি
                                </a>
                            </td>
                            <td>
                                <?= $cat['is_featured'] ? '<span class="stock-pill in-stock" style="font-size: 0.72rem;">হ্যাঁ</span>' : '<span style="color: var(--text-muted); font-size: 0.8rem;">না</span>' ?>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="/admin/categories.php?action=edit&id=<?= $cat['id'] ?>" class="admin-action-btn btn-edit" title="এডিট">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="/admin/categories.php?action=delete&id=<?= $cat['id'] ?>" class="admin-action-btn btn-delete" onclick="return confirm('আপনি কি নিশ্চিত এই ক্যাটাগরি মুছে ফেলতে চান?');" title="মুছুন">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media (max-width: 850px) {
    div[style*="grid-template-columns: 1fr 2fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
