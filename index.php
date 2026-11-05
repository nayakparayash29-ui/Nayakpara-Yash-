<?php
/**
 * API Router (all /api/* requests route through here using .htaccess rewrite)
 * Mirrors the Node.js routes from server.js
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// CORS — dynamic origin whitelist based on current host + dev ports
$currentHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$schemeReq = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 0) == 443) ? 'https' : 'http';
$allowedOrigins = [
    "{$schemeReq}://{$currentHost}",
    "http://localhost:8000",
    "http://localhost:5173",
    "http://localhost:5174",
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
}
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Cache-Control, Expires, Pragma, X-Admin-Secret');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Normalize request path
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$script = dirname($_SERVER['SCRIPT_NAME']);
$script = str_replace('\\', '/', $script);
if ($script && strpos($uri, $script) === 0) {
    $uri = substr($uri, strlen($script));
}
$uri = '/' . trim($uri, '/');

// Strip leading /api (in case rewrite didn't)
if (strpos($uri, '/api') === 0) {
    $uri = substr($uri, 4);
}
$uri = '/' . trim($uri, '/');

$method = $_SERVER['REQUEST_METHOD'];

// =========================================
// Route dispatcher
// =========================================

// Auth routes ----------------------------------------------------------
if ($uri === '/auth/register' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/AuthController.php';
    (new AuthController())->register();
}
if ($uri === '/auth/login' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/AuthController.php';
    (new AuthController())->login();
}
if ($uri === '/auth/logout' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/AuthController.php';
    (new AuthController())->logout();
}
if ($uri === '/auth/check-auth' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/AuthController.php';
    (new AuthController())->checkAuth();
}
if ($uri === '/auth/update-password' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/AuthController.php';
    (new AuthController())->updatePassword();
}
if ($uri === '/auth/update-profile' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/AuthController.php';
    (new AuthController())->updateProfile();
}
if ($uri === '/auth/forgot-password' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/AuthController.php';
    (new AuthController())->forgotPassword();
}

// Admin products routes ------------------------------------------------
if (preg_match('#^/admin/products/upload$#', $uri) && $method === 'POST') {
    require_once __DIR__ . '/../controllers/admin/ProductsController.php';
    (new AdminProductsController())->uploadImage();
}
if ($uri === '/admin/products' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/admin/ProductsController.php';
    (new AdminProductsController())->addProduct();
}
if ($uri === '/admin/products' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/admin/ProductsController.php';
    (new AdminProductsController())->fetchAll();
}
if (preg_match('#^/admin/products/(\d+)$#', $uri, $m) && $method === 'PUT') {
    require_once __DIR__ . '/../controllers/admin/ProductsController.php';
    (new AdminProductsController())->editProduct((int)$m[1]);
}
if (preg_match('#^/admin/products/(\d+)$#', $uri, $m) && $method === 'DELETE') {
    require_once __DIR__ . '/../controllers/admin/ProductsController.php';
    (new AdminProductsController())->deleteProduct((int)$m[1]);
}

// Admin orders routes --------------------------------------------------
if ($uri === '/admin/orders' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/admin/OrdersController.php';
    (new AdminOrdersController())->getAllOrders();
}
if ($uri === '/admin/orders/analytics' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/admin/OrdersController.php';
    (new AdminOrdersController())->getAnalytics();
}
if (preg_match('#^/admin/orders/(\d+)$#', $uri, $m) && $method === 'GET') {
    require_once __DIR__ . '/../controllers/admin/OrdersController.php';
    (new AdminOrdersController())->getOrderDetails((int)$m[1]);
}
if (preg_match('#^/admin/orders/(\d+)$#', $uri, $m) && $method === 'PUT') {
    require_once __DIR__ . '/../controllers/admin/OrdersController.php';
    (new AdminOrdersController())->updateOrderStatus((int)$m[1]);
}

// Admin users ----------------------------------------------------------
if ($uri === '/admin/users' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/admin/UsersController.php';
    (new AdminUsersController())->getAllUsers();
}
if (preg_match('#^/admin/users/(\d+)$#', $uri, $m) && $method === 'GET') {
    require_once __DIR__ . '/../controllers/admin/UsersController.php';
    (new AdminUsersController())->getUserById((int)$m[1]);
}
if (preg_match('#^/admin/users/(\d+)$#', $uri, $m) && $method === 'PUT') {
    require_once __DIR__ . '/../controllers/admin/UsersController.php';
    (new AdminUsersController())->updateUser((int)$m[1]);
}
if (preg_match('#^/admin/users/(\d+)$#', $uri, $m) && $method === 'DELETE') {
    require_once __DIR__ . '/../controllers/admin/UsersController.php';
    (new AdminUsersController())->deleteUser((int)$m[1]);
}

// Admin reviews --------------------------------------------------------
if ($uri === '/admin/reviews' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/admin/ReviewsController.php';
    (new AdminReviewsController())->getAllReviews();
}
if (preg_match('#^/admin/reviews/(\d+)$#', $uri, $m) && $method === 'DELETE') {
    require_once __DIR__ . '/../controllers/admin/ReviewsController.php';
    (new AdminReviewsController())->deleteReview((int)$m[1]);
}

// Admin features / banners (alias for common/feature) -----------------
if (preg_match('#^/admin/features/upload$#', $uri) && $method === 'POST') {
    require_once __DIR__ . '/../controllers/FeatureController.php';
    (new FeatureController())->upload();
}
if ($uri === '/admin/features' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/FeatureController.php';
    (new FeatureController())->getAll();
}
if ($uri === '/admin/features' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/FeatureController.php';
    (new FeatureController())->add();
}
if (preg_match('#^/admin/features/(\d+)$#', $uri, $m) && $method === 'PUT') {
    require_once __DIR__ . '/../controllers/FeatureController.php';
    (new FeatureController())->update((int)$m[1]);
}
if (preg_match('#^/admin/features/(\d+)$#', $uri, $m) && $method === 'DELETE') {
    require_once __DIR__ . '/../controllers/FeatureController.php';
    (new FeatureController())->delete((int)$m[1]);
}

// Admin categories & brands --------------------------------------------
if ($uri === '/admin/categories' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/admin/CategoriesController.php';
    (new AdminCategoriesController())->getAll();
}
if ($uri === '/admin/categories' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/admin/CategoriesController.php';
    (new AdminCategoriesController())->addCategory();
}
if ($uri === '/admin/categories' && $method === 'PUT') {
    require_once __DIR__ . '/../controllers/admin/CategoriesController.php';
    (new AdminCategoriesController())->renameCategory();
}
if ($uri === '/admin/categories' && $method === 'DELETE') {
    require_once __DIR__ . '/../controllers/admin/CategoriesController.php';
    (new AdminCategoriesController())->deleteCategory();
}
if ($uri === '/admin/brands' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/admin/CategoriesController.php';
    (new AdminCategoriesController())->addBrand();
}
if ($uri === '/admin/brands' && $method === 'PUT') {
    require_once __DIR__ . '/../controllers/admin/CategoriesController.php';
    (new AdminCategoriesController())->renameBrand();
}
if ($uri === '/admin/brands' && $method === 'DELETE') {
    require_once __DIR__ . '/../controllers/admin/CategoriesController.php';
    (new AdminCategoriesController())->deleteBrand();
}

// Admin coupons --------------------------------------------------------
if ($uri === '/admin/coupons' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/admin/CouponsController.php';
    (new AdminCouponsController())->getAll();
}
if ($uri === '/admin/coupons' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/admin/CouponsController.php';
    (new AdminCouponsController())->create();
}
if (preg_match('#^/admin/coupons/(\d+)$#', $uri, $m) && $method === 'PUT') {
    require_once __DIR__ . '/../controllers/admin/CouponsController.php';
    (new AdminCouponsController())->update((int)$m[1]);
}
if (preg_match('#^/admin/coupons/(\d+)$#', $uri, $m) && $method === 'DELETE') {
    require_once __DIR__ . '/../controllers/admin/CouponsController.php';
    (new AdminCouponsController())->delete((int)$m[1]);
}
if (preg_match('#^/admin/coupons/(\d+)/toggle$#', $uri, $m) && $method === 'POST') {
    require_once __DIR__ . '/../controllers/admin/CouponsController.php';
    (new AdminCouponsController())->toggle((int)$m[1]);
}

// Admin settings -------------------------------------------------------
if ($uri === '/admin/settings' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/admin/SettingsController.php';
    (new AdminSettingsController())->getSettings();
}
if ($uri === '/admin/settings' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/admin/SettingsController.php';
    (new AdminSettingsController())->saveSettings();
}
if ($uri === '/admin/settings/reset' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/admin/SettingsController.php';
    (new AdminSettingsController())->resetSettings();
}

// Shop products (public) -----------------------------------------------
if ($uri === '/shop/products' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/shop/ProductsController.php';
    (new ShopProductsController())->getFilteredProducts();
}
if ($uri === '/shop/products/meta' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/shop/ProductsController.php';
    (new ShopProductsController())->getMeta();
}
if (preg_match('#^/shop/products/(\d+)$#', $uri, $m) && $method === 'GET') {
    require_once __DIR__ . '/../controllers/shop/ProductsController.php';
    (new ShopProductsController())->getProductDetails((int)$m[1]);
}

// Shop cart ------------------------------------------------------------
if ($uri === '/shop/cart' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/shop/CartController.php';
    (new ShopCartController())->addToCart();
}
if ($uri === '/shop/cart' && $method === 'PUT') {
    require_once __DIR__ . '/../controllers/shop/CartController.php';
    (new ShopCartController())->updateCartItemQty();
}
if (preg_match('#^/shop/cart/([^/]+)$#', $uri, $m) && $method === 'GET') {
    require_once __DIR__ . '/../controllers/shop/CartController.php';
    (new ShopCartController())->fetchCartItems($m[1]);
}
if (preg_match('#^/shop/cart/([^/]+)/([^/]+)$#', $uri, $m) && $method === 'DELETE') {
    require_once __DIR__ . '/../controllers/shop/CartController.php';
    (new ShopCartController())->deleteCartItem($m[1], $m[2]);
}

// Shop address ---------------------------------------------------------
if ($uri === '/shop/address' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/shop/AddressController.php';
    (new ShopAddressController())->addAddress();
}
if (preg_match('#^/shop/address/([^/]+)$#', $uri, $m) && $method === 'GET') {
    require_once __DIR__ . '/../controllers/shop/AddressController.php';
    (new ShopAddressController())->getAddresses($m[1]);
}
if (preg_match('#^/shop/address/(\d+)$#', $uri, $m) && $method === 'PUT') {
    require_once __DIR__ . '/../controllers/shop/AddressController.php';
    (new ShopAddressController())->updateAddress((int)$m[1]);
}
if (preg_match('#^/shop/address/(\d+)$#', $uri, $m) && $method === 'DELETE') {
    require_once __DIR__ . '/../controllers/shop/AddressController.php';
    (new ShopAddressController())->deleteAddress((int)$m[1]);
}

// Shop order -----------------------------------------------------------
if ($uri === '/shop/order' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/shop/OrderController.php';
    (new ShopOrderController())->createOrder();
}
if ($uri === '/shop/order/capture' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/shop/OrderController.php';
    (new ShopOrderController())->capturePayment();
}
if ($uri === '/shop/order/stripe-checkout' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/shop/OrderController.php';
    (new ShopOrderController())->createStripeCheckoutSession();
}
if ($uri === '/shop/order/stripe-verify' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/shop/OrderController.php';
    (new ShopOrderController())->verifyStripeSession();
}
if (preg_match('#^/shop/order/user/([^/]+)$#', $uri, $m) && $method === 'GET') {
    require_once __DIR__ . '/../controllers/shop/OrderController.php';
    (new ShopOrderController())->getAllOrdersByUser($m[1]);
}
if (preg_match('#^/shop/order/(\d+)$#', $uri, $m) && $method === 'GET') {
    require_once __DIR__ . '/../controllers/shop/OrderController.php';
    (new ShopOrderController())->getOrderDetails((int)$m[1]);
}

// Shop search ----------------------------------------------------------
if ($uri === '/shop/search' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/shop/SearchController.php';
    (new ShopSearchController())->search();
}

// Shop review ----------------------------------------------------------
if ($uri === '/shop/review' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/shop/ReviewController.php';
    (new ShopReviewController())->addReview();
}
if (preg_match('#^/shop/review/(\d+)$#', $uri, $m) && $method === 'GET') {
    require_once __DIR__ . '/../controllers/shop/ReviewController.php';
    (new ShopReviewController())->getProductReviews((int)$m[1]);
}
if (preg_match('#^/shop/review/(\d+)$#', $uri, $m) && $method === 'PUT') {
    require_once __DIR__ . '/../controllers/shop/ReviewController.php';
    (new ShopReviewController())->updateReview((int)$m[1]);
}
if (preg_match('#^/shop/review/(\d+)$#', $uri, $m) && $method === 'DELETE') {
    require_once __DIR__ . '/../controllers/shop/ReviewController.php';
    (new ShopReviewController())->deleteReview((int)$m[1]);
}

// Shop wishlist --------------------------------------------------------
if ($uri === '/shop/wishlist' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/shop/WishlistController.php';
    (new ShopWishlistController())->getWishlist();
}
if (($uri === '/shop/wishlist/toggle' || $uri === '/shop/wishlist') && $method === 'POST') {
    require_once __DIR__ . '/../controllers/shop/WishlistController.php';
    (new ShopWishlistController())->toggleWishlist();
}
if (preg_match('#^/shop/wishlist/(\d+)$#', $uri, $m) && $method === 'DELETE') {
    require_once __DIR__ . '/../controllers/shop/WishlistController.php';
    (new ShopWishlistController())->removeWishlist((int)$m[1]);
}

// Shop coupon ----------------------------------------------------------
if ($uri === '/shop/coupon/apply' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/shop/CouponController.php';
    (new ShopCouponController())->apply();
}

// Common feature (banners) ---------------------------------------------
if ($uri === '/common/feature' && $method === 'GET') {
    require_once __DIR__ . '/../controllers/FeatureController.php';
    (new FeatureController())->getAll();
}
if ($uri === '/common/feature' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/FeatureController.php';
    (new FeatureController())->add();
}
if ($uri === '/common/feature/upload' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/FeatureController.php';
    (new FeatureController())->upload();
}
if (preg_match('#^/common/feature/(\d+)$#', $uri, $m) && $method === 'PUT') {
    require_once __DIR__ . '/../controllers/FeatureController.php';
    (new FeatureController())->update((int)$m[1]);
}
if (preg_match('#^/common/feature/(\d+)$#', $uri, $m) && $method === 'DELETE') {
    require_once __DIR__ . '/../controllers/FeatureController.php';
    (new FeatureController())->delete((int)$m[1]);
}

// Fallback -------------------------------------------------------------
jsonResponse(false, "Endpoint not found: {$method} {$uri}", null, 404);
