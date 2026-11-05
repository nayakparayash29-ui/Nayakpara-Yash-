<?php
/**
 * Main Page Router (mirrors client/src/App.jsx routes)
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/' && strpos($requestUri, $scriptName) === 0) {
    $requestUri = substr($requestUri, strlen($scriptName));
}
$requestUri = '/' . ltrim($requestUri, '/');
$segments = array_values(array_filter(explode('/', $requestUri), 'strlen'));
$count = count($segments);

$isAuthenticated = isLoggedIn();
$user = currentUser();
$isAdmin = isAdmin();

$protectedShop = ['checkout', 'account', 'payment-success', 'wishlist'];
if ($count >= 2 && $segments[0] === 'shop' && in_array($segments[1], $protectedShop)) {
    if (!$isAuthenticated) {
        redirect('/auth/login');
    }
}
if ($count >= 1 && $segments[0] === 'admin') {
    if (!$isAuthenticated) {
        redirect('/auth/login');
    }
    if (!$isAdmin) {
        redirect('/unauth-page');
    }
}

if ($requestUri === '/' || $requestUri === '') {
    if ($isAdmin) {
        redirect('/admin/dashboard');
    } else {
        redirect('/shop/home');
    }
}

if ($count === 1) {
    if ($segments[0] === 'shop') {
        redirect('/shop/home');
    }
    if ($segments[0] === 'auth') {
        redirect('/auth/login');
    }
    if ($segments[0] === 'admin') {
        redirect('/admin/dashboard');
    }
}

if ($count >= 2 && $segments[0] === 'auth') {
    $authPage = $segments[1];
    if ($authPage === 'login') {
        $viewContent = __DIR__ . '/views/auth/login.php';
        $pageTitle = 'Login';
        require __DIR__ . '/views/layouts/auth_layout.php';
        exit;
    } elseif ($authPage === 'register') {
        $viewContent = __DIR__ . '/views/auth/register.php';
        $pageTitle = 'Register';
        require __DIR__ . '/views/layouts/auth_layout.php';
        exit;
    } elseif ($authPage === 'forgot-password') {
        $viewContent = __DIR__ . '/views/auth/forgot_password.php';
        $pageTitle = 'Forgot Password';
        require __DIR__ . '/views/layouts/auth_layout.php';
        exit;
    }
}

if ($count >= 2 && $segments[0] === 'admin') {
    $adminPage = $segments[1];
    $adminRoutes = [
        'dashboard'   => ['file' => 'dashboard.php',   'title' => 'Dashboard'],
        'analytics'   => ['file' => 'analytics.php',   'title' => 'Analytics'],
        'products'    => ['file' => 'products.php',    'title' => 'Products'],
        'orders'      => ['file' => 'orders.php',      'title' => 'Orders'],
        'features'    => ['file' => 'features.php',    'title' => 'Banners / Features'],
        'categories'  => ['file' => 'categories.php',  'title' => 'Categories'],
        'customers'   => ['file' => 'customers.php',   'title' => 'Customers'],
        'feedback'    => ['file' => 'feedback.php',    'title' => 'Feedback'],
        'coupons'     => ['file' => 'coupons.php',     'title' => 'Coupons'],
        'reviews'     => ['file' => 'reviews.php',     'title' => 'Reviews'],
        'inventory'   => ['file' => 'inventory.php',   'title' => 'Inventory'],
        'settings'    => ['file' => 'settings.php',    'title' => 'Settings'],
    ];
    if (isset($adminRoutes[$adminPage])) {
        $viewContent = __DIR__ . '/views/admin/' . $adminRoutes[$adminPage]['file'];
        $active = $adminPage;
        $pageTitle = $adminRoutes[$adminPage]['title'];
        require __DIR__ . '/views/layouts/admin_layout.php';
        exit;
    }
}

if ($count >= 2 && $segments[0] === 'shop') {
    $shopPage = $segments[1];
    if ($shopPage === 'policies') {
        if ($count >= 3) {
            $_GET['section'] = $segments[2];
        }
        $viewContent = __DIR__ . '/views/shop/policies.php';
        $active = 'policies';
        $pageTitle = 'Policies';
        require __DIR__ . '/views/layouts/shop_layout.php';
        exit;
    }
    if ($shopPage === 'product') {
        if ($count >= 3 && is_numeric($segments[2])) {
            $_GET['id'] = (int)$segments[2];
        }
        $viewContent = __DIR__ . '/views/shop/product_detail.php';
        $active = 'listing';
        $pageTitle = 'Product Details';
        require __DIR__ . '/views/layouts/shop_layout.php';
        exit;
    }
    // Shop route aliases
    if ($shopPage === 'orders' || $shopPage === 'order') {
        redirect('/shop/account?tab=orders');
    }
    if ($shopPage === 'profile') {
        redirect('/shop/account?tab=profile');
    }
    if ($shopPage === 'addresses') {
        redirect('/shop/account?tab=addresses');
    }
    if ($shopPage === 'help' || $shopPage === 'support') {
        redirect('/shop/contact');
    }

    $shopRoutes = [
        'home'            => ['file' => 'home.php',            'title' => 'Home'],
        'listing'         => ['file' => 'listing.php',         'title' => 'Products'],
        'about'           => ['file' => 'about.php',           'title' => 'About Atelier'],
        'cart'            => ['file' => 'cart.php',            'title' => 'Shopping Cart'],
        'checkout'        => ['file' => 'checkout.php',        'title' => 'Checkout'],
        'account'         => ['file' => 'account.php',         'title' => 'My Account'],
        'payment-success' => ['file' => 'payment_success.php', 'title' => 'Payment Success'],
        'search'          => ['file' => 'search.php',          'title' => 'Search'],
        'wishlist'        => ['file' => 'wishlist.php',        'title' => 'My Wishlist'],
        'contact'         => ['file' => 'contact.php',         'title' => 'Contact Us'],
    ];
    if (isset($shopRoutes[$shopPage])) {
        $viewContent = __DIR__ . '/views/shop/' . $shopRoutes[$shopPage]['file'];
        $active = $shopPage;
        $pageTitle = $shopRoutes[$shopPage]['title'];
        require __DIR__ . '/views/layouts/shop_layout.php';
        exit;
    }
}

if ($requestUri === '/unauth-page') {
    require __DIR__ . '/views/unauth.php';
    exit;
}

require __DIR__ . '/views/not_found.php';
