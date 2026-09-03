<?php
/**
 * Database Configuration & Connection
 * Compatible with InfinityFree MySQL hosting & local SQLite fallback.
 */

// ==========================================
// INFINITYFREE MYSQL CONFIGURATION
// Replace these with your InfinityFree MySQL details from cPanel
// ==========================================
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');          // e.g. sql108.infinityfree.com or localhost
if (!defined('DB_USER')) define('DB_USER', 'root');               // e.g. if0_38000000
if (!defined('DB_PASS')) define('DB_PASS', '');                   // Your InfinityFree vPanel password
if (!defined('DB_NAME')) define('DB_NAME', 'bongo_store');        // e.g. if0_38000000_bongo_store

// Currency Configuration - STRICTLY BANGLADESHI TAKA
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', '৳');
if (!defined('CURRENCY_CODE')) define('CURRENCY_CODE', 'BDT');

// Store Info
if (!defined('STORE_NAME')) define('STORE_NAME', 'Arif Shikder IT Services');
if (!defined('STORE_TAGLINE')) define('STORE_TAGLINE', 'বিশ্বস্ত আইটি সল্যুশন, কম্পিউটার গ্যাজেট ও ডিজিটাল সেবা');
if (!defined('STORE_PHONE')) define('STORE_PHONE', '+880 1711-998877');
if (!defined('STORE_EMAIL')) define('STORE_EMAIL', 'arifshikderdm2000@gmail.com');
if (!defined('STORE_ADDRESS')) define('STORE_ADDRESS', 'Level 4, Concord Tower, 55 Motijheel C/A, Dhaka-1000, Bangladesh');

// Start PHP session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get PDO Database Connection
 * Tries MySQL first. If MySQL is not running or credentials fail,
 * it seamlessly initializes an embedded SQLite database for local preview/development.
 */
function get_db_connection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $isMySQL = false;

    // 1. Attempt MySQL connection
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 2,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $isMySQL = true;
    } catch (PDOException $e) {
        // MySQL not available (e.g. running in local container preview without MySQL daemon)
        // Fall back seamlessly to SQLite so the app runs out of the box!
        $sqliteFile = __DIR__ . '/../database.sqlite';
        $needsInit = !file_exists($sqliteFile) || filesize($sqliteFile) === 0;

        try {
            $pdo = new PDO("sqlite:" . $sqliteFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            if ($needsInit) {
                initialize_sqlite_database($pdo);
            }
        } catch (PDOException $sqle) {
            die("Database Connection Error: " . htmlspecialchars($sqle->getMessage()));
        }
    }

    return $pdo;
}

/**
 * Initialize SQLite tables and seed data for immediate local preview
 */
function initialize_sqlite_database($pdo) {
    // Admin users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL,
        role TEXT DEFAULT 'admin',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        icon TEXT,
        image TEXT,
        description TEXT,
        is_featured INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Products table
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        description TEXT,
        short_description TEXT,
        price REAL NOT NULL,
        discount_price REAL DEFAULT NULL,
        stock INTEGER DEFAULT 10,
        sku TEXT,
        image TEXT,
        images TEXT,
        is_featured INTEGER DEFAULT 0,
        is_new INTEGER DEFAULT 0,
        is_bestseller INTEGER DEFAULT 0,
        status TEXT DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Customers table
    $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        phone TEXT NOT NULL,
        email TEXT,
        address TEXT NOT NULL,
        district TEXT NOT NULL,
        total_orders INTEGER DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Orders table
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_number TEXT UNIQUE NOT NULL,
        customer_name TEXT NOT NULL,
        customer_phone TEXT NOT NULL,
        customer_alt_phone TEXT,
        customer_email TEXT,
        address TEXT NOT NULL,
        delivery_address TEXT,
        district TEXT NOT NULL,
        delivery_area TEXT NOT NULL,
        subtotal REAL DEFAULT 0,
        delivery_charge REAL NOT NULL,
        discount_amount REAL DEFAULT 0,
        total_amount REAL NOT NULL,
        payment_method TEXT DEFAULT 'Cash on Delivery',
        payment_status TEXT DEFAULT 'unpaid',
        order_notes TEXT,
        status TEXT DEFAULT 'Pending',
        order_status TEXT DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Order items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        product_name TEXT NOT NULL,
        product_sku TEXT,
        product_price REAL NOT NULL,
        unit_price REAL,
        quantity INTEGER NOT NULL,
        subtotal REAL NOT NULL
    )");

    // Reviews table
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        customer_name TEXT NOT NULL,
        district TEXT,
        rating INTEGER NOT NULL,
        comment TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Seed default admin user (username: admin, password: admin123)
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        'admin',
        password_hash('admin123', PASSWORD_DEFAULT),
        'Arif Shikder (Administrator)',
        'arifshikderdm2000@gmail.com',
        'admin'
    ]);

    // Seed Categories
    $categories = [
        ['Traditional & Fashion', 'traditional-fashion', 'fa-shirt', 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=600&q=80', 'আভিজাত্যময় শাড়ি, প্রিমিয়াম পাঞ্জাবি ও ট্রাডিশনাল পোশাক', 1],
        ['Electronics & Gadgets', 'electronics-gadgets', 'fa-mobile-screen', 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=600&q=80', 'স্মার্টওয়াচ, ইয়ারবাডস, ব্লুটুথ স্পিকার ও আধুনিক গ্যাজেটস', 1],
        ['Pure & Organic Food', 'pure-organic-food', 'fa-jar', 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=600&q=80', 'খাঁটি সুন্দরবনের মধু, গাওয়া ঘি ও খাঁটি সরিষার তেল', 1],
        ['Home & Lifestyle', 'home-lifestyle', 'fa-house', 'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?w=600&q=80', 'প্রিমিয়াম কটন বেডশিট, কিচেন আইটেম ও ডেকোরেশন সামগ্রী', 1],
        ['Health & Beauty', 'health-beauty', 'fa-spa', 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=600&q=80', 'অর্গানিক স্কিন কেয়ার, হারবাল হেয়ার অয়েল ও পার্সোনাল কেয়ার', 1]
    ];

    $catStmt = $pdo->prepare("INSERT INTO categories (name, slug, icon, image, description, is_featured) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($categories as $cat) {
        $catStmt->execute($cat);
    }

    // Seed Products (Bangladeshi products with ৳ BDT pricing)
    $products = [
        [
            1, // Traditional & Fashion
            'প্রিমিয়াম জামদানি কটন শাড়ি (Traditional Cotton Jamdani Saree)',
            'premium-cotton-jamdani-saree',
            '১০০% সুতি সুতায় হাতে বোনা ঐতিহ্যবাহী ঢাকাই জামদানি শাড়ি। অত্যন্ত আরামদায়ক ও দীর্ঘস্থায়ী রঙ। বিয়ে, উৎসব ও যেকোনো ঘরোয়া অনুষ্ঠানে পরিধানের জন্য অনন্য। সাথে রানিং ব্লাউজ পিস অন্তর্ভুক্ত।',
            '১০০% সুতি খাঁটি জামদানি শাড়ি, ব্লাউজ পিস সহ।',
            2850,
            2450,
            18,
            'JMD-881',
            'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=600&q=80',
            json_encode([
                'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=600&q=80',
                'https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?w=600&q=80'
            ]),
            1, 1, 1
        ],
        [
            1, // Traditional & Fashion
            'রয়েল সেমি-তসর সিল্ক পাঞ্জাবি (Royal Semi-Tussar Silk Panjabi)',
            'royal-semi-tussar-silk-panjabi',
            'বিশেষ উৎসব ও জুমার নামাজের জন্য তৈরি চমৎকার কারুকাজ করা সেমি-তসর সিল্ক পাঞ্জাবি। কলার ও প্ল্যাকেটে সূক্ষ্ম এমব্রয়ডারি ডিজাইন। বিভিন্ন সাইজে (৩৮, ৪০, ৪২, ৪৪) উপলব্ধ।',
            'উন্নত মানের তসর সিল্ক ফ্যাব্রিক ও সূক্ষ্ম এমব্রয়ডারি কাজ।',
            2200,
            1750,
            25,
            'PNJ-102',
            'https://images.unsplash.com/photo-1597983073493-88cd35cf93b0?w=600&q=80',
            json_encode([
                'https://images.unsplash.com/photo-1597983073493-88cd35cf93b0?w=600&q=80'
            ]),
            1, 1, 1
        ],
        [
            2, // Electronics & Gadgets
            'আল্ট্রা ট্রু ওয়্যারলেস এয়ারবাডস ANC (TWS Wireless Earbuds)',
            'ultra-tws-wireless-earbuds-anc',
            'এক্টিভ নয়েজ ক্যান্সেলেশন (ANC) ও সুপার বাস সাউন্ড কোয়ালিটি সম্পন্ন ব্লুটুথ ৫.৩ এয়ারবাডস। এক চার্জে ৬ ঘণ্টা এবং কেস সহ মোট ২৮ ঘণ্টা ব্যাকআপ। আইপিএক্স৫ ওয়াটার রেসিস্ট্যান্ট।',
            'ANC সহ সুপার বাস ব্লুটুথ ৫.৩ এয়ারবাডস, ২৮ ঘণ্টা ব্যাটারি ব্যাকআপ।',
            1850,
            1399,
            40,
            'EAR-550',
            'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&q=80',
            json_encode([
                'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&q=80',
                'https://images.unsplash.com/photo-1572569511254-d8f925fe2cbb?w=600&q=80'
            ]),
            1, 1, 1
        ],
        [
            2, // Electronics & Gadgets
            'স্মার্ট অ্যামোলেড ডিসপ্লে ফিটনেস ওয়াচ (Smart Fitness Watch)',
            'smart-amoled-fitness-watch-bd',
            '১.৪৩ ইঞ্চি উজ্জ্বল অ্যামোলেড ডিসপ্লে, ব্লুটুথ কলিং, হার্ট রেট ও ব্লাড অক্সিজেন (SpO2) ট্র্যাকিং সুবিধা। ১০০টিরও বেশি স্পোর্টস মোড এবং ৭ দিনের ব্যাটারি লাইফ।',
            'ব্লুটুথ কলিং এবং অ্যামোলেড ডিসপ্লে সমৃদ্ধ প্রিমিয়াম স্মার্টওয়াচ।',
            3500,
            2850,
            15,
            'WTC-901',
            'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=600&q=80',
            json_encode([
                'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=600&q=80',
                'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600&q=80'
            ]),
            1, 0, 1
        ],
        [
            3, // Pure Organic Food
            '১০০% খাঁটি সুন্দরবনের প্রাকৃতিক মধু (Sundarbans Natural Honey - 500g)',
            'sundarbans-pure-natural-honey-500g',
            'সুন্দরবনের গভীর জঙ্গল থেকে সংগৃহীত সম্পূর্ণ খাঁটি ও প্রাকৃতিক খলিশা ফুলের মধু। কোনো প্রকার কৃত্রিম মিষ্টি বা প্রিজারভেটিভ মুক্ত। রোগ প্রতিরোধ ক্ষমতা বৃদ্ধিতে অত্যন্ত কার্যকর।',
            'সুন্দরবনের খাঁটি খলিশা ফুলের মধু, ল্যাব টেস্টে পরীক্ষিত।',
            850,
            699,
            50,
            'HNY-050',
            'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=600&q=80',
            json_encode([
                'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=600&q=80'
            ]),
            1, 1, 1
        ],
        [
            3, // Pure Organic Food
            'খাঁটি গাওয়া ঘি - সিরাজগঞ্জ ঐতিহ্য (Pure Ghee from Sirajganj - 500g)',
            'pure-desi-ghee-sirajganj-500g',
            'সিরাজগঞ্জের সেরা দুধের মাখন জ্বাল দিয়ে তৈরি সুস্বাদু ও ঘ্রাণযুক্ত খাঁটি গাওয়া ঘি। ভাত, খিচুড়ি ও মিষ্টি তৈরিতে অতুলনীয় স্বাদ এনে দেবে। ১০০% কেমিক্যাল মুক্ত।',
            'সিরাজগঞ্জের ঐতিহ্যবাহী ১০০% খাঁটি সুস্বাদু গাওয়া ঘি।',
            1100,
            950,
            30,
            'GHE-500',
            'https://images.unsplash.com/photo-1628088062854-d1870b4553da?w=600&q=80',
            json_encode([
                'https://images.unsplash.com/photo-1628088062854-d1870b4553da?w=600&q=80'
            ]),
            1, 0, 1
        ],
        [
            4, // Home & Lifestyle
            '১০০% কটন কিং সাইজ বেডশিট সেট (King Size Cotton Bedsheet Set)',
            'king-size-cotton-bedsheet-set',
            'উচ্চমানের কটন সুতায় তৈরি কিং সাইজ (৭.৫ ফুট x ৮.৫ ফুট) বেডশিট। সাথে ২টি ম্যাচিং বালিশের কভার। রঙের ১০০% গ্যারান্টি, কোনো প্রকার গুটি উঠবে না।',
            'কিং সাইজ পিওর কটন বেডশিট + ২টি বালিশের কভার।',
            1650,
            1290,
            22,
            'BED-701',
            'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?w=600&q=80',
            json_encode([
                'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?w=600&q=80'
            ]),
            1, 1, 0
        ],
        [
            4, // Home & Lifestyle
            'স্টেইনলেস স্টিল ইলেকট্রিক কেটলি ২ লিটার (Electric Kettle 2.0L)',
            'stainless-steel-electric-kettle-2l',
            'দ্রুত পানি গরম করার জন্য ১৫০০ ওয়াট ক্ষমতাসম্পন্ন ফুড-গ্রেড স্টেইনলেস স্টিল ইলেকট্রিক কেটলি। অটো শাট-অফ ও ড্রাই-বয়েল প্রটেকশন সুবিধা। চা, কফি বা গরম পানির জন্য উপযুক্ত।',
            '১৫০০ ওয়াট দ্রুত গরম হওয়ার অটো কাট-অফ ইলেকট্রিক কেটলি।',
            1250,
            980,
            35,
            'KTL-200',
            'https://images.unsplash.com/photo-1594213114663-ddf4f240f10d?w=600&q=80',
            json_encode([
                'https://images.unsplash.com/photo-1594213114663-ddf4f240f10d?w=600&q=80'
            ]),
            0, 1, 1
        ],
        [
            5, // Health & Beauty
            'অর্গানিক রেড অনিয়ন হেয়ার গ্রোথ অয়েল (Organic Onion Hair Oil - 200ml)',
            'organic-onion-hair-growth-oil-200ml',
            'চুল পড়া বন্ধ করতে ও নতুন চুল গজাতে সাহায্যকারী প্রাকৃতিক লাল পেঁয়াজের নির্যাস, কালোজিরা ও ক্যাস্টর অয়েল সমৃদ্ধ হারবাল হেয়ার অয়েল। প্যারাবেন ও মিনারেল অয়েল মুক্ত।',
            'চুল পড়া রোধে কার্যকর প্রাকৃতিক লাল পেঁয়াজ ও কালোজিরা তেল।',
            750,
            550,
            45,
            'OIL-200',
            'https://images.unsplash.com/photo-1608248597359-2e65c5896a24?w=600&q=80',
            json_encode([
                'https://images.unsplash.com/photo-1608248597359-2e65c5896a24?w=600&q=80'
            ]),
            1, 1, 1
        ],
        [
            2, // Electronics & Gadgets
            '২০,০০০ mAh ফাস্ট চার্জিং পাওয়ার ব্যাংক (20000mAh Power Bank 22.5W)',
            '20000mah-fast-charging-power-bank',
            '২২.৫ ওয়াট সুপার ফাস্ট চার্জিং ও পিডি (Power Delivery) প্রযুক্তিসম্পন্ন পাওয়ার ব্যাংক। ডিজিটাল এলইডি ব্যাটারি ডিসপ্লে, একসাথে ৩টি ডিভাইস চার্জ করার সুবিধা।',
            '২২.৫ ওয়াট ফাস্ট চার্জিং, ডিজিটাল ডিসপ্লে ২০০০০mAh পাওয়ার ব্যাংক।',
            2400,
            1850,
            28,
            'PWR-20K',
            'https://images.unsplash.com/photo-1609592426508-cc821966a3e1?w=600&q=80',
            json_encode([
                'https://images.unsplash.com/photo-1609592426508-cc821966a3e1?w=600&q=80'
            ]),
            1, 0, 1
        ],
        [
            3, // Pure Organic Food
            'গাছের কাঠের ঘানির খাঁটি সরিষার তেল (Pure Mustard Oil - 1 Litre)',
            'wood-pressed-pure-mustard-oil-1l',
            'ঐতিহ্যবাহী কাঠের ঘানিতে ভাঙানো দেশি লাল সরিষার ঝাঁজালো খাঁটি তেল। কোনো কেমিক্যাল বা কৃত্রিম ঝাঁজ ছাড়া প্রাকৃতিক ঘ্রাণ ও গুণাগুণ সংরক্ষিত।',
            'কাঠের ঘানিতে ভাঙানো শতভাগ খাঁটি দেশি সরিষার তেল।',
            450,
            380,
            60,
            'MST-100',
            'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&q=80',
            json_encode([
                'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&q=80'
            ]),
            0, 1, 0
        ],
        [
            1, // Traditional & Fashion
            'ম্যানস ক্যাজুয়াল ফুল স্লিভ শার্ট (Men Casual Slim Fit Shirt)',
            'men-casual-slim-fit-shirt-bd',
            '১০০% প্রিমিয়াম সুতি সুতায় তৈরি আরামদায়ক ক্যাজুয়াল শার্ট। অফিসিয়াল বা ক্যাজুয়াল ব্যবহারের জন্য মানানসই। ফিনিশিং ও স্টিচিং নিখুঁত।',
            '১০০% সফট কটন স্লিম ফিট প্রিমিয়াম ক্যাজুয়াল শার্ট।',
            1400,
            1100,
            30,
            'SHT-331',
            'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=600&q=80',
            json_encode([
                'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=600&q=80'
            ]),
            0, 1, 1
        ]
    ];

    $prodStmt = $pdo->prepare("INSERT INTO products (category_id, name, slug, description, short_description, price, discount_price, stock, sku, image, images, is_featured, is_new, is_bestseller) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($products as $p) {
        $prodStmt->execute($p);
    }

    // Seed Sample Customer Reviews
    $reviews = [
        [1, 'সাব্বির আহমেদ', 'ঢাকা', 5, 'জামদানি শাড়িটির মান খুবই ভালো। যেমন ছবিতে দেখেছি ঠিক তেমনই পেয়েছি। মাত্র ২৪ ঘণ্টায় ডেলিভারি পেয়েছি।'],
        [2, 'তানভীর হাসান', 'চট্টগ্রাম', 5, 'পাঞ্জাবির কাপড় এবং এমব্রয়ডারি সত্যিই চমৎকার। ঈদের জন্য কেনা সার্থক হয়েছে। ধন্যবাদ বঙ্গস্টোর!'],
        [3, 'মাহমুদুল হক', 'সিলেট', 5, 'এয়ারবাডসের সাউন্ড এবং বাস অনেক ক্লিয়ার। চার্জও অনেক দীর্ঘস্থায়ী থাকে। ক্যাশ অন ডেলিভারিতে কোনো ঝামেলা হয়নি।'],
        [5, 'রুমানা আক্তার', 'বগুড়া', 5, 'খাঁটি সুন্দরবনের মধু! স্বাদ এবং ঘ্রাণেই বোঝা যায় কোনো ভেজাল নেই। পরিবারের সবার খুব পছন্দ হয়েছে।'],
        [7, 'নাসরিন জাহান', 'খুলনা', 5, 'বেডশিটের কাপড় পিওর কটন। ধোয়ার পরও রঙের কোনো পরিবর্তন হয়নি। সাইজও পারফেক্ট।']
    ];

    $revStmt = $pdo->prepare("INSERT INTO reviews (product_id, customer_name, district, rating, comment) VALUES (?, ?, ?, ?, ?)");
    foreach ($reviews as $rev) {
        $revStmt->execute($rev);
    }

    // Seed a couple of initial sample orders for dashboard stats
    $pdo->exec("INSERT INTO orders (order_number, customer_name, customer_phone, customer_email, address, district, delivery_area, delivery_charge, discount_amount, total_amount, payment_method, order_notes, status, created_at)
        VALUES ('BD-10021', 'আরিফুল ইসলাম', '01711223344', 'arif@gmail.com', 'বাড়ি ১২, রোড ৪, ধানমন্ডি', 'ঢাকা', 'Inside Dhaka', 70, 0, 2520, 'Cash on Delivery', 'সন্ধ্যার পর ডেলিভারি দিলে ভালো হয়', 'Delivered', datetime('now', '-2 days'))");

    $pdo->exec("INSERT INTO orders (order_number, customer_name, customer_phone, customer_email, address, district, delivery_area, delivery_charge, discount_amount, total_amount, payment_method, order_notes, status, created_at)
        VALUES ('BD-10022', 'রাকিবুল হাসান', '01822334455', 'rakib@gmail.com', 'জিইসি মোড়, লালখান বাজার', 'চট্টগ্রাম', 'Outside Dhaka', 130, 100, 1429, 'Cash on Delivery', 'কল দিয়ে আসবেন', 'Processing', datetime('now', '-1 day'))");

    $pdo->exec("INSERT INTO orders (order_number, customer_name, customer_phone, customer_email, address, district, delivery_area, delivery_charge, discount_amount, total_amount, payment_method, order_notes, status, created_at)
        VALUES ('BD-10023', 'সুমাইয়া রহমান', '01933445566', 'sumaiya@gmail.com', 'উপশহর ব্লক-বি', 'সিলেট', 'Outside Dhaka', 130, 0, 2980, 'Cash on Delivery', '', 'Pending', datetime('now', '-3 hours'))");
}
