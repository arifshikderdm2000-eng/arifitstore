<?php
/**
 * Home Page
 * BongoStore BD
 */
$pageTitle = "হোম - বাংলাদেশের নির্ভরযোগ্য অনলাইন শপিং স্টোর";
$activeNav = 'home';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db_connection();

// 1. Featured Categories
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories WHERE is_featured = 1 ORDER BY id ASC LIMIT 5");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {}

// 2. Featured Products
$featuredProducts = [];
try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 AND p.status = 'active' ORDER BY p.id DESC LIMIT 8");
    $featuredProducts = $stmt->fetchAll();
} catch (Exception $e) {}

// 3. Best-Selling Products
$bestSellers = [];
try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_bestseller = 1 AND p.status = 'active' ORDER BY p.id DESC LIMIT 4");
    $bestSellers = $stmt->fetchAll();
} catch (Exception $e) {}

// 4. New Arrivals
$newArrivals = [];
try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_new = 1 AND p.status = 'active' ORDER BY p.id DESC LIMIT 4");
    $newArrivals = $stmt->fetchAll();
} catch (Exception $e) {}

// 5. Customer Reviews
$reviews = [];
try {
    $stmt = $pdo->query("SELECT r.*, p.name as product_name FROM reviews r LEFT JOIN products p ON r.product_id = p.id ORDER BY r.id DESC LIMIT 3");
    $reviews = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<main class="container">
    <!-- Hero Banner Section -->
    <section class="hero-section">
        <div class="hero-grid">
            <!-- Main Hero Card -->
            <div class="hero-banner-main">
                <span class="hero-tag"><i class="fa-solid fa-sparkles"></i> বৈশাখী ও ঈদ স্পেশাল অফার ২০২৬</span>
                <h1 class="hero-title">বাংলাদেশের খাঁটি পণ্য ও লেটেস্ট গ্যাজেট কিনুন ঘরে বসেই!</h1>
                <p class="hero-subtitle">ঢাকাই জামদানি, তসর সিল্ক পাঞ্জাবি, সুন্দরবনের প্রাকৃতিক মধু ও স্মার্ট গ্যাজেটস এখন সুলভ মূল্যে। সারাদেশে দ্রুত ক্যাশ অন ডেলিভারি।</p>
                <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
                    <a href="/shop.php" class="hero-cta-btn" id="hero-shop-now-btn">
                        <span>এখনই অর্ডার করুন</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <span style="font-weight: 600; font-size: 0.95rem; color: #a7f3d0;">
                        <i class="fa-solid fa-truck"></i> ঢাকা সিটিতে ৳৭০ | অন্যান্য জেলায় ৳১৩০
                    </span>
                </div>
            </div>

            <!-- Side Promotional Cards -->
            <div class="hero-side-banners">
                <div class="side-banner-card side-banner-1">
                    <div>
                        <span class="badge-tag badge-sale">স্পেশাল ডিল</span>
                        <h4>অরিজিনাল গ্যাজেটস কালেকশন</h4>
                        <p>নয়েজ ক্যানসেলিং TWS ও স্মার্টওয়াচে বিশেষ ছাড়।</p>
                    </div>
                    <a href="/shop.php?category=electronics-gadgets" style="color: #38bdf8; font-weight: 700; font-size: 0.88rem; display: flex; align-items: center; gap: 6px;">
                        কালেকশন দেখুন <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <div class="side-banner-card side-banner-2">
                    <div>
                        <span class="badge-tag badge-bestseller">১০০% খাঁটি</span>
                        <h4>সুন্দরবনের মধু ও গাওয়া ঘি</h4>
                        <p>সরাসরি উৎস থেকে সংগৃহীত নির্ভেজাল প্রাকৃতিক খাদ্য।</p>
                    </div>
                    <a href="/shop.php?category=pure-organic-food" style="color: #fef08a; font-weight: 700; font-size: 0.88rem; display: flex; align-items: center; gap: 6px;">
                        অর্ডার করুন <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Features Bar -->
    <section class="features-bar">
        <div class="features-grid">
            <div class="feature-box">
                <i class="fa-solid fa-hand-holding-dollar"></i>
                <div>
                    <h5>ক্যাশ অন ডেলিভারি</h5>
                    <p>পণ্য হাতে পেয়ে টাকা দিন</p>
                </div>
            </div>
            <div class="feature-box">
                <i class="fa-solid fa-map-location-dot"></i>
                <div>
                    <h5>৬৪ জেলায় ডেলিভারি</h5>
                    <p>বাংলাদেশের যেকোনো প্রান্তে</p>
                </div>
            </div>
            <div class="feature-box">
                <i class="fa-solid fa-shield-halved"></i>
                <div>
                    <h5>১০০% আসল পণ্য</h5>
                    <p>সেরা মানের বিশ্বস্ত নিশ্চয়তা</p>
                </div>
            </div>
            <div class="feature-box">
                <i class="fa-solid fa-arrow-rotate-left"></i>
                <div>
                    <h5>৭ দিনের রিটার্ন</h5>
                    <p>সহজ ও দ্রুত রিপ্লেসমেন্ট</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Grid -->
    <section>
        <div class="section-header">
            <div class="section-title-wrap">
                <div class="section-icon"><i class="fa-solid fa-layer-group"></i></div>
                <h2 class="section-title">জনপ্রিয় ক্যাটাগরি</h2>
            </div>
            <a href="/shop.php" class="section-link">সব দেখুন <i class="fa-solid fa-angle-right"></i></a>
        </div>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="/shop.php?category=<?= urlencode($cat['slug']) ?>" class="category-card" id="cat-card-<?= $cat['id'] ?>">
                    <img src="<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="category-img" loading="lazy">
                    <span class="category-name"><?= htmlspecialchars($cat['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Flash Deals Offer Section -->
    <section class="offer-banner-section">
        <div class="offer-banner-grid">
            <div class="offer-content">
                <span class="badge-tag badge-sale" style="margin-bottom: 12px; display: inline-block;">সীমিত সময়ের অফার</span>
                <h3>আজকের ধামাকা ফ্ল্যাশ সেল! ২০% থেকে ৫০% পর্যন্ত মূল্যছাড়</h3>
                <p>সেরা মানের ঐতিহ্যবাহী পোশাক ও স্মার্ট ডিভাইসে সীমিত সময়ের বিশেষ ছাড় উপভোগ করুন। অফার শেষ হতে আর মাত্র বাকি:</p>
                <div class="countdown-timer">
                    <div class="countdown-box">
                        <span class="countdown-number" id="cd-hours">14</span>
                        <span class="countdown-label">ঘণ্টা</span>
                    </div>
                    <div class="countdown-box">
                        <span class="countdown-number" id="cd-mins">35</span>
                        <span class="countdown-label">মিনিট</span>
                    </div>
                    <div class="countdown-box">
                        <span class="countdown-number" id="cd-secs">40</span>
                        <span class="countdown-label">সেকেন্ড</span>
                    </div>
                </div>
            </div>
            <div style="text-align: center;">
                <a href="/shop.php?filter=deals" class="hero-cta-btn" style="background: #ffffff; color: #047857; font-size: 1.1rem; padding: 15px 32px;">
                    <i class="fa-solid fa-bolt text-amber-500"></i> অফারটি লুফে নিন
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section>
        <div class="section-header">
            <div class="section-title-wrap">
                <div class="section-icon"><i class="fa-solid fa-star"></i></div>
                <h2 class="section-title">স্পেশাল ফিচার্ড পণ্যসমূহ</h2>
            </div>
            <a href="/shop.php" class="section-link">সকল পণ্য <i class="fa-solid fa-angle-right"></i></a>
        </div>

        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <?php
                    $regularPrice = (float)$product['price'];
                    $discountPrice = ($product['discount_price'] !== null && $product['discount_price'] > 0) ? (float)$product['discount_price'] : null;
                    $finalPrice = $discountPrice ?: $regularPrice;
                    $hasDiscount = $discountPrice !== null && $discountPrice < $regularPrice;
                    $percentOff = $hasDiscount ? round((($regularPrice - $discountPrice) / $regularPrice) * 100) : 0;
                ?>
                <div class="product-card" id="product-card-<?= $product['id'] ?>">
                    <div class="product-thumb-wrap">
                        <div class="badge-float-left">
                            <?php if ($hasDiscount): ?>
                                <span class="badge-tag badge-sale">-<?= $percentOff ?>% ছাড়</span>
                            <?php endif; ?>
                            <?php if ($product['is_new']): ?>
                                <span class="badge-tag badge-new">নতুন</span>
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
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star-half-stroke"></i>
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
                                <i class="fa-solid fa-cart-plus"></i> কার্টে যোগ করুন
                            </button>
                            <a href="/checkout.php?buy_now=<?= $product['id'] ?>" class="btn-buy-now">
                                এখনই কিনুন
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Best-Selling Products Section -->
    <section>
        <div class="section-header">
            <div class="section-title-wrap">
                <div class="section-icon"><i class="fa-solid fa-fire"></i></div>
                <h2 class="section-title">সর্বাধিক বিক্রিত পণ্য (Best Selling)</h2>
            </div>
            <a href="/shop.php?sort=popular" class="section-link">আরও দেখুন <i class="fa-solid fa-angle-right"></i></a>
        </div>

        <div class="products-grid">
            <?php foreach ($bestSellers as $product): ?>
                <?php
                    $regularPrice = (float)$product['price'];
                    $discountPrice = ($product['discount_price'] !== null && $product['discount_price'] > 0) ? (float)$product['discount_price'] : null;
                    $finalPrice = $discountPrice ?: $regularPrice;
                    $hasDiscount = $discountPrice !== null && $discountPrice < $regularPrice;
                    $percentOff = $hasDiscount ? round((($regularPrice - $discountPrice) / $regularPrice) * 100) : 0;
                ?>
                <div class="product-card">
                    <div class="product-thumb-wrap">
                        <div class="badge-float-left">
                            <span class="badge-tag badge-bestseller">বেস্ট সেলার</span>
                            <?php if ($hasDiscount): ?>
                                <span class="badge-tag badge-sale">-<?= $percentOff ?>%</span>
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
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
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
                                <i class="fa-solid fa-cart-plus"></i> কার্টে যোগ করুন
                            </button>
                            <a href="/checkout.php?buy_now=<?= $product['id'] ?>" class="btn-buy-now">
                                এখনই কিনুন
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Customer Reviews / Testimonials -->
    <section>
        <div class="section-header">
            <div class="section-title-wrap">
                <div class="section-icon"><i class="fa-solid fa-comments"></i></div>
                <h2 class="section-title">গ্রাহকদের বিশ্বস্ত মতামত</h2>
            </div>
        </div>

        <div class="reviews-grid">
            <?php foreach ($reviews as $rev): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div class="customer-avatar">
                            <?= mb_substr($rev['customer_name'], 0, 1, 'UTF-8') ?>
                        </div>
                        <div>
                            <div class="review-author"><?= htmlspecialchars($rev['customer_name']) ?></div>
                            <div class="review-location"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($rev['district']) ?></div>
                        </div>
                    </div>
                    <div class="rating-stars" style="margin-bottom: 8px;">
                        <?php for ($i = 0; $i < (int)$rev['rating']; $i++): ?>
                            <i class="fa-solid fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="review-comment">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Contact & Assistance Callout Section -->
    <section style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 32px; margin-bottom: 40px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div>
                <h3 style="font-size: 1.4rem; margin-bottom: 6px;">অর্ডারে কোনো সহায়তার প্রয়োজন?</h3>
                <p style="color: var(--text-muted); font-size: 0.95rem;">আমাদের কাস্টমার কেয়ার প্রতিনিধির সাথে কথা বলে সরাসরি ফোনেও অর্ডার কনফার্ম করতে পারেন।</p>
            </div>
            <div style="display: flex; gap: 14px; align-items: center;">
                <a href="tel:<?= str_replace([' ', '-'], '', STORE_PHONE) ?>" class="hero-cta-btn" style="background: #047857; color: #fff;">
                    <i class="fa-solid fa-phone"></i> কল করুন: <?= STORE_PHONE ?>
                </a>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
