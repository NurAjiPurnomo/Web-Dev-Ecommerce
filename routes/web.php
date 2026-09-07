<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RajaOngkirController;
use App\Http\Middleware\AdminMiddleware;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalog', [HomeController::class, 'catalog'])->name('catalog');
Route::get('/product/{id?}/{slug?}', [HomeController::class, 'productDetail'])->name('product.detail');
Route::get('/promo', [HomeController::class, 'promo'])->name('promo');
Route::get('/berita', [HomeController::class, 'articlesIndex'])->name('articles.index');
Route::get('/berita/{slug}', [HomeController::class, 'articleDetail'])->name('articles.detail');

// RajaOngkir Helper API Routes for Checkout
Route::get('/shipping/provinces', [RajaOngkirController::class, 'getProvinces'])->name('shipping.provinces');
Route::get('/shipping/cities/{province_id}', [RajaOngkirController::class, 'getCities'])->name('shipping.cities');

// Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/buy-now', [CartController::class, 'buyNow'])->name('cart.buyNow');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/remove-selected', [CartController::class, 'removeSelected'])->name('cart.removeSelected');
Route::post('/cart/update-selected', [CartController::class, 'updateSelected'])->name('cart.updateSelected');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Checkout Routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout')->middleware('auth');
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process')->middleware('auth');
Route::post('/checkout/address', [CheckoutController::class, 'updateAddress'])->name('checkout.address')->middleware('auth');
Route::post('/checkout/calculate-shipping', [CheckoutController::class, 'calculateShipping'])->name('checkout.calculateShipping')->middleware('auth');
Route::post('/checkout/confirm-payment', [CheckoutController::class, 'confirmPayment'])->name('checkout.confirmPayment')->middleware('auth');
Route::get('/checkout/success/{order_id?}', [CheckoutController::class, 'success'])->name('checkout.success')->middleware('auth');

// Profile & Order Management Routes
Route::get('/profile', [ProfileController::class, 'show'])->name('profile')->middleware('auth');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');
Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password')->middleware('auth');
Route::get('/orders', [OrderController::class, 'index'])->name('orders')->middleware('auth');
Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel')->where('id', '.*')->middleware('auth');
Route::post('/orders/review', [OrderController::class, 'submitReview'])->name('orders.review')->middleware('auth');
Route::get('/setup-dummy-order', [\App\Http\Controllers\OrderController::class, 'setupDummyOrder']);

// Wishlist Routes
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist')->middleware('auth');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle')->middleware('auth');

// Voucher Routes
Route::post('/vouchers/{id}/claim', [\App\Http\Controllers\VoucherController::class, 'claim'])->name('vouchers.claim')->middleware('auth');
Route::get('/profile/vouchers', [\App\Http\Controllers\VoucherController::class, 'myVouchers'])->name('vouchers.mine')->middleware('auth');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/auth/google', [AuthController::class, 'googleRedirect'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback'])->name('auth.google.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetOtp'])->name('password.email');
Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
Route::post('/resend-reset-otp', [ForgotPasswordController::class, 'resendResetOtp'])->name('password.resendOtp');

// OTP Routes
Route::get('/verify-otp', [OtpController::class, 'showVerifyForm'])->name('otp.verify');
Route::post('/verify-otp', [OtpController::class, 'verify'])->name('otp.verify.post');
Route::post('/resend-otp', [OtpController::class, 'resend'])->name('otp.resend');

// ==========================================
// ADMIN DASHBOARD & MANAGEMENT ROUTES
// ==========================================
Route::get('/admin', [AdminController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.post');

Route::middleware([AdminMiddleware::class])->prefix('admin')->group(function () {
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Products CRUD
    Route::get('/products', [AdminController::class, 'products'])->name('admin.products');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('admin.products.store');
    Route::post('/products/{id}/update', [AdminController::class, 'updateProduct'])->name('admin.products.update');
    Route::match(['post', 'delete'], '/products/{id}/delete', [AdminController::class, 'deleteProduct'])->name('admin.products.delete');

    // Users Management
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/{id}/toggle', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle');

    // Orders Management
    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::post('/orders/{id}/update', [AdminController::class, 'updateOrder'])->name('admin.orders.update');

    // Vouchers Management
    Route::get('/vouchers', [AdminController::class, 'vouchers'])->name('admin.vouchers');
    Route::post('/vouchers', [AdminController::class, 'storeVoucher'])->name('admin.vouchers.store');
    Route::post('/vouchers/{id}/toggle', [AdminController::class, 'toggleVoucher'])->name('admin.vouchers.toggle');

    // Affiliates Management
    Route::get('/affiliates', [AdminController::class, 'affiliates'])->name('admin.affiliates');
    Route::post('/affiliates/{id}/payout', [AdminController::class, 'payoutAffiliate'])->name('admin.affiliates.payout');

    // Notifications Management
    Route::get('/notifications', [AdminController::class, 'notifications'])->name('admin.notifications');
    Route::post('/notifications', [AdminController::class, 'storeNotification'])->name('admin.notifications.store');
    Route::post('/notifications/{id}/toggle', [AdminController::class, 'toggleNotification'])->name('admin.notifications.toggle');

    // Product Reviews Management
    Route::get('/reviews', [AdminController::class, 'reviews'])->name('admin.reviews');
    Route::post('/reviews/{id}/delete', [AdminController::class, 'deleteReview'])->name('admin.reviews.delete');

    // Promo Page Management
    Route::get('/promos', [AdminController::class, 'promos'])->name('admin.promos');

    // Banner Promo Management
    Route::get('/banners', [AdminController::class, 'banners'])->name('admin.banners');
    Route::post('/banners', [AdminController::class, 'storeBanner'])->name('admin.banners.store');
    Route::post('/banners/{id}/update', [AdminController::class, 'updateBanner'])->name('admin.banners.update');
    Route::post('/banners/{id}/toggle', [AdminController::class, 'toggleBannerStatus'])->name('admin.banners.toggle');
    Route::post('/banners/{id}/delete', [AdminController::class, 'deleteBanner'])->name('admin.banners.delete');
    // Articles / Blog Management
    Route::get('/articles', [AdminController::class, 'articles'])->name('admin.articles');
    Route::post('/articles', [AdminController::class, 'storeArticle'])->name('admin.articles.store');
    Route::post('/articles/{id}/update', [AdminController::class, 'updateArticle'])->name('admin.articles.update');
    Route::post('/articles/{id}/toggle', [AdminController::class, 'toggleArticleStatus'])->name('admin.articles.toggle');
    Route::post('/articles/{id}/delete', [AdminController::class, 'deleteArticle'])->name('admin.articles.delete');

    // Custom Pages Management
    Route::get('/pages', [AdminController::class, 'pages'])->name('admin.pages');
    Route::post('/pages', [AdminController::class, 'storePage'])->name('admin.pages.store');
    Route::post('/pages/{id}/update', [AdminController::class, 'updatePage'])->name('admin.pages.update');
    Route::post('/pages/{id}/toggle', [AdminController::class, 'togglePageStatus'])->name('admin.pages.toggle');
    Route::post('/pages/{id}/delete', [AdminController::class, 'deletePage'])->name('admin.pages.delete');
});

// Dynamic Pages Route (must be at the end to avoid conflicts)
Route::get('/page/{slug}', [HomeController::class, 'dynamicPage'])->name('page.show');

// DOKU Webhook Route
Route::post('/api/doku/webhook', [App\Http\Controllers\DokuWebhookController::class, 'handle'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);





