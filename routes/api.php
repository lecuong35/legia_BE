<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// ----------------USER-----------------
Route::prefix('user')->middleware('auth:api')->group( function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/me', [UserController::class, 'me']);
    Route::post('/update', [UserController::class, 'update']);
    Route::patch('/reset-password', [UserController::class, 'resetPassword']);
    // voucher
    Route::get('my-vouchers',[VoucherController::class, 'show_user_vouchers']);

    // bill
    Route::prefix('/bills')->group(function() {
        Route::delete('/{id}/cancel', [BillController::class, 'user_cancel']);
    });

    //cart item
    Route::prefix('/cart-items')->group(function() {
        Route::patch('/{id}', [CartItemController::class, 'update']);
        Route::delete('/{id}', [CartItemController::class, 'destroy']);
    });

    //notifications
    Route::get('/notifications', [UserController::class, 'get_notifications']);
});



// -------------ADMIN---------------
Route::prefix('admin')->middleware('auth:admin')->group( function () {
    Route::post('logout', [AdminController::class, 'logout']);
    Route::post('update', [AdminController::class, 'update']);

    // Brand
    Route::resource('brands', BrandController::class)->except([ 'index' ]);

    // Category
    Route::resource('categories', CategoryController::class)->except([ 'index' ]);

    // Product
    Route::resource('products', ProductController::class)->except([
        'index', 'show',
    ]);
    Route::prefix('products') ->group(function() {
        Route::get('export-excel', [ProductController::class, 'exportIntoExcel']);
        Route::post('import', [ProductController::class, 'importExcelFile']);
        Route::post('/{id}', [ProductController::class, 'update']);
    });

    // voucher
    Route::resource('vouchers', VoucherController::class)->only(['store', 'update', 'destroy', 'index']);

    // bill
    Route::prefix('bills')->group(function() {
        Route::patch('/', [BillController::class, 'update']);
        // Route::get('/{id}/export', [BillController::class, 'export_bill']);
    });

    //notifications
    Route::get('/notifications', [AdminController::class, 'get_notifications']);
});


// product
Route::prefix('products')->group(function() {
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/search', [ProductController::class, 'search']);
});

// brand
Route::get('/brands', [BrandController::class, 'index']);

// category
Route::get('/categories', [CategoryController::class, 'index']);

// voucher
Route::get('/vouchers/{id}', [VoucherController::class,'show']);

// bill
Route::prefix('/bills')->group(function() {
    Route::get('/filter', [BillController::class, 'search']);
    Route::post('/', [BillController::class, 'store']);
    Route::post('/order', [BillController::class, 'order']);
    Route::get('/{id}', [BillController::class, 'show']);
});

// cart item
Route::prefix('cart-items')->group(function() {
    Route::post('/', [CartItemController::class, 'store']);
    Route::get('/', [CartItemController::class, 'index']);
    Route::patch('/{id}', [CartItemController::class, 'update']);
});

// user
Route::prefix('user')->group(function() {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/forget-password', [UserController::class, 'forgetPassword']);

});

//admin
Route::prefix('admin')->group(function() {
    Route::post('/login', [AdminController::class, 'login']);
    Route::post('/register', [AdminController::class, 'register']);
});

Route::get('bills/{id}/export', [BillController::class, 'export_bill']);
