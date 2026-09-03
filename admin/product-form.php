<?php
/**
 * Admin Add / Edit Product Form
 * BongoStore BD
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin_login();

$pdo = get_db_connection();
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEditing = $productId > 0;

$product = [
    'name' => '',
    'slug' => '',
    'category_id' => 1,
    'price' => '',
    'discount_price' => '',
    'stock' => 10,
    'sku' => '',
    'short_description' => '',
    'description' => '',
    'image' => '',
    'images' => '',
    'status' => 'active',
    'is_featured' => 0,
    'is_bestseller' => 0,
    'is_new' => 1
];

if ($isEditing) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $existing = $stmt->fetch();
    if ($existing) {
        $product = $existing;
    } else {
        set_flash('danger', 'পণ্যটি খুঁজে পাওয়া যায়নি।');
        header("Location: /admin/products.php");
        exit;
    }
}

// Categories list
$categories = [];
try {
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {}

$errors = [];

// Form submission handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 1);
    $price = (float)($_POST['price'] ?? 0);
    $discountPrice = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $sku = sanitize($_POST['sku'] ?? '');
    $shortDesc = sanitize($_POST['short_description'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');
    $image = sanitize($_POST['image'] ?? '');
    $galleryUrlsRaw = trim($_POST['gallery_urls'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $isNew = isset($_POST['is_new']) ? 1 : 0;

    // Validate
    if (empty($name)) {
        $errors[] = 'পণ্যের নাম আবশ্যক।';
    }
    if ($price <= 0) {
        $errors[] = 'পণ্যের সঠিক মূল্য (৳ BDT) দিন।';
    }
    if (empty($image)) {
        $image = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80';
    }

    // Process gallery images into JSON array
    $imagesArray = [$image];
    if (!empty($galleryUrlsRaw)) {
        $lines = preg_split('/[\r\n,]+/', $galleryUrlsRaw);
        foreach ($lines as $line) {
            $cleaned = trim($line);
            if (!empty($cleaned) && !in_array($cleaned, $imagesArray)) {
                $imagesArray[] = $cleaned;
            }
        }
    }
    $imagesJson = json_encode($imagesArray);

    $slug = slugify($name);
    if (empty($sku)) {
        $sku = 'BD-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    if (empty($errors)) {
        try {
            if ($isEditing) {
                $stmt = $pdo->prepare("
                    UPDATE products SET
                        name = ?, slug = ?, category_id = ?, price = ?, discount_price = ?,
                        stock = ?, sku = ?, short_description = ?, description = ?,
                        image = ?, images = ?, status = ?, is_featured = ?, is_bestseller = ?, is_new = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $name, $slug, $categoryId, $price, $discountPrice,
                    $stock, $sku, $shortDesc, $desc,
                    $image, $imagesJson, $status, $isFeatured, $isBestseller, $isNew,
                    $productId
                ]);
                set_flash('success', 'পণ্যটি সফলভাবে আপডেট করা হয়েছে!');
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO products (
                        name, slug, category_id, price, discount_price, stock, sku,
                        short_description, description, image, images, status,
                        is_featured, is_bestseller, is_new
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, ?
                    )
                ");
                $stmt->execute([
                    $name, $slug, $categoryId, $price, $discountPrice, $stock, $sku,
                    $shortDesc, $desc, $image, $imagesJson, $status,
                    $isFeatured, $isBestseller, $isNew
                ]);
                set_flash('success', 'নতুন পণ্য সফলভাবে তালিকায় যোগ করা হয়েছে!');
            }

            header("Location: /admin/products.php");
            exit;
        } catch (Exception $e) {
            $errors[] = 'ডাটাবেজে পণ্য সংরক্ষণ করতে সমস্যা হয়েছে: ' . $e->getMessage();
        }
    }
}

// Convert existing images array to textarea value
$galleryText = '';
if (!empty($product['images'])) {
    $arr = json_decode($product['images'], true);
    if (is_array($arr)) {
        // filter out main image if present
        $extras = array_filter($arr, fn($img) => $img !== $product['image']);
        $galleryText = implode("\n", $extras);
    }
}

$adminPageTitle = ($isEditing ? "পণ্য সম্পাদনা" : "নতুন পণ্য যোগ") . " | Arif Shikder IT Admin";
$adminHeaderTitle = $isEditing ? "পণ্য তথ্য সম্পাদনা (Edit Product)" : "নতুন পণ্য যুক্ত করুন (Add Product)";
$activeTab = $isEditing ? 'products' : 'product-add';
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 900px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-size: 1.4rem; color: var(--secondary);">
            <i class="fa-solid <?= $isEditing ? 'fa-pen-to-square' : 'fa-plus-circle' ?> text-emerald-600"></i>
            <?= $isEditing ? 'পণ্য সম্পাদনা: ' . htmlspecialchars($product['name']) : 'নতুন পণ্য যুক্ত করার ফর্ম' ?>
        </h2>
        <a href="/admin/products.php" style="color: var(--text-muted); font-size: 0.88rem;">
            <i class="fa-solid fa-arrow-left"></i> পণ্য তালিকায় ফিরে যান
        </a>
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

    <div class="admin-table-card" style="padding: 28px;">
        <form action="" method="POST">
            <!-- Basic Details -->
            <div class="form-group">
                <label class="form-label">পণ্যের পুরো নাম <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="যেমন: ঢাকাই ঐতিহ্যবাহী জামদানি শাড়ি" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">ক্যাটাগরি <span class="required">*</span></label>
                    <select name="category_id" class="form-control" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (int)$product['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">SKU / পণ্য কোড</label>
                    <input type="text" name="sku" class="form-control" placeholder="যেমন: JAM-001" value="<?= htmlspecialchars($product['sku']) ?>">
                </div>
            </div>

            <!-- Pricing in ৳ BDT and Stock -->
            <div style="background: #f8fafc; border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 18px; margin-bottom: 20px;">
                <div style="font-weight: 700; color: var(--secondary); margin-bottom: 12px; font-size: 0.95rem;">
                    <i class="fa-solid fa-bangladeshi-taka-sign text-emerald-600"></i> মূল্য (৳ BDT) ও ইনভেন্টরি স্টক
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">নিয়মিত মূল্য (Regular Price ৳) <span class="required">*</span></label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="যেমন: 4500" value="<?= htmlspecialchars($product['price']) ?>" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">ছাড়ের মূল্য (Discount Price ৳)</label>
                        <input type="number" step="0.01" name="discount_price" class="form-control" placeholder="যেমন: 3800 (না থাকলে খালি রাখুন)" value="<?= htmlspecialchars($product['discount_price'] ?? '') ?>">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">স্টক পরিমাণ (Stock Quantity) <span class="required">*</span></label>
                        <input type="number" name="stock" class="form-control" placeholder="যেমন: 15" value="<?= htmlspecialchars($product['stock']) ?>" min="0" required>
                    </div>
                </div>
            </div>

            <!-- Images (Multiple Images Support) -->
            <div class="form-group">
                <label class="form-label">প্রধান ছবির লিংক (Primary Image URL) <span class="required">*</span></label>
                <input type="url" name="image" class="form-control" placeholder="https://images.unsplash.com/..." value="<?= htmlspecialchars($product['image']) ?>" required>
                <span style="font-size: 0.78rem; color: var(--text-muted);">পণ্যের প্রধান ছবি হিসেবে ক্যাশিয়ার ও ক্যাটালগে প্রদর্শিত হবে।</span>
            </div>

            <div class="form-group">
                <label class="form-label">অতিরিক্ত ছবির লিংকসমূহ (Gallery Images - প্রতি লাইনে একটি করে URL)</label>
                <textarea name="gallery_urls" class="form-control" rows="3" placeholder="https://images.unsplash.com/photo-1...&#10;https://images.unsplash.com/photo-2..."><?= htmlspecialchars($galleryText) ?></textarea>
                <span style="font-size: 0.78rem; color: var(--text-muted);">প্রোডাক্ট ডিটেইলস পেজে গ্যালারি থাম্বনেইল হিসেবে প্রদর্শিত হবে।</span>
            </div>

            <!-- Descriptions -->
            <div class="form-group">
                <label class="form-label">সংক্ষিপ্ত বিবরণ (Short Description)</label>
                <input type="text" name="short_description" class="form-control" placeholder="পণ্যটির ১-২ লাইনের মূল আকর্ষণ..." value="<?= htmlspecialchars($product['short_description']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label">পূর্ণ বিবরণ (Full Product Description)</label>
                <textarea name="description" class="form-control" rows="5" placeholder="পণ্যের উপাদান, সাইজ, গুণাগুণ ও ব্যবহারের নিয়ম বিস্তারিত লিখুন..."><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <!-- Status & Flags -->
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; align-items: center; border-top: 1px solid var(--border-light); padding-top: 20px; margin-top: 20px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">পণ্য স্ট্যাটাস</label>
                    <select name="status" class="form-control">
                        <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>সক্রিয় (Active)</option>
                        <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>নিষ্ক্রিয় (Inactive)</option>
                    </select>
                </div>

                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 600;">
                        <input type="checkbox" name="is_featured" value="1" <?= $product['is_featured'] ? 'checked' : '' ?>>
                        <span>হোমপেজ ফিচার্ড</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 600;">
                        <input type="checkbox" name="is_bestseller" value="1" <?= $product['is_bestseller'] ? 'checked' : '' ?>>
                        <span>বেস্ট সেলার পণ্য</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 600;">
                        <input type="checkbox" name="is_new" value="1" <?= $product['is_new'] ? 'checked' : '' ?>>
                        <span>নতুন সংযোজন (New)</span>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div style="margin-top: 28px; display: flex; justify-content: flex-end; gap: 14px;">
                <a href="/admin/products.php" class="btn-buy-now" style="background: #e2e8f0; color: #334155; padding: 10px 20px;">
                    বাতিল করুন
                </a>
                <button type="submit" class="btn-checkout" style="background: var(--primary); padding: 10px 28px; font-size: 0.95rem;">
                    <i class="fa-solid fa-floppy-disk"></i> <?= $isEditing ? 'পরিবর্তন সংরক্ষণ করুন' : 'পণ্য যোগ করুন' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
