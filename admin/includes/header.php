<?php
/**
 * Admin Panel Header
 * BongoStore BD
 */
require_once __DIR__ . '/../../includes/functions.php';
require_admin_login();

$currentAdmin = get_logged_admin();
$adminPageTitle = isset($adminPageTitle) ? $adminPageTitle . " | Arif Shikder IT Admin" : "Arif Shikder IT Services Admin Panel";
$activeTab = $activeTab ?? 'dashboard';

// Pending orders count badge
$pendingOrdersCount = 0;
try {
    $pdo = get_db_connection();
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'");
    $pendingOrdersCount = (int)$stmt->fetchColumn();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminPageTitle) ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Main Style -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body style="background: #f1f5f9;">

<div class="admin-layout">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <a href="/admin/index.php" style="display: flex; align-items: center; gap: 10px; color: #fff; text-decoration: none;">
                <i class="fa-solid fa-laptop-code" style="color: #34d399; font-size: 1.4rem;"></i>
                <div>
                    <div style="font-weight: 800; font-size: 1.05rem; line-height: 1.2;">Arif Shikder IT</div>
                    <div style="font-size: 0.70rem; color: #94a3b8; letter-spacing: 0.5px;">ADMIN PANEL (৳ BDT)</div>
                </div>
            </a>
        </div>

        <ul class="admin-nav">
            <li class="admin-nav-item">
                <a href="/admin/index.php" class="admin-nav-link <?= $activeTab === 'dashboard' ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>ড্যাশবোর্ড (Dashboard)</span>
                </a>
            </li>

            <li class="admin-nav-item">
                <a href="/admin/orders.php" class="admin-nav-link <?= $activeTab === 'orders' ? 'active' : '' ?>">
                    <i class="fa-solid fa-cart-flatbed"></i>
                    <span>অর্ডার সমূহ (Orders)</span>
                    <?php if ($pendingOrdersCount > 0): ?>
                        <span class="badge-tag badge-sale" style="margin-left: auto; padding: 2px 8px; font-size: 0.72rem;"><?= $pendingOrdersCount ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="admin-nav-item">
                <a href="/admin/products.php" class="admin-nav-link <?= $activeTab === 'products' ? 'active' : '' ?>">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>পণ্য তালিকা (Products)</span>
                </a>
            </li>

            <li class="admin-nav-item">
                <a href="/admin/product-form.php" class="admin-nav-link <?= $activeTab === 'product-add' ? 'active' : '' ?>">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>নতুন পণ্য যোগ (Add Product)</span>
                </a>
            </li>

            <li class="admin-nav-item">
                <a href="/admin/categories.php" class="admin-nav-link <?= $activeTab === 'categories' ? 'active' : '' ?>">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>ক্যাটাগরি সমূহ</span>
                </a>
            </li>

            <li style="border-top: 1px solid rgba(255,255,255,0.1); margin: 16px 0; padding-top: 10px;"></li>

            <li class="admin-nav-item">
                <a href="/" target="_blank" class="admin-nav-link">
                    <i class="fa-solid fa-globe"></i>
                    <span>মূল ওয়েবসাইট দেখুন</span>
                </a>
            </li>

            <li class="admin-nav-item">
                <a href="/admin/logout.php" class="admin-nav-link" style="color: #f87171;">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>লগআউট</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div style="font-weight: 700; font-size: 1.1rem; color: var(--secondary); display: flex; align-items: center; gap: 8px;">
                <span><?= htmlspecialchars($adminHeaderTitle ?? 'অ্যাডমিন ড্যাশবোর্ড') ?></span>
            </div>

            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="background: #ecfdf5; border: 1px solid #059669; color: #065f46; padding: 4px 12px; border-radius: 9999px; font-weight: 700; font-size: 0.82rem;">
                    মুদ্রা: <strong>৳ BDT (টাকা)</strong>
                </div>
                <div style="font-size: 0.88rem; color: var(--text-main); font-weight: 600;">
                    <i class="fa-solid fa-circle-user text-emerald-600"></i> <?= htmlspecialchars($currentAdmin['name'] ?? 'Admin') ?>
                </div>
                <a href="/admin/logout.php" class="btn-buy-now" style="background: #ef4444; color: #fff; padding: 6px 12px; font-size: 0.8rem;">
                    লগআউট
                </a>
            </div>
        </header>

        <!-- Admin Content Container -->
        <div class="admin-content-area">
            <?php display_flash(); ?>
