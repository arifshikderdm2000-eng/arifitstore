-- ==========================================================
-- Arif Shikder IT Services - MySQL Database Dump for InfinityFree Hosting
-- Compatibility: MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+
-- All prices are strictly in Bangladeshi Taka (৳ BDT)
-- ==========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+06:00"; -- Bangladesh Standard Time (BST)

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Table structure for `admin_users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default admin user (Username: admin, Password: admin123)
INSERT INTO `admin_users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$eA87H96P8c3Wj85kF5f4n.f9lY4mD5L10K8R2uG/2qN8P9ZzT1gQW', 'Arif Shikder (Administrator)', 'arifshikderdm2000@gmail.com', 'admin', NOW())
ON DUPLICATE KEY UPDATE `username`=`username`;

-- --------------------------------------------------------
-- Table structure for `categories`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL UNIQUE,
  `icon` varchar(50) DEFAULT 'fa-box',
  `image` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `image`, `description`, `is_featured`) VALUES
(1, 'Traditional & Fashion', 'traditional-fashion', 'fa-shirt', 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=600&q=80', 'আভিজাত্যময় শাড়ি, প্রিমিয়াম পাঞ্জাবি ও ট্রাডিশনাল পোশাক', 1),
(2, 'Electronics & Gadgets', 'electronics-gadgets', 'fa-mobile-screen', 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=600&q=80', 'স্মার্টওয়াচ, ইয়ারবাডস, ব্লুটুথ স্পিকার ও আধুনিক গ্যাজেটস', 1),
(3, 'Pure & Organic Food', 'pure-organic-food', 'fa-jar', 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=600&q=80', 'খাঁটি সুন্দরবনের মধু, গাওয়া ঘি ও খাঁটি সরিষার তেল', 1),
(4, 'Home & Lifestyle', 'home-lifestyle', 'fa-house', 'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?w=600&q=80', 'প্রিমিয়াম কটন বেডশিট, কিচেন আইটেম ও ডেকোরেশন সামগ্রী', 1),
(5, 'Health & Beauty', 'health-beauty', 'fa-spa', 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=600&q=80', 'অর্গানিক স্কিন কেয়ার, হারবাল হেয়ার অয়েল ও পার্সোনাল কেয়ার', 1)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- --------------------------------------------------------
-- Table structure for `products`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `description` text NOT NULL,
  `short_description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 10,
  `sku` varchar(50) DEFAULT NULL,
  `image` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_new` tinyint(1) DEFAULT 0,
  `is_bestseller` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Products (with prices strictly in ৳ BDT)
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `short_description`, `price`, `discount_price`, `stock`, `sku`, `image`, `images`, `is_featured`, `is_new`, `is_bestseller`, `status`) VALUES
(1, 1, 'প্রিমিয়াম জামদানি কটন শাড়ি (Traditional Cotton Jamdani Saree)', 'premium-cotton-jamdani-saree', '১০০% সুতি সুতায় হাতে বোনা ঐতিহ্যবাহী ঢাকাই জামদানি শাড়ি। অত্যন্ত আরামদায়ক ও দীর্ঘস্থায়ী রঙ। বিয়ে, উৎসব ও যেকোনো ঘরোয়া অনুষ্ঠানে পরিধানের জন্য অনন্য। সাথে রানিং ব্লাউজ পিস অন্তর্ভুক্ত।', '১০০% সুতি খাঁটি জামদানি শাড়ি, ব্লাউজ পিস সহ।', 2850.00, 2450.00, 18, 'JMD-881', 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=600&q=80', '[\"https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=600&q=80\",\"https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?w=600&q=80\"]', 1, 1, 1, 'active'),
(2, 1, 'রয়েল সেমি-তসর সিল্ক পাঞ্জাবি (Royal Semi-Tussar Silk Panjabi)', 'royal-semi-tussar-silk-panjabi', 'বিশেষ উৎসব ও জুমার নামাজের জন্য তৈরি চমৎকার কারুকাজ করা সেমি-তসর সিল্ক পাঞ্জাবি। কলার ও প্ল্যাকেটে সূক্ষ্ম এমব্রয়ডারি ডিজাইন। বিভিন্ন সাইজে (৩৮, ৪০, ৪২, ৪৪) উপলব্ধ।', 'উন্নত মানের তসর সিল্ক ফ্যাব্রিক ও সূক্ষ্ম এমব্রয়ডারি কাজ।', 2200.00, 1750.00, 25, 'PNJ-102', 'https://images.unsplash.com/photo-1597983073493-88cd35cf93b0?w=600&q=80', '[\"https://images.unsplash.com/photo-1597983073493-88cd35cf93b0?w=600&q=80\"]', 1, 1, 1, 'active'),
(3, 2, 'আল্ট্রা ট্রু ওয়্যারলেস এয়ারবাডস ANC (TWS Wireless Earbuds)', 'ultra-tws-wireless-earbuds-anc', 'এক্টিভ নয়েজ ক্যান্সেলেশন (ANC) ও সুপার বাস সাউন্ড কোয়ালিটি সম্পন্ন ব্লুটুথ ৫.৩ এয়ারবাডস। এক চার্জে ৬ ঘণ্টা এবং কেস সহ মোট ২৮ ঘণ্টা ব্যাকআপ। আইপিএক্স৫ ওয়াটার রেসিস্ট্যান্ট।', 'ANC সহ সুপার বাস ব্লুটুথ ৫.৩ এয়ারবাডস, ২৮ ঘণ্টা ব্যাটারি ব্যাকআপ।', 1850.00, 1399.00, 40, 'EAR-550', 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&q=80', '[\"https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&q=80\",\"https://images.unsplash.com/photo-1572569511254-d8f925fe2cbb?w=600&q=80\"]', 1, 1, 1, 'active'),
(4, 2, 'স্মার্ট অ্যামোলেড ডিসপ্লে ফিটনেস ওয়াচ (Smart Fitness Watch)', 'smart-amoled-fitness-watch-bd', '১.৪৩ ইঞ্চি উজ্জ্বল অ্যামোলেড ডিসপ্লে, ব্লুটুথ কলিং, হার্ট রেট ও ব্লাড অক্সিজেন (SpO2) ট্র্যাকিং সুবিধা। ১০০টিরও বেশি স্পোর্টস মোড এবং ৭ দিনের ব্যাটারি লাইফ।', 'ব্লুটুথ কলিং এবং অ্যামোলেড ডিসপ্লে সমৃদ্ধ প্রিমিয়াম স্মার্টওয়াচ।', 3500.00, 2850.00, 15, 'WTC-901', 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=600&q=80', '[\"https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=600&q=80\"]', 1, 0, 1, 'active'),
(5, 3, '১০০% খাঁটি সুন্দরবনের প্রাকৃতিক মধু (Sundarbans Natural Honey - 500g)', 'sundarbans-pure-natural-honey-500g', 'সুন্দরবনের গভীর জঙ্গল থেকে সংগৃহীত সম্পূর্ণ খাঁটি ও প্রাকৃতিক খলিশা ফুলের মধু। কোনো প্রকার কৃত্রিম মিষ্টি বা প্রিজারভেটিভ মুক্ত। রোগ প্রতিরোধ ক্ষমতা বৃদ্ধিতে অত্যন্ত কার্যকর।', 'সুন্দরবনের খাঁটি খলিশা ফুলের মধু, ল্যাব টেস্টে পরীক্ষিত।', 850.00, 699.00, 50, 'HNY-050', 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=600&q=80', '[\"https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=600&q=80\"]', 1, 1, 1, 'active'),
(6, 3, 'খাঁটি গাওয়া ঘি - সিরাজগঞ্জ ঐতিহ্য (Pure Ghee from Sirajganj - 500g)', 'pure-desi-ghee-sirajganj-500g', 'সিরাজগঞ্জের সেরা দুধের মাখন জ্বাল দিয়ে তৈরি সুস্বাদু ও ঘ্রাণযুক্ত খাঁটি গাওয়া ঘি। ভাত, খিচুড়ি ও মিষ্টি তৈরিতে অতুলনীয় স্বাদ এনে দেবে। ১০০% কেমিক্যাল মুক্ত।', 'সিরাজগঞ্জের ঐতিহ্যবাহী ১০০% খাঁটি সুস্বাদু গাওয়া ঘি।', 1100.00, 950.00, 30, 'GHE-500', 'https://images.unsplash.com/photo-1628088062854-d1870b4553da?w=600&q=80', '[\"https://images.unsplash.com/photo-1628088062854-d1870b4553da?w=600&q=80\"]', 1, 0, 1, 'active'),
(7, 4, '১০০% কটন কিং সাইজ বেডশিট সেট (King Size Cotton Bedsheet Set)', 'king-size-cotton-bedsheet-set', 'উচ্চমানের কটন সুতায় তৈরি কিং সাইজ (৭.৫ ফুট x ৮.৫ ফুট) বেডশিট। সাথে ২টি ম্যাচিং বালিশের কভার। রঙের ১০০% গ্যারান্টি, কোনো প্রকার গুটি উঠবে না।', 'কিং সাইজ পিওর কটন বেডশিট + ২টি বালিশের কভার।', 1650.00, 1290.00, 22, 'BED-701', 'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?w=600&q=80', '[\"https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?w=600&q=80\"]', 1, 1, 0, 'active'),
(8, 4, 'স্টেইনলেস স্টিল ইলেকট্রিক কেটলি ২ লিটার (Electric Kettle 2.0L)', 'stainless-steel-electric-kettle-2l', 'দ্রুত পানি গরম করার জন্য ১৫০০ ওয়াট ক্ষমতাসম্পন্ন ফুড-গ্রেড স্টেইনলেস স্টিল ইলেকট্রিক কেটলি। অটো শাট-অফ ও ড্রাই-বয়েল প্রটেকশন সুবিধা। চা, কফি বা গরম পানির জন্য উপযুক্ত।', '১৫০০ ওয়াট দ্রুত গরম হওয়ার অটো কাট-অফ ইলেকট্রিক কেটলি।', 1250.00, 980.00, 35, 'KTL-200', 'https://images.unsplash.com/photo-1594213114663-ddf4f240f10d?w=600&q=80', '[\"https://images.unsplash.com/photo-1594213114663-ddf4f240f10d?w=600&q=80\"]', 0, 1, 1, 'active'),
(9, 5, 'অর্গানিক রেড অনিয়ন হেয়ার গ্রোথ অয়েল (Organic Onion Hair Oil - 200ml)', 'organic-onion-hair-growth-oil-200ml', 'চুল পড়া বন্ধ করতে ও নতুন চুল গজাতে সাহায্যকারী প্রাকৃতিক লাল পেঁয়াজের নির্যাস, কালোজিরা ও ক্যাস্টর অয়েল সমৃদ্ধ হারবাল হেয়ার অয়েল। প্যারাবেন ও মিনারেল অয়েল মুক্ত।', 'চুল পড়া রোধে কার্যকর প্রাকৃতিক লাল পেঁয়াজ ও কালোজিরা তেল।', 750.00, 550.00, 45, 'OIL-200', 'https://images.unsplash.com/photo-1608248597359-2e65c5896a24?w=600&q=80', '[\"https://images.unsplash.com/photo-1608248597359-2e65c5896a24?w=600&q=80\"]', 1, 1, 1, 'active'),
(10, 2, '২০,০০০ mAh ফাস্ট চার্জিং পাওয়ার ব্যাংক (20000mAh Power Bank 22.5W)', '20000mah-fast-charging-power-bank', '২২.৫ ওয়াট সুপার ফাস্ট চার্জিং ও পিডি (Power Delivery) প্রযুক্তিসম্পন্ন পাওয়ার ব্যাংক। ডিজিটাল এলইডি ব্যাটারি ডিসপ্লে, একসাথে ৩টি ডিভাইস চার্জ করার সুবিধা।', '২২.৫ ওয়াট ফাস্ট চার্জিং, ডিজিটাল ডিসপ্লে ২০০০০mAh পাওয়ার ব্যাংক।', 2400.00, 1850.00, 28, 'PWR-20K', 'https://images.unsplash.com/photo-1609592426508-cc821966a3e1?w=600&q=80', '[\"https://images.unsplash.com/photo-1609592426508-cc821966a3e1?w=600&q=80\"]', 1, 0, 1, 'active'),
(11, 3, 'গাছের কাঠের ঘানির খাঁটি সরিষার তেল (Pure Mustard Oil - 1 Litre)', 'wood-pressed-pure-mustard-oil-1l', 'ঐতিহ্যবাহী কাঠের ঘানিতে ভাঙানো দেশি লাল সরিষার ঝাঁজালো খাঁটি তেল। কোনো কেমিক্যাল বা কৃত্রিম ঝাঁজ ছাড়া প্রাকৃতিক ঘ্রাণ ও গুণাগুণ সংরক্ষিত।', 'কাঠের ঘানিতে ভাঙানো শতভাগ খাঁটি দেশি সরিষার তেল।', 450.00, 380.00, 60, 'MST-100', 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&q=80', '[\"https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&q=80\"]', 0, 1, 0, 'active'),
(12, 1, 'ম্যানস ক্যাজুয়াল ফুল স্লিভ শার্ট (Men Casual Slim Fit Shirt)', 'men-casual-slim-fit-shirt-bd', '১০০% প্রিমিয়াম সুতি সুতায় তৈরি আরামদায়ক ক্যাজুয়াল শার্ট। অফিসিয়াল বা ক্যাজুয়াল ব্যবহারের জন্য মানানসই। ফিনিশিং ও স্টিচিং নিখুঁত।', '১০০% সফট কটন স্লিম ফিট প্রিমিয়াম ক্যাজুয়াল শার্ট।', 1400.00, 1100.00, 30, 'SHT-331', 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=600&q=80', '[\"https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=600&q=80\"]', 0, 1, 1, 'active')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- --------------------------------------------------------
-- Table structure for `customers`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `district` varchar(50) NOT NULL,
  `total_orders` int(11) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `orders`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL UNIQUE,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_alt_phone` varchar(20) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `delivery_address` text DEFAULT NULL,
  `district` varchar(50) NOT NULL,
  `delivery_area` varchar(50) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `delivery_charge` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'Cash on Delivery',
  `payment_status` varchar(20) DEFAULT 'unpaid',
  `order_notes` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `order_status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Sample Orders
INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `customer_phone`, `customer_alt_phone`, `customer_email`, `address`, `delivery_address`, `district`, `delivery_area`, `subtotal`, `delivery_charge`, `discount_amount`, `total_amount`, `payment_method`, `payment_status`, `order_notes`, `status`, `order_status`, `created_at`) VALUES
(1, 'BD-10021', 'আরিফুল ইসলাম', '01711223344', NULL, 'arif@gmail.com', 'বাড়ি ১২, রোড ৪, ধানমন্ডি', 'বাড়ি ১২, রোড ৪, ধানমন্ডি', 'ঢাকা', 'Inside Dhaka', 2450.00, 70.00, 0.00, 2520.00, 'Cash on Delivery', 'paid', 'সন্ধ্যার পর ডেলিভারি দিলে ভালো হয়', 'Delivered', 'delivered', NOW() - INTERVAL 2 DAY),
(2, 'BD-10022', 'রাকিবুল হাসান', '01822334455', NULL, 'rakib@gmail.com', 'জিইসি মোড়, লালখান বাজার', 'জিইসি মোড়, লালখান বাজার', 'চট্টগ্রাম', 'Outside Dhaka', 1399.00, 130.00, 100.00, 1429.00, 'Cash on Delivery', 'unpaid', 'কল দিয়ে আসবেন', 'Processing', 'processing', NOW() - INTERVAL 1 DAY),
(3, 'BD-10023', 'সুমাইয়া রহমান', '01933445566', NULL, 'sumaiya@gmail.com', 'উপশহর ব্লক-বি', 'উপশহর ব্লক-বি', 'সিলেট', 'Outside Dhaka', 2850.00, 130.00, 0.00, 2980.00, 'Cash on Delivery', 'unpaid', '', 'Pending', 'pending', NOW() - INTERVAL 3 HOUR);

-- --------------------------------------------------------
-- Table structure for `order_items`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(50) DEFAULT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Sample Order Items
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_price`, `quantity`, `subtotal`) VALUES
(1, 1, 1, 'প্রিমিয়াম জামদানি কটন শাড়ি', 2450.00, 1, 2450.00),
(2, 2, 3, 'আল্ট্রা ট্রু ওয়্যারলেস এয়ারবাডস ANC', 1399.00, 1, 1399.00),
(3, 3, 4, 'স্মার্ট অ্যামোলেড ডিসপ্লে ফিটনেস ওয়াচ', 2850.00, 1, 2850.00);

-- --------------------------------------------------------
-- Table structure for `reviews`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `district` varchar(50) DEFAULT NULL,
  `rating` int(1) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Reviews
INSERT INTO `reviews` (`id`, `product_id`, `customer_name`, `district`, `rating`, `comment`) VALUES
(1, 1, 'সাব্বির আহমেদ', 'ঢাকা', 5, 'জামদানি শাড়িটির মান খুবই ভালো। যেমন ছবিতে দেখেছি ঠিক তেমনই পেয়েছি। মাত্র ২৪ ঘণ্টায় ডেলিভারি পেয়েছি।'),
(2, 2, 'তানভীর হাসান', 'চট্টগ্রাম', 5, 'পাঞ্জাবির কাপড় এবং এমব্রয়ডারি সত্যিই চমৎকার। ঈদের জন্য কেনা সার্থক হয়েছে। ধন্যবাদ বঙ্গস্টোর!'),
(3, 3, 'মাহমুদুল হক', 'সিলেট', 5, 'এয়ারবাডসের সাউন্ড এবং বাস অনেক ক্লিয়ার। চার্জও অনেক দীর্ঘস্থায়ী থাকে। ক্যাশ অন ডেলিভারিতে কোনো ঝামেলা হয়নি।'),
(4, 5, 'রুমানা আক্তার', 'বগুড়া', 5, 'খাঁটি সুন্দরবনের মধু! স্বাদ এবং ঘ্রাণেই বোঝা যায় কোনো ভেজাল নেই। পরিবারের সবার খুব পছন্দ হয়েছে।'),
(5, 7, 'নাসরিন জাহান', 'খুলনা', 5, 'বেডশিটের কাপড় পিওর কটন। ধোয়ার পরও রঙের কোনো পরিবর্তন হয়নি। সাইজও পারফেক্ট।');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
