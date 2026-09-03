# 🇧🇩 InfinityFree Hosting Deployment Guide - Arif Shikder IT Services

This e-commerce store is 100% built using pure **HTML, CSS, JavaScript, PHP, and MySQL**, with **NO Node.js required in production**, making it completely compatible with **InfinityFree (free PHP/MySQL shared hosting)** or any standard cPanel/vPanel hosting provider.

---

## 🚀 Step-by-Step InfinityFree Deployment Instructions

### 1. Create a Free Account on InfinityFree
1. Go to [InfinityFree.com](https://www.infinityfree.com/) and register an account.
2. Create a new hosting account and pick your free subdomain (e.g. `yourstore.epizy.com` or `yourstore.infinityfreeapp.com`).

---

### 2. Upload Website Files
1. Open the **Control Panel (vPanel)** from your InfinityFree client area.
2. Click on **Online File Manager** (or connect using an FTP client like FileZilla with the provided FTP credentials).
3. Open the **`htdocs`** folder.
4. Upload all the files and folders from this project directly into `htdocs`:
   - `index.php`
   - `shop.php`
   - `product.php`
   - `cart.php`
   - `checkout.php`
   - `order-success.php`
   - `router.php`
   - `.htaccess`
   - `includes/`
   - `admin/`
   - `config/`
   - `assets/`
   - `database.sql`

---

### 3. Create MySQL Database & Import Data
1. In your InfinityFree vPanel, go to **MySQL Databases**.
2. Create a new database name, e.g., `bongo_store`.
3. Note down the following details provided by InfinityFree:
   - **MySQL Host Name**: (e.g. `sql108.infinityfree.com` or `sql205.infinityfree.com`)
   - **MySQL Database Name**: (e.g. `if0_38123456_bongo_store`)
   - **MySQL Username**: (e.g. `if0_38123456`)
   - **MySQL Password**: (Your vPanel account password)
4. Click **phpMyAdmin** next to your newly created database.
5. In phpMyAdmin, click the **Import** tab at the top.
6. Choose the **`database.sql`** file from your project and click **Go**.
   - All tables (`categories`, `products`, `customers`, `orders`, `order_items`, `reviews`, `admin_users`) will be created automatically with sample data in Bangladeshi Taka (৳).

---

### 4. Configure Database Connection
1. In the File Manager, open `/config/db.php`.
2. Update the credentials on lines 11–14 with your InfinityFree details:

```php
define('DB_HOST', 'sql108.infinityfree.com'); // Your InfinityFree MySQL Host
define('DB_USER', 'if0_38123456');             // Your InfinityFree MySQL Username
define('DB_PASS', 'YourVpanelPassword');       // Your InfinityFree vPanel Password
define('DB_NAME', 'if0_38123456_bongo_store'); // Your InfinityFree MySQL Database Name
```

3. Save the file.

---

### 5. Access Your Website & Admin Panel
- **Customer Store**: `http://yourstore.epizy.com/`
- **Admin Dashboard**: `http://yourstore.epizy.com/admin/login.php`
  - **Default Username**: `admin`
  - **Default Password**: `admin123`

---

## 🌟 Features Included

- **100% Bangladeshi Taka (৳ BDT)**: Every single price, discount, delivery fee, and order total is strictly formatted in BDT.
- **Cash on Delivery (COD)** & Mobile Financial Services (bKash, Nagad, Rocket) reference support.
- **Dhaka & Outside Dhaka Delivery Rates**: ৳৭০ inside Dhaka, ৳১৩০ outside Dhaka.
- **Coupon Discount Engine**: Preloaded with codes `EID2026` (৳১৫০ off) and `BONGO100` (৳১০০ off).
- **Interactive Shopping Cart & Checkout**: Add to cart with quantity management, 64 Bangladesh districts dropdown selector, address verification, and real-time total updates.
- **Comprehensive Admin Panel**:
  - Live Sales & Orders Overview Cards
  - Product Catalog Management (Add, Edit, Stock, Pricing in ৳, Delete)
  - Category Management
  - Order Management & Status Workflow (Pending ➔ Processing ➔ Shipped ➔ Delivered ➔ Cancelled)
  - Printable Invoices & Direct Customer Phone Call
