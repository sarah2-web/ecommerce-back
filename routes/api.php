<?php

use App\Http\Controllers\API\UserController as APIUserController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\UserController as ControllersAdminUserController;

Route::apiResource('/users',UserController::class);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register',[UserController::class,'register']);

 Route::post('login',[UserController::class,'login']);
 Route::post('logout',[UserController::class,'logout'])->middleware('auth:sanctum');
 Route::get('/index', function() { return view('index'); }); // الصفحة الرئيسية
Route::get('/admin', function() { return view('admin'); }); // صفحة الأدمن

Route::apiResource('/products',ProductController::class);
Route::apiResource('/orders',OrderController::class);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/orders', [OrderController::class, 'index']);
Route::apiResource('/order-items',OrderItemController::class);
Route::get('users/{id}/orders', [OrderController::class, 'usersOrders']);
// Route::apiResource('profiles', ProfileController::class);
Route::get('/profile', [ProfileController::class, 'index'])->middleware('auth:sanctum');
Route::post('/profile', [ProfileController::class, 'storeOrUpdate'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/profile', function (Request $request) {
    return $request->user();
});
Route::get('/admin/users-orders', [UserController::class, 'adminUsersOrders' ]);


// Checkout
Route::post('/checkout', [OrderController::class, 'checkout']);   // تحويل Cart → Order

// Orders API
Route::apiResource('/orders', OrderController::class); // index, store, show, update, destroy

// Users Orders for Admin
Route::get('users/{id}/orders', [OrderController::class, 'usersOrders']);
Route::get('/admin/users-orders', [UserController::class, 'adminUsersOrders']);


// إضافة منتج للكارت
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/order/add', [OrderController::class, 'addToCart']);
    Route::get('/order/cart', [OrderController::class, 'getCart']);
    Route::delete('/order/cart/{id}', [OrderController::class, 'removeCartItem']);
    Route::post('/order/checkout', [OrderController::class, 'checkout']);
    Route::post('/order/cart/update', [OrderController::class, 'updateCartItem']);

});
