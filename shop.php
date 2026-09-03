<?php
/**
 * Shop Page
 * BongoStore BD - Products Catalog with Search, Category Filter, Price Filter, and Sorting
 */
$pageTitle = "সকল পণ্য ও সেবা | " . (defined('STORE_NAME') ? STORE_NAME : 'Arif Shikder IT Services');
$activeNav = 'shop';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db_connection();

// Filter parameters
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$selectedCategorySlug = isset($_GET['category']) ? trim($_GET['category']) : '';
$minPrice = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$sortBy = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$filterType = isset($_GET['filter']) ? trim($_GET['filter']) : '';

// Fetch all categories for sidebar filter
$categories = [];
try {
    $stmt = $pdo->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active' GROUP BY c.id ORDER BY c.name ASC");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {}

// Build Query
$where = ["p.status = 'active'"];
$params = [];

if (!empty($searchQuery)) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
    $searchTerm = "%{$searchQuery}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($selectedCategorySlug)) {
    $where[] = "c.slug = ?";
    $params[] = $selectedCategorySlug;
}

if ($minPrice > 0) {
    $where[] = "COALESCE(p.discount_price, p.price) >= ?";
    $params[] = $minPrice;
}

if ($maxPrice > 0) {
    $where[] = "COALESCE(p.discount_price, p.price) <= ?";
    $params[] = $maxPrice;
}

if ($filterType === 'deals') {
    $where[] = "p.discount_price IS NOT NULL AND p.discount_price < p.price";
}

$whereClause = implode(' AND ', $where);

// Sorting
$orderBy = "p.id DESC";
if ($sortBy === 'price_asc') {
    $orderBy = "COALESCE(p.discount_price, p.price) ASC";
} elseif ($sortBy === 'price_desc') {
    $orderBy = "COALESCE(p.discount_price, p.price) DESC";
} elseif ($sortBy === 'popular') {
    $orderBy = "p.is_bestseller DESC, p.id DESC";
} elseif ($sortBy === 'newest') {
    $orderBy = "p.id DESC";
}

$products = [];
try {
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE {$whereClause} ORDER BY {$orderBy}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    $products = [];
}
?>

<main class="container">
    <div style="padding: 18px 0 6px;">
        <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
            <a href="/" style="color: var(--primary);">হোম</a>
            <i class="fa-solid fa-angle-right" style="font-size: 0.7rem;"></i>
            <span>শপ ক্যাটালগ</span>
            <?php if (!empty($selectedCategorySlug)): ?>
                <i class="fa-solid fa-angle-right" style="font-size: 0.7rem;"></i>
                <span>ক্যাটাগরি</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="shop-layout">
        <!-- Sidebar Filters -->
        <aside class="shop-sidebar">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 1.15rem;"><i class="fa-solid fa-filter text-emerald-600"></i> ফিল্টার</h3>
                <a href="/shop.php" style="font-size: 0.8rem; color: var(--danger); font-weight: 600;">রিসেট</a>
            </div>

            <!-- Categories Filter -->
            <div class="filter-block">
                <div class="filter-title">ক্যাটাগরি</div>
                <ul class="category-filter-list">
                    <li>
                        <a href="/shop.php" class="<?= empty($selectedCategorySlug) ? 'active' : '' ?>">
                            <span>সকল পণ্য</span>
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="/shop.php?category=<?= urlencode($cat['slug']) ?>" class="<?= ($selectedCategorySlug === $cat['slug']) ? 'active' : '' ?>">
                                <span><?= htmlspecialchars($cat['name']) ?></span>
                                <span style="font-size: 0.78rem; color: var(--text-light);">(<?= $cat['product_count'] ?>)</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Price Filter Form -->
            <div class="filter-block">
                <div class="filter-title">মূল্য সীমা (৳ BDT)</div>
                <form action="/shop.php" method="GET" class="price-slider-wrap">
                    <?php if (!empty($selectedCategorySlug)): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategorySlug) ?>">
                    <?php endif; ?>
                    <?php if (!empty($searchQuery)): ?>
                        <input type="hidden" name="q" value="<?= htmlspecialchars($searchQuery) ?>">
                    <?php endif; ?>
                    <?php if (!empty($sortBy)): ?>
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sortBy) ?>">
                    <?php endif; ?>

                    <div class="price-inputs-row">
                        <input type="number" name="min_price" class="price-input-box" placeholder="নূন্যতম ৳" value="<?= $minPrice > 0 ? $minPrice : '' ?>" min="0">
                        <span>-</span>
                        <input type="number" name="max_price" class="price-input-box" placeholder="সর্বোচ্চ ৳" value="<?= $maxPrice > 0 ? $maxPrice : '' ?>" min="0">
                    </div>
                    <button type="submit" class="btn-buy-now" style="width: 100%; padding: 8px; font-size: 0.85rem; background: var(--primary); color: #fff;">
                        প্রয়োগ করুন
                    </button>
                </form>
            </div>

            <!-- Special Offer Filter -->
            <div class="filter-block">
                <div class="filter-title">বিশেষ অফার</div>
                <ul class="category-filter-list">
                    <li>
                        <a href="/shop.php?filter=deals" style="color: var(--danger); font-weight: 600;">
                            <i class="fa-solid fa-bolt"></i> মূল্যছাড় অফার সমূহ
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Delivery Info Callout -->
            <div style="background: var(--primary-light); border: 1px solid #a7f3d0; border-radius: var(--radius-md); padding: 14px; margin-top: 20px; font-size: 0.84rem;">
                <div style="font-weight: 700; color: var(--primary); margin-bottom: 4px;">
                    <i class="fa-solid fa-truck"></i> দ্রুত ডেলিভারি
                </div>
                <p style="color: #065f46; margin: 0;">ঢাকা সিটিতে ডেলিভারি চার্জ মাত্র <strong>৳৭০</strong>, ঢাকার বাইরে <strong>৳১৩০</strong>।</p>
            </div>
        </aside>

        <!-- Main Catalog Products Area -->
        <section>
            <!-- Topbar Sort and Summary -->
            <div class="shop-topbar">
                <div style="font-size: 0.95rem; color: var(--text-muted);">
                    মোট <strong><?= count($products) ?></strong> টি পণ্য পাওয়া গেছে
                    <?php if (!empty($searchQuery)): ?>
                        (অনুসন্ধান: "<strong><?= htmlspecialchars($searchQuery) ?></strong>")
                    <?php endif; ?>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <label for="shop-sort" style="font-size: 0.88rem; font-weight: 600;">সর্ট করুন:</label>
                    <select id="shop-sort" class="sort-select" onchange="location = this.value;">
                        <?php
                            $baseUrl = '/shop.php?';
                            $qParams = [];
                            if (!empty($searchQuery)) $qParams['q'] = $searchQuery;
                            if (!empty($selectedCategorySlug)) $qParams['category'] = $selectedCategorySlug;
                            if ($minPrice > 0) $qParams['min_price'] = $minPrice;
                            if ($maxPrice > 0) $qParams['max_price'] = $maxPrice;
                            if (!empty($filterType)) $qParams['filter'] = $filterType;

                            function makeSortUrl($params, $sortVal) {
                                $copy = $params;
                                $copy['sort'] = $sortVal;
                                return '/shop.php?' . http_build_query($copy);
                            }
                        ?>
                        <option value="<?= makeSortUrl($qParams, 'newest') ?>" <?= $sortBy === 'newest' ? 'selected' : '' ?>>সর্বশেষ সংযোজিত</option>
                        <option value="<?= makeSortUrl($qParams, 'popular') ?>" <?= $sortBy === 'popular' ? 'selected' : '' ?>>জনপ্রিয়তা অনুযায়ী</option>
                        <option value="<?= makeSortUrl($qParams, 'price_asc') ?>" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>মূল্য: কম থেকে বেশি (৳)</option>
                        <option value="<?= makeSortUrl($qParams, 'price_desc') ?>" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>মূল্য: বেশি থেকে কম (৳)</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <?php if (empty($products)): ?>
                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 50px 20px; text-align: center;">
                    <i class="fa-solid fa-box-open" style="font-size: 3.5rem; color: #cbd5e1; margin-bottom: 16px;"></i>
                    <h3 style="font-size: 1.3rem; margin-bottom: 8px;">কোনো পণ্য পাওয়া যায়নি</h3>
                    <p style="color: var(--text-muted); margin-bottom: 20px;">আপনার অনুসন্ধানের শর্তানুযায়ী কোনো পণ্য খুঁজে পাওয়া যায়নি। ফিল্টার পরিবর্তন করে আবার চেষ্টা করুন।</p>
                    <a href="/shop.php" class="btn-buy-now" style="background: var(--primary); color: #fff; padding: 10px 24px;">সকল পণ্য দেখুন</a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <?php
                            $regularPrice = (float)$product['price'];
                            $discountPrice = ($product['discount_price'] !== null && $product['discount_price'] > 0) ? (float)$product['discount_price'] : null;
                            $finalPrice = $discountPrice ?: $regularPrice;
                            $hasDiscount = $discountPrice !== null && $discountPrice < $regularPrice;
                            $percentOff = $hasDiscount ? round((($regularPrice - $discountPrice) / $regularPrice) * 100) : 0;
                        ?>
                        <div class="product-card" id="shop-product-<?= $product['id'] ?>">
                            <div class="product-thumb-wrap">
                                <div class="badge-float-left">
                                    <?php if ($hasDiscount): ?>
                                        <span class="badge-tag badge-sale">-<?= $percentOff ?>% ছাড়</span>
                                    <?php endif; ?>
                                    <?php if ($product['is_bestseller']): ?>
                                        <span class="badge-tag badge-bestseller">বেস্ট সেলার</span>
                                    <?php endif; ?>
                                </div>
                                <a href="/product.php?id=<?= $product['id'] ?>">
                                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                                </a>
                            </div>
                            <div class="product-details">
                                <span class="product-cat"><?= htmlspecialchars($product['category_name'] ?? 'General') ?></span>
                                <h3 class="product-title">
                                    <a href="/product.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a>
                                </h3>
                                <div class="rating-stars">
                                    <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i>
                                    <span>(5.0)</span>
                                </div>
                                <div class="product-price-row">
                                    <span class="price-current"><?= format_bdt($finalPrice) ?></span>
                                    <?php if ($hasDiscount): ?>
                                        <span class="price-old"><?= format_bdt($regularPrice) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-actions-row">
                                    <button type="button" class="btn-add-cart" onclick="addToCart(<?= $product['id'] ?>, 1, this)">
                                        <i class="fa-solid fa-cart-plus"></i> কার্ট
                                    </button>
                                    <a href="/checkout.php?buy_now=<?= $product['id'] ?>" class="btn-buy-now">
                                        কিনুন
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
