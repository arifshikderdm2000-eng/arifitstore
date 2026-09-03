<?php
/**
 * Product Details Page
 * BongoStore BD
 */
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    header("Location: /shop.php");
    exit;
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    $custName = sanitize($_POST['customer_name'] ?? '');
    $custDistrict = sanitize($_POST['district'] ?? 'ঢাকা');
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $comment = sanitize($_POST['comment'] ?? '');

    if (!empty($custName) && !empty($comment)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (product_id, customer_name, district, rating, comment) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$productId, $custName, $custDistrict, $rating, $comment]);
            set_flash('success', 'আপনার মূল্যবান রিভিউ সফলভাবে যুক্ত হয়েছে!');
        } catch (Exception $e) {
            set_flash('danger', 'রিভিউ যুক্ত করতে সমস্যা হয়েছে।');
        }
    } else {
        set_flash('danger', 'অনুগ্রহ করে নাম এবং মতামত সঠিকভাবে লিখুন।');
    }
    header("Location: /product.php?id=" . $productId);
    exit;
}

// Fetch Product details
$product = null;
try {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
} catch (Exception $e) {}

if (!$product) {
    header("Location: /shop.php");
    exit;
}

// Images array
$images = [];
if (!empty($product['images'])) {
    $decoded = json_decode($product['images'], true);
    if (is_array($decoded)) {
        $images = $decoded;
    }
}
if (empty($images) && !empty($product['image'])) {
    $images = [$product['image']];
}

// Related products
$relatedProducts = [];
try {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' LIMIT 4");
    $stmt->execute([$product['category_id'], $productId]);
    $relatedProducts = $stmt->fetchAll();
} catch (Exception $e) {}

// Customer reviews for this product
$reviews = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY id DESC");
    $stmt->execute([$productId]);
    $reviews = $stmt->fetchAll();
} catch (Exception $e) {}

// Calculations
$regularPrice = (float)$product['price'];
$discountPrice = ($product['discount_price'] !== null && $product['discount_price'] > 0) ? (float)$product['discount_price'] : null;
$finalPrice = $discountPrice ?: $regularPrice;
$hasDiscount = $discountPrice !== null && $discountPrice < $regularPrice;
$savedAmount = $hasDiscount ? ($regularPrice - $discountPrice) : 0;
$percentOff = $hasDiscount ? round(($savedAmount / $regularPrice) * 100) : 0;
$inStock = (int)$product['stock'] > 0;

$pageTitle = $product['name'] . " - " . STORE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <!-- Breadcrumb -->
    <div style="padding: 16px 0 10px;">
        <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
            <a href="/" style="color: var(--primary);">হোম</a>
            <i class="fa-solid fa-angle-right" style="font-size: 0.7rem;"></i>
            <a href="/shop.php" style="color: var(--primary);">শপ</a>
            <i class="fa-solid fa-angle-right" style="font-size: 0.7rem;"></i>
            <a href="/shop.php?category=<?= urlencode($product['category_slug'] ?? '') ?>" style="color: var(--primary);"><?= htmlspecialchars($product['category_name'] ?? 'ক্যাটাগরি') ?></a>
            <i class="fa-solid fa-angle-right" style="font-size: 0.7rem;"></i>
            <span style="color: var(--text-main); font-weight: 600;"><?= htmlspecialchars($product['name']) ?></span>
        </div>
    </div>

    <!-- Main Product Details Grid -->
    <div class="product-details-grid">
        <!-- Gallery Column -->
        <div class="gallery-container">
            <div class="main-image-box">
                <img id="pdp-main-image" src="<?= htmlspecialchars($images[0] ?? $product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>

            <?php if (count($images) > 1): ?>
                <div class="thumbnail-row">
                    <?php foreach ($images as $index => $img): ?>
                        <button type="button" class="thumbnail-btn <?= $index === 0 ? 'active' : '' ?>" data-full-img="<?= htmlspecialchars($img) ?>" aria-label="Product image <?= $index + 1 ?>">
                            <img src="<?= htmlspecialchars($img) ?>" alt="Thumbnail <?= $index + 1 ?>">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Details & Order Controls Column -->
        <div class="product-info-col">
            <span class="product-cat" style="font-size: 0.88rem;"><?= htmlspecialchars($product['category_name'] ?? '') ?></span>
            <h1><?= htmlspecialchars($product['name']) ?></h1>

            <div class="product-meta-row">
                <div class="rating-stars" style="margin-bottom: 0;">
                    <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i>
                    <span>(<?= count($reviews) > 0 ? count($reviews) : 5 ?> কাস্টমার রিভিউ)</span>
                </div>
                <div style="color: var(--border-color);">|</div>
                <div>
                    <?php if ($inStock): ?>
                        <span class="stock-pill in-stock"><i class="fa-solid fa-circle-check"></i> ইন স্টক (<?= $product['stock'] ?> টি অবশিষ্ট)</span>
                    <?php else: ?>
                        <span class="stock-pill out-stock"><i class="fa-solid fa-circle-xmark"></i> স্টক শেষ</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($product['sku'])): ?>
                    <div style="color: var(--border-color);">|</div>
                    <div style="font-size: 0.82rem; color: var(--text-muted);">কোড: <strong><?= htmlspecialchars($product['sku']) ?></strong></div>
                <?php endif; ?>
            </div>

            <!-- Price Box in ৳ BDT -->
            <div class="product-price-box">
                <span class="price-large"><?= format_bdt($finalPrice) ?></span>
                <?php if ($hasDiscount): ?>
                    <span class="price-strike-large"><?= format_bdt($regularPrice) ?></span>
                    <span class="savings-tag">সাশ্রয় <?= format_bdt($savedAmount) ?> (-<?= $percentOff ?>%)</span>
                <?php endif; ?>
            </div>

            <!-- Short description -->
            <p style="color: var(--text-main); font-size: 0.96rem; line-height: 1.6; margin-bottom: 24px;">
                <?= nl2br(htmlspecialchars($product['short_description'] ?: $product['description'])) ?>
            </p>

            <!-- Quantity & Add to Cart / Buy Now Form -->
            <form action="/cart.php" method="POST" id="pdp-order-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                <div class="quantity-control-row">
                    <span style="font-weight: 700; font-size: 0.95rem; color: var(--secondary);">পরিমাণ:</span>
                    <div class="qty-stepper">
                        <button type="button" class="qty-btn qty-minus" aria-label="Decrease quantity">-</button>
                        <input type="number" name="quantity" id="pdp-qty-input" class="qty-input" value="1" min="1" max="<?= $product['stock'] ?>">
                        <button type="button" class="qty-btn qty-plus" aria-label="Increase quantity">+</button>
                    </div>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">আইটেম</span>
                </div>

                <div class="pdp-action-buttons">
                    <button type="button" class="btn-add-to-cart-large" onclick="addToCart(<?= $product['id'] ?>, document.getElementById('pdp-qty-input').value, this)" <?= !$inStock ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-cart-plus"></i> কার্টে যোগ করুন
                    </button>
                    <button type="button" class="btn-buy-now-large" onclick="window.location.href='/checkout.php?buy_now=<?= $product['id'] ?>&qty=' + document.getElementById('pdp-qty-input').value" <?= !$inStock ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-bolt"></i> সরাসরি অর্ডার করুন (Buy Now)
                    </button>
                </div>
            </form>

            <!-- Delivery & Service Guarantee Box -->
            <div class="delivery-features-card">
                <div class="delivery-item">
                    <i class="fa-solid fa-truck-fast"></i>
                    <div>
                        <strong>ডেলিভারি চার্জ:</strong> ঢাকা সিটিতে <strong>৳৭০</strong> (২৪-৪৮ ঘণ্টায় ডেলিভারি), ঢাকার বাইরে <strong>৳১৩০</strong> (৩-৫ কর্মদিবস)।
                    </div>
                </div>
                <div class="delivery-item">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <div>
                        <strong>পেমেন্ট পদ্ধতি:</strong> সম্পূর্ণ ক্যাশ অন ডেলিভারি (Cash on Delivery) সুবিধা।
                    </div>
                </div>
                <div class="delivery-item">
                    <i class="fa-solid fa-shield-check"></i>
                    <div>
                        <strong>১০০% অরিজিনাল পণ্য:</strong> ত্রুটিযুক্ত পণ্যের ক্ষেত্রে তাৎক্ষণিক রিপ্লেসমেন্ট গ্যারান্টি।
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Tabs: Full Description & Customer Reviews -->
    <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 30px; margin-bottom: 40px;">
        <h3 style="font-size: 1.3rem; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid var(--primary-light);">
            <i class="fa-solid fa-circle-info text-emerald-600"></i> বিস্তারিত বিবরণ (Product Description)
        </h3>
        <div style="font-size: 1rem; line-height: 1.8; color: var(--text-main); margin-bottom: 36px;">
            <?= nl2br(htmlspecialchars($product['description'])) ?>
        </div>

        <!-- Reviews Section -->
        <h3 style="font-size: 1.3rem; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid var(--primary-light);">
            <i class="fa-solid fa-comments text-emerald-600"></i> কাস্টমার রিভিউ (<?= count($reviews) ?>)
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
            <!-- Existing Reviews List -->
            <div>
                <?php if (empty($reviews)): ?>
                    <p style="color: var(--text-muted);">এখনও কোনো রিভিউ দেওয়া হয়নি। প্রথম রিভিউটি আপনি দিন!</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <?php foreach ($reviews as $rev): ?>
                            <div style="background: #f8fafc; border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <strong><?= htmlspecialchars($rev['customer_name']) ?> <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal;">(<?= htmlspecialchars($rev['district'] ?? 'ঢাকা') ?>)</span></strong>
                                    <div class="rating-stars" style="margin-bottom: 0;">
                                        <?php for ($i = 0; $i < (int)$rev['rating']; $i++): ?>
                                            <i class="fa-solid fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p style="font-size: 0.92rem; color: #334155; margin: 0;">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submit Review Form -->
            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px;">
                <h4 style="font-size: 1.05rem; margin-bottom: 14px;">আপনার মতামত বা রিভিউ দিন</h4>
                <form action="/product.php?id=<?= $productId ?>" method="POST">
                    <input type="hidden" name="action" value="submit_review">

                    <div class="form-group">
                        <label class="form-label">আপনার নাম <span class="required">*</span></label>
                        <input type="text" name="customer_name" class="form-control" placeholder="যেমন: তানভীর আহমেদ" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">আপনার জেলা</label>
                        <input type="text" name="district" class="form-control" placeholder="যেমন: ঢাকা / চট্টগ্রাম">
                    </div>

                    <div class="form-group">
                        <label class="form-label">রেটিং <span class="required">*</span></label>
                        <select name="rating" class="form-control">
                            <option value="5">⭐⭐⭐⭐⭐ (৫ স্টার - চমৎকার)</option>
                            <option value="4">⭐⭐⭐⭐ (৪ স্টার - খুব ভালো)</option>
                            <option value="3">⭐⭐⭐ (৩ স্টার - সন্তোষজনক)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">আপনার রিভিউ <span class="required">*</span></label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="পণ্যটি সম্পর্কে আপনার অনুভূতি লিখুন..." required></textarea>
                    </div>

                    <button type="submit" class="btn-buy-now" style="background: var(--primary); color: #fff; width: 100%; padding: 10px;">
                        রিভিউ সাবমিট করুন
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
        <section style="margin-bottom: 40px;">
            <div class="section-header">
                <div class="section-title-wrap">
                    <div class="section-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <h2 class="section-title">সম্পর্কিত অন্যান্য পণ্য (Related Products)</h2>
                </div>
            </div>

            <div class="products-grid">
                <?php foreach ($relatedProducts as $rel): ?>
                    <?php
                        $relRegPrice = (float)$rel['price'];
                        $relDiscPrice = ($rel['discount_price'] !== null && $rel['discount_price'] > 0) ? (float)$rel['discount_price'] : null;
                        $relFinalPrice = $relDiscPrice ?: $relRegPrice;
                        $relHasDisc = $relDiscPrice !== null && $relDiscPrice < $relRegPrice;
                    ?>
                    <div class="product-card">
                        <div class="product-thumb-wrap">
                            <a href="/product.php?id=<?= $rel['id'] ?>">
                                <img src="<?= htmlspecialchars($rel['image']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="product-details">
                            <span class="product-cat"><?= htmlspecialchars($rel['category_name'] ?? '') ?></span>
                            <h3 class="product-title">
                                <a href="/product.php?id=<?= $rel['id'] ?>"><?= htmlspecialchars($rel['name']) ?></a>
                            </h3>
                            <div class="product-price-row">
                                <span class="price-current"><?= format_bdt($relFinalPrice) ?></span>
                                <?php if ($relHasDisc): ?>
                                    <span class="price-old"><?= format_bdt($relRegPrice) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions-row">
                                <button type="button" class="btn-add-cart" onclick="addToCart(<?= $rel['id'] ?>, 1, this)">
                                    <i class="fa-solid fa-cart-plus"></i> কার্ট
                                </button>
                                <a href="/checkout.php?buy_now=<?= $rel['id'] ?>" class="btn-buy-now">
                                    কিনুন
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
