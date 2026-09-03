<?php
/**
 * Router script for PHP Built-in Web Server
 * Ensures clean URLs and direct .php script execution.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static assets directly if they exist
$filePath = __DIR__ . $uri;
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    // Check if it's a PHP file; if so, execute it
    if (pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
        require $filePath;
        return true;
    }
    return false; // Serve static file as-is
}

// Check if direct path with .php exists
if (file_exists($filePath . '.php')) {
    require $filePath . '.php';
    return true;
}

// Route directory index
if (is_dir($filePath)) {
    $indexFile = rtrim($filePath, '/') . '/index.php';
    if (file_exists($indexFile)) {
        require $indexFile;
        return true;
    }
}

// Handle root
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
    return true;
}

// Route common customer pages
$routes = [
    '/shop' => '/shop.php',
    '/cart' => '/cart.php',
    '/checkout' => '/checkout.php',
    '/product' => '/product.php',
    '/order-confirmation' => '/order-confirmation.php',
    '/contact' => '/contact.php',
    '/admin' => '/admin/index.php',
    '/admin/login' => '/admin/login.php',
    '/admin/logout' => '/admin/logout.php',
    '/admin/products' => '/admin/products.php',
    '/admin/product-add' => '/admin/product-add.php',
    '/admin/product-edit' => '/admin/product-edit.php',
    '/admin/categories' => '/admin/categories.php',
    '/admin/orders' => '/admin/orders.php',
    '/admin/order-view' => '/admin/order-view.php',
    '/admin/customers' => '/admin/customers.php',
];

if (isset($routes[$uri]) && file_exists(__DIR__ . $routes[$uri])) {
    require __DIR__ . $routes[$uri];
    return true;
}

// 404 fallback
http_response_code(404);
echo "<h1>404 Not Found</h1><p>The requested page was not found.</p><a href='/'>Go to Home</a>";
return true;
