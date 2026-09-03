<?php
/**
 * Customer Header Component
 * BongoStore BD
 */
require_once __DIR__ . '/functions.php';

$cartCount = get_cart_count();

// Fetch categories for top navigation
$pdo = get_db_connection();
$navCats = [];
try {
    $stmt = $pdo->query("SELECT id, name, slug FROM categories ORDER BY id ASC LIMIT 6");
    $navCats = $stmt->fetchAll();
} catch (Exception $e) {
    // Graceful fallback
}

$pageTitle = isset($pageTitle) ? $pageTitle . " | " . STORE_NAME : STORE_NAME . " - " . STORE_TAGLINE;
$activeNav = isset($activeNav) ? $activeNav : '';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="বাংলাদেশের বিশ্বস্ত অনলাইন শপিং স্টোর। ক্যাশ অন ডেলিভারি, দ্রুত ডেলিভারি ও অরিজিনাল পণ্যের নিশ্চয়তা।">
    
    <!-- Google Font: Hind Siliguri (Bangla & English) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<!-- Top Notice Bar -->
<div class="top-notice-bar">
    <div class="container top-notice-content">
        <div class="notice-delivery">
            <i class="fa-solid fa-truck-fast text-emerald-400"></i>
            <span>🚚 ডেলিভারি চার্জ: ঢাকা সিটিতে <strong>৳৭০</strong>, ঢাকার বাইরে <strong>৳১৩০</strong> | সারাদেশে ক্যাশ অন ডেলিভারি</span>
        </div>
        <div class="top-links">
            <span><i class="fa-solid fa-phone"></i> <?= STORE_PHONE ?></span>
            <a href="/admin/login.php" class="header-admin-link"><i class="fa-solid fa-lock"></i> অ্যাডমিন প্যানেল</a>
        </div>
    </div>
</div>

<!-- Main Sticky Header -->
<header class="main-header">
    <div class="container header-inner">
        <!-- Brand Logo -->
        <a href="/" class="brand-logo" id="header-brand-logo">
            <i class="fa-solid fa-laptop-code" style="color: var(--primary);"></i>
            <span>Arif Shikder <span style="color: var(--secondary); font-weight: 800;">IT Services</span></span>
        </a>

        <!-- Search Bar -->
        <div class="search-container">
            <form action="/shop.php" method="GET" class="search-form" id="global-search-form">
                <input type="text" name="q" class="search-input" placeholder="পণ্য খুঁজুন (যেমন: শাড়ি, পাঞ্জাবি, মধু, এয়ারবাডস)..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" autocomplete="off" required>
                <button type="submit" class="search-btn" aria-label="Search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>

        <!-- Header Actions -->
        <div class="header-actions">
            <!-- Shop All Link -->
            <a href="/shop.php" class="header-icon-btn d-none-mobile">
                <i class="fa-solid fa-store"></i>
                <span>সব পণ্য</span>
            </a>

            <!-- Cart Button -->
            <a href="/cart.php" class="header-icon-btn" id="header-cart-btn" aria-label="Shopping Cart">
                <i class="fa-solid fa-cart-shopping" style="font-size: 1.25rem;"></i>
                <span class="d-none-mobile">কার্ট</span>
                <span class="cart-counter" style="<?= $cartCount > 0 ? '' : 'display:none;' ?>"><?= $cartCount ?></span>
            </a>

            <!-- Mobile Hamburger Toggle -->
            <button class="mobile-toggle" id="mobile-menu-toggle" aria-label="Open Mobile Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- Category Sub-Nav Bar -->
    <nav class="nav-categories-bar">
        <div class="container">
            <ul class="nav-links">
                <li><a href="/" class="<?= $activeNav === 'home' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> হোম</a></li>
                <li><a href="/shop.php" class="<?= $activeNav === 'shop' ? 'active' : '' ?>"><i class="fa-solid fa-border-all"></i> সকল ক্যাটাগরি</a></li>
                <?php foreach ($navCats as $c): ?>
                    <li>
                        <a href="/shop.php?category=<?= urlencode($c['slug']) ?>" class="<?= (isset($_GET['category']) && $_GET['category'] === $c['slug']) ? 'active' : '' ?>">
                            <?= htmlspecialchars($c['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li><a href="/shop.php?filter=deals" style="color: var(--danger); font-weight: 700;"><i class="fa-solid fa-bolt"></i> স্পেশাল অফার</a></li>
            </ul>
        </div>
    </nav>
</header>

<!-- Mobile Navigation Drawer -->
<div id="drawer-backdrop" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 998; opacity: 0; pointer-events: none; transition: 0.3s ease;"></div>
<aside id="mobile-drawer" style="position: fixed; top: 0; left: -290px; width: 280px; height: 100vh; background: #fff; z-index: 999; box-shadow: var(--shadow-xl); transition: 0.3s ease; padding: 20px; display: flex; flex-direction: column;">
    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0; margin-bottom: 16px;">
        <span class="brand-logo" style="font-size: 1.15rem;"><i class="fa-solid fa-laptop-code text-emerald-600"></i> Arif Shikder IT</span>
        <button id="drawer-close-btn" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
    </div>
    <ul style="list-style: none; display: flex; flex-direction: column; gap: 14px; font-weight: 600;">
        <li><a href="/" style="display: flex; align-items: center; gap: 10px;"><i class="fa-solid fa-house text-emerald-600"></i> হোম</a></li>
        <li><a href="/shop.php" style="display: flex; align-items: center; gap: 10px;"><i class="fa-solid fa-store text-emerald-600"></i> সকল পণ্য</a></li>
        <li><a href="/cart.php" style="display: flex; align-items: center; gap: 10px;"><i class="fa-solid fa-cart-shopping text-emerald-600"></i> শপিং কার্ট (<?= $cartCount ?>)</a></li>
        <li><a href="/checkout.php" style="display: flex; align-items: center; gap: 10px;"><i class="fa-solid fa-credit-card text-emerald-600"></i> চেকআউট</a></li>
        <li style="border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 6px; font-size: 0.85rem; color: #64748b;">ক্যাটাগরি সমূহ</li>
        <?php foreach ($navCats as $c): ?>
            <li><a href="/shop.php?category=<?= urlencode($c['slug']) ?>" style="display: flex; align-items: center; gap: 8px; font-size: 0.92rem; color: #334155;"><i class="fa-solid fa-angle-right" style="font-size: 0.75rem; color: #047857;"></i> <?= htmlspecialchars($c['name']) ?></a></li>
        <?php endforeach; ?>
        <li style="border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 6px;">
            <a href="/admin/login.php" style="display: flex; align-items: center; gap: 10px; color: #b45309;"><i class="fa-solid fa-lock"></i> অ্যাডমিন লগইন</a>
        </li>
    </ul>
    <div style="margin-top: auto; padding-top: 20px; font-size: 0.82rem; color: #64748b; border-top: 1px solid #e2e8f0;">
        <div>হটলাইন: <?= STORE_PHONE ?></div>
        <div>ক্যাশ অন ডেলিভারি উপলব্ধ</div>
    </div>
</aside>

<style>
#mobile-drawer.open { left: 0 !important; }
#drawer-backdrop.active { opacity: 1 !important; pointer-events: auto !important; }
@media (max-width: 768px) {
    .d-none-mobile { display: none !important; }
}
</style>

<!-- Flash Alerts Container -->
<div class="container" style="margin-top: 16px;">
    <?php display_flash(); ?>
</div>
