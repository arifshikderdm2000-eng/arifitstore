<?php
/**
 * Core Helper Functions
 * BongoStore BD
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Format any price amount strictly into Bangladeshi Taka (৳ BDT)
 * Ensures NO dollar signs or USD anywhere in the entire platform.
 * Example: format_bdt(1500) => "৳1,500"
 */
function format_bdt($amount) {
    if ($amount === null || $amount === '') {
        $amount = 0;
    }
    return '৳' . number_format((float)$amount, 0, '.', ',');
}

/**
 * Clean & sanitize user input
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Set flash alert message for current/next request
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Display and clear flash message
 */
function display_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $alertClass = 'alert-' . ($flash['type'] === 'danger' ? 'danger' : $flash['type']);
        $iconClass = $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation';
        echo '<div class="alert ' . $alertClass . ' animate-fade-in" id="flash-alert">';
        echo '<div class="alert-content"><i class="fa-solid ' . $iconClass . '"></i> <span>' . htmlspecialchars($flash['message']) . '</span></div>';
        echo '<button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>';
        echo '</div>';
    }
}

/**
 * Check if admin is currently authenticated
 */
function is_admin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require admin authentication or redirect to login
 */
function require_admin() {
    if (!is_admin()) {
        header("Location: /admin/login.php");
        exit;
    }
}

// ==========================================
// SHOPPING CART SYSTEM
// ==========================================

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/**
 * Add a product to session cart
 */
function add_to_cart($product_id, $quantity = 1) {
    $product_id = (int)$product_id;
    $quantity = max(1, (int)$quantity);

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

/**
 * Update quantity of an item in cart
 */
function update_cart($product_id, $quantity) {
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;

    if ($quantity <= 0) {
        unset($_SESSION['cart'][$product_id]);
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

/**
 * Remove an item from cart
 */
function remove_from_cart($product_id) {
    $product_id = (int)$product_id;
    unset($_SESSION['cart'][$product_id]);
}

/**
 * Clear the entire cart
 */
function clear_cart() {
    $_SESSION['cart'] = [];
    unset($_SESSION['applied_coupon']);
}

/**
 * Get total quantity count of all items in cart
 */
function get_cart_count() {
    if (empty($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}

/**
 * Get full hydrated items in the cart from database
 */
function get_cart_details() {
    $items = [];
    $subtotal = 0;

    if (empty($_SESSION['cart'])) {
        return ['items' => [], 'subtotal' => 0];
    }

    $pdo = get_db_connection();
    $productIds = array_keys($_SESSION['cart']);
    if (empty($productIds)) {
        return ['items' => [], 'subtotal' => 0];
    }

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id IN ($placeholders)");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll();

    $productMap = [];
    foreach ($products as $p) {
        $productMap[$p['id']] = $p;
    }

    foreach ($_SESSION['cart'] as $pid => $qty) {
        if (isset($productMap[$pid])) {
            $p = $productMap[$pid];
            $unitPrice = ($p['discount_price'] !== null && $p['discount_price'] > 0) ? (float)$p['discount_price'] : (float)$p['price'];
            $itemSubtotal = $unitPrice * $qty;
            $subtotal += $itemSubtotal;

            $items[] = [
                'product' => $p,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $itemSubtotal
            ];
        }
    }

    return ['items' => $items, 'subtotal' => $subtotal];
}

/**
 * Valid coupons list
 */
function get_available_coupons() {
    return [
        'EID2026' => ['discount' => 150, 'min_order' => 1000, 'label' => 'ঈদ স্পেশাল ছাড় ৳১৫০'],
        'BONGO100' => ['discount' => 100, 'min_order' => 800, 'label' => 'বঙ্গস্টোর অফার ৳১০০'],
        'NEWUSER' => ['discount' => 50, 'min_order' => 500, 'label' => 'নতুন কাস্টমার ছাড় ৳৫০']
    ];
}

/**
 * Get 64 Districts of Bangladesh
 */
function get_bangladesh_districts() {
    return [
        'Dhaka' => 'ঢাকা (Dhaka)',
        'Chattogram' => 'চট্টগ্রাম (Chattogram)',
        'Sylhet' => 'সিলেট (Sylhet)',
        'Rajshahi' => 'রাজশাহী (Rajshahi)',
        'Khulna' => 'খুলনা (Khulna)',
        'Barishal' => 'বরিশাল (Barishal)',
        'Rangpur' => 'রংপুর (Rangpur)',
        'Mymensingh' => 'ময়মনসিংহ (Mymensingh)',
        'Gazipur' => 'গাজীপুর (Gazipur)',
        'Narayanganj' => 'নারায়ণগঞ্জ (Narayanganj)',
        'Cumilla' => 'কুমিল্লা (Cumilla)',
        'Bogura' => 'বগুড়া (Bogura)',
        'Coxs Bazar' => 'কক্সবাজার (Cox\'s Bazar)',
        'Jessore' => 'যশোর (Jessore)',
        'Tangail' => 'টাঙ্গাইল (Tangail)',
        'Brahmanbaria' => 'ব্রাহ্মণবাড়িয়া (Brahmanbaria)',
        'Narsingdi' => 'নরসিংদী (Narsingdi)',
        'Noakhali' => 'নোয়াখালী (Noakhali)',
        'Feni' => 'ফেনী (Feni)',
        'Kushtia' => 'কুষ্টিয়া (Kushtia)',
        'Dinajpur' => 'দিনাজপুর (Dinajpur)',
        'Faridpur' => 'ফরিদপুর (Faridpur)',
        'Jamalpur' => 'জামালপুর (Jamalpur)',
        'Pabna' => 'পাবনা (Pabna)',
        'Sirajganj' => 'সিরাজগঞ্জ (Sirajganj)',
        'Naogaon' => 'নওগাঁ (Naogaon)',
        'Manikganj' => 'মানিকগঞ্জ (Manikganj)',
        'Munshiganj' => 'মুন্সীগঞ্জ (Munshiganj)',
        'Madaripur' => 'মাদারীপুর (Madaripur)',
        'Gopalganj' => 'গোপালগঞ্জ (Gopalganj)',
        'Shariatpur' => 'শরীয়তপুর (Shariatpur)',
        'Kishoreganj' => 'কিশোরগঞ্জ (Kishoreganj)',
        'Netrokona' => 'নেত্রকোণা (Netrokona)',
        'Sherpur' => 'শেরপুর (Sherpur)',
        'Chandpur' => 'চাঁদপুর (Chandpur)',
        'Lakshmipur' => 'লক্ষ্মীপুর (Lakshmipur)',
        'Habiganj' => 'হবিগঞ্জ (Habiganj)',
        'Moulvibazar' => 'মৌলভীবাজার (Moulvibazar)',
        'Sunamganj' => 'সুনামগঞ্জ (Sunamganj)',
        'Natore' => 'নাটোর (Natore)',
        'Chapai Nawabganj' => 'চাঁপাইনবাবগঞ্জ (Chapai Nawabganj)',
        'Joypurhat' => 'জয়পুরহাট (Joypurhat)',
        'Kurigram' => 'কুড়িগ্রাম (Kurigram)',
        'Gaibandha' => 'গাইবান্ধা (Gaibandha)',
        'Lalmonirhat' => 'লালমনিরহাট (Lalmonirhat)',
        'Nilphamari' => 'নীলফামারী (Nilphamari)',
        'Panchagarh' => 'পঞ্চগড় (Panchagarh)',
        'Thakurgaon' => 'ঠাকুরগাঁও (Thakurgaon)',
        'Bagerhat' => 'বাগেরহাট (Bagerhat)',
        'Satkhira' => 'সাতক্ষীরা (Satkhira)',
        'Jhenaidah' => 'ঝিনাইদহ (Jhenaidah)',
        'Magura' => 'মাগুরা (Magura)',
        'Narail' => 'নড়াইল (Narail)',
        'Chuadanga' => 'চুয়াডাঙ্গা (Chuadanga)',
        'Meherpur' => 'মেহেরপুর (Meherpur)',
        'Patuakhali' => 'পটুয়াখালী (Patuakhali)',
        'Bhola' => 'ভোলা (Bhola)',
        'Pirojpur' => 'পিরোজপুর (Pirojpur)',
        'Jhalokati' => 'ঝালকাঠি (Jhalokati)',
        'Barguna' => 'বরগুনা (Barguna)',
        'Bandarban' => 'বান্দরবান (Bandarban)',
        'Khagrachhari' => 'খাগড়াছড়ি (Khagrachhari)',
        'Rangamati' => 'রাঙ্গামাটি (Rangamati)'
    ];
}

/**
 * Generate unique order tracking number
 * Example: BD-2609-8472
 */
function generate_order_number() {
    return 'BD-' . date('y') . rand(10, 99) . '-' . rand(1000, 9999);
}

/**
 * Check if admin is logged in
 */
function is_admin_logged_in() {
    return !empty($_SESSION['admin_user']) && isset($_SESSION['admin_user']['id']);
}

/**
 * Get current logged in admin data
 */
function get_logged_admin() {
    return $_SESSION['admin_user'] ?? [
        'id' => 1,
        'username' => 'admin',
        'name' => 'Administrator',
        'role' => 'admin'
    ];
}

/**
 * Enforce admin login on admin pages
 */
function require_admin_login() {
    if (!is_admin_logged_in()) {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => 'অ্যাডমিন প্যানেলে প্রবেশের জন্য অনুগ্রহ করে লগইন করুন।'
        ];
        header("Location: /admin/login.php");
        exit;
    }
}

/**
 * Translate order status to Bengali label
 */
function get_status_label_bn($status) {
    $map = [
        'pending' => 'অপেক্ষমান (Pending)',
        'Pending' => 'অপেক্ষমান (Pending)',
        'confirmed' => 'নিশ্চিত (Confirmed)',
        'Confirmed' => 'নিশ্চিত (Confirmed)',
        'processing' => 'প্রসেসিং (Processing)',
        'Processing' => 'প্রসেসিং (Processing)',
        'shipped' => 'শিপড / কুরিয়ারে (Shipped)',
        'Shipped' => 'শিপড / কুরিয়ারে (Shipped)',
        'delivered' => 'ডেলিভারি সম্পন্ন (Delivered)',
        'Delivered' => 'ডেলিভারি সম্পন্ন (Delivered)',
        'cancelled' => 'বাতিল (Cancelled)',
        'Cancelled' => 'বাতিল (Cancelled)',
    ];
    return $map[$status] ?? $status;
}
