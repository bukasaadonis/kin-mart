<?php
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ProductController;
use App\Controllers\StockController;
use App\Controllers\SupplierController;
use App\Controllers\PurchaseController;
use App\Controllers\PosController;
use App\Controllers\SalesController;
use App\Controllers\CustomerController;
use App\Controllers\OrderController;
use App\Controllers\PromotionController;
use App\Controllers\ReportController;
use App\Controllers\SettingsController;
use App\Controllers\UserController;
use App\Controllers\AnalyticsController;
use App\Controllers\DeliveryController;
use App\Controllers\AuditController;
use App\Controllers\BackupController;
use App\Controllers\ShopController;
use App\Controllers\SearchController;
use App\Controllers\NotificationController;

$admin=['auth','role:SUPER_ADMIN,ADMIN'];
$management=['auth','role:SUPER_ADMIN,ADMIN,STOCK_MANAGER'];
$sales=['auth','role:SUPER_ADMIN,ADMIN,CASHIER'];
$crm=['auth','role:SUPER_ADMIN,ADMIN,SALES_MANAGER'];

// Boutique publique
$router->get('',[ShopController::class,'home']);
$router->get('catalog',[ShopController::class,'catalog']);
$router->get('product/{id}',[ShopController::class,'product']);
$router->get('cart',[ShopController::class,'cart']);
$router->post('cart/add',[ShopController::class,'addCart']);
$router->post('cart/update',[ShopController::class,'updateCart']);
$router->get('checkout',[ShopController::class,'checkoutForm']);
$router->post('checkout',[ShopController::class,'checkout']);
$router->post('favorite',[ShopController::class,'favorite'],['auth','role:CLIENT']);
$router->get('account',[ShopController::class,'account'],['auth','role:CLIENT']);
$router->get('account/orders/{id}',[ShopController::class,'accountOrder'],['auth','role:CLIENT']);

// Auth
$router->get('login',[AuthController::class,'loginForm']);
$router->post('login',[AuthController::class,'login']);
$router->get('register',[AuthController::class,'registerForm']);
$router->post('register',[AuthController::class,'register']);
$router->post('logout',[AuthController::class,'logout'],['auth']);
$router->get('change-password',[AuthController::class,'changePasswordForm'],['auth']);
$router->post('change-password',[AuthController::class,'changePassword'],['auth']);

// Back-office
$router->get('admin',[DashboardController::class,'index'],['auth','role:SUPER_ADMIN,ADMIN,CASHIER,STOCK_MANAGER,SALES_MANAGER']);
$router->get('search',[SearchController::class,'index'],['auth']);
$router->get('notifications',[NotificationController::class,'index'],['auth']);
$router->post('notifications/{id}/read',[NotificationController::class,'read'],['auth']);
$router->post('notifications/read-all',[NotificationController::class,'readAll'],['auth']);
$router->get('products',[ProductController::class,'index'],$management);
$router->get('products/create',[ProductController::class,'createForm'],$management);
$router->get('products/export',[ProductController::class,'exportCsv'],$management);
$router->post('products/import',[ProductController::class,'importCsv'],$management);
$router->post('products/create',[ProductController::class,'store'],$management);
$router->get('products/{id}/edit',[ProductController::class,'editForm'],$management);
$router->get('products/{id}/label',[ProductController::class,'label'],$management);
$router->post('products/{id}/edit',[ProductController::class,'update'],$management);
$router->post('products/{id}/delete',[ProductController::class,'delete'],$admin);
$router->get('api/products',[ProductController::class,'searchApi'],['auth']);

$router->get('stock',[StockController::class,'index'],$management);
$router->post('stock/movement',[StockController::class,'movement'],$management);
$router->get('stock/inventory',[StockController::class,'inventoryForm'],$management);
$router->post('stock/inventory',[StockController::class,'inventoryStore'],$management);

$router->get('suppliers',[SupplierController::class,'index'],$management);
$router->post('suppliers',[SupplierController::class,'store'],$management);
$router->post('suppliers/{id}',[SupplierController::class,'update'],$management);
$router->get('purchases',[PurchaseController::class,'index'],$management);
$router->get('purchases/create',[PurchaseController::class,'createForm'],$management);
$router->post('purchases/create',[PurchaseController::class,'store'],$management);
$router->post('purchases/{id}/receive',[PurchaseController::class,'receive'],$management);

$router->get('pos',[PosController::class,'index'],$sales);
$router->post('pos/open',[PosController::class,'open'],$sales);
$router->post('pos/add',[PosController::class,'add'],$sales);
$router->post('pos/update',[PosController::class,'update'],$sales);
$router->post('pos/checkout',[PosController::class,'checkout'],$sales);
$router->post('pos/close',[PosController::class,'close'],$sales);
$router->post('pos/expense',[PosController::class,'expense'],$sales);
$router->get('sales',[SalesController::class,'index'],$sales);
$router->get('sales/{id}',[SalesController::class,'show'],$sales);
$router->post('sales/{id}/cancel',[SalesController::class,'cancel'],['auth','role:SUPER_ADMIN,ADMIN']);
$router->post('sales/{id}/return',[SalesController::class,'returnItem'],['auth','role:SUPER_ADMIN,ADMIN,CASHIER']);
$router->get('sales/{id}/receipt',[PosController::class,'receipt'],$sales);

$router->get('customers',[CustomerController::class,'index'],$crm);
$router->post('customers',[CustomerController::class,'store'],$crm);
$router->get('customers/{id}',[CustomerController::class,'show'],$crm);
$router->post('customers/{id}',[CustomerController::class,'update'],$crm);

$router->get('orders',[OrderController::class,'index'],['auth','role:SUPER_ADMIN,ADMIN,SALES_MANAGER']);
$router->get('orders/{id}',[OrderController::class,'show'],['auth','role:SUPER_ADMIN,ADMIN,SALES_MANAGER']);
$router->post('orders/{id}/status',[OrderController::class,'status'],['auth','role:SUPER_ADMIN,ADMIN,SALES_MANAGER']);
$router->post('orders/{id}/assign',[OrderController::class,'assign'],['auth','role:SUPER_ADMIN,ADMIN']);
$router->get('deliveries',[DeliveryController::class,'index'],['auth','role:DELIVERY']);
$router->post('deliveries/{id}/status',[DeliveryController::class,'status'],['auth','role:DELIVERY']);

$router->get('promotions',[PromotionController::class,'index'],$crm);
$router->post('promotions',[PromotionController::class,'store'],$crm);
$router->post('coupons',[PromotionController::class,'storeCoupon'],$crm);
$router->get('analytics',[AnalyticsController::class,'index'],['auth','role:SUPER_ADMIN,ADMIN,SALES_MANAGER,STOCK_MANAGER']);
$router->get('reports',[ReportController::class,'index'],['auth','role:SUPER_ADMIN,ADMIN,SALES_MANAGER']);
$router->get('reports/csv',[ReportController::class,'csv'],['auth','role:SUPER_ADMIN,ADMIN,SALES_MANAGER']);
$router->get('reports/xls',[ReportController::class,'xls'],['auth','role:SUPER_ADMIN,ADMIN,SALES_MANAGER']);
$router->get('reports/pdf',[ReportController::class,'pdf'],['auth','role:SUPER_ADMIN,ADMIN,SALES_MANAGER']);

$router->get('settings',[SettingsController::class,'index'],['auth','role:SUPER_ADMIN']);
$router->post('settings',[SettingsController::class,'save'],['auth','role:SUPER_ADMIN']);
$router->post('settings/delivery-zones',[SettingsController::class,'addZone'],['auth','role:SUPER_ADMIN']);
$router->post('settings/delivery-zones/{id}',[SettingsController::class,'updateZone'],['auth','role:SUPER_ADMIN']);
$router->get('users',[UserController::class,'index'],['auth','role:SUPER_ADMIN']);
$router->post('users',[UserController::class,'store'],['auth','role:SUPER_ADMIN']);
$router->post('users/{id}/status',[UserController::class,'status'],['auth','role:SUPER_ADMIN']);
$router->get('audit',[AuditController::class,'index'],['auth','role:SUPER_ADMIN']);
$router->get('backup',[BackupController::class,'download'],['auth','role:SUPER_ADMIN']);
