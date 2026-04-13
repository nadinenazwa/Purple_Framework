<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\POSController;

// Halaman awal: arahkan ke dashboard (yang dilindungi middleware)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Redirect plural '/barangs' to '/barang' (folder is named 'barang')
Route::redirect('/barang', '/barang');

// Custom minimal auth routes (login/logout) — memastikan route `login` tersedia
use App\Http\Controllers\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    // Google SSO
    Route::get('auth/google', [App\Http\Controllers\SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [App\Http\Controllers\SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Route otomatis untuk Login, Register, dll
Auth::routes();

// Semua menu yang butuh login taruh di dalam grup ini
Route::middleware(['auth'])->group(function () {
    
    // Ini yang bikin dashboard kamu jalan
    Route::get('/home', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [HomeController::class, 'index']); 

    // Route untuk menu lainnya
    Route::resource('buku', BukuController::class);
    Route::resource('kategori', KategoriController::class);
    // Barang CRUD + DataTables + print labels
    // DataTables JSON endpoint for barang list (place this before the resource routes
    // so it won't be captured by resource show route)
    Route::get('barang/data', [BarangController::class, 'data'])->name('barang.data');

    // Demo pages: plain table and DataTables demo for local testing (not persisted)
    // Place demo routes BEFORE the resource routes so they are not matched as {id_barang}
    Route::get('barang/demo', function () { return view('barang.demo_plain'); })->name('barang.demo_plain');
    Route::get('barang/demo-dt', function () { return view('barang.demo_datatables'); })->name('barang.demo_datatables');
    // Kota labels/demo page
    Route::get('barang/labels', function () { return view('barang.labels'); })->name('barang.labels');
    // POS (Point of Sales) page and API
    Route::get('pos', [POSController::class, 'index'])->name('pos.index');
    // Public-friendly kantin (customer-facing) route -> POS ordering UI
    Route::get('kantin', [POSController::class, 'index'])->name('kantin.index');
    Route::get('api/pos/barang/{kode}', [POSController::class, 'getBarang']);
    // Midtrans: obtain snap token (returns JSON { snap_token })
    Route::get('midtrans/snap/{id}', [POSController::class, 'getSnapToken']);
    // Semua Pesanan Kantin (list)
    Route::get('pesanan', [POSController::class, 'allPesanan'])->name('pesanan.index');
    // Sync status endpoint (AJAX)
    Route::post('api/pesanan/sync/{id}', [POSController::class, 'syncStatus']);
    // Sync status endpoint (AJAX)
    Route::post('api/pesanan/sync/{id}', [POSController::class, 'syncStatus']);
    // Midtrans webhook/callback (public endpoint)
    Route::post('midtrans/callback', [POSController::class, 'midtransCallback'])->name('midtrans.callback');
    Route::post('api/pos/penjualan', [POSController::class, 'storePenjualan']);
        // API for vendor menus (used by ordering page via AJAX)
        Route::get('api/menus', [POSController::class, 'getMenusByVendor']);
    // Dedicated Kota page
    Route::get('kota', function () { return view('kota.index'); })->name('kota.index');

    // Use resource routes (map parameter to id_barang to match controller signatures)
    Route::resource('barang', BarangController::class)->parameters(['barang' => 'id_barang']);

    // Vendor area: dashboard & menu management
    Route::prefix('vendor')->name('vendor.')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\VendorController::class, 'dashboard'])->name('dashboard');
        Route::resource('menus', App\Http\Controllers\MenuController::class)->names([
            'index' => 'menus.index',
            'create' => 'menus.create',
            'store' => 'menus.store',
            'edit' => 'menus.edit',
            'update' => 'menus.update',
            'destroy' => 'menus.destroy',
        ]);
        // Note: vendor kantin & pembayaran routes removed (menu deleted)
    });

    // Master data for admin: vendors and menus
    Route::get('master/vendors', [App\Http\Controllers\MasterDataController::class, 'vendors'])->name('master.vendors');
    Route::post('master/vendors', [App\Http\Controllers\MasterDataController::class, 'storeVendor']);
    Route::get('master/menus', [App\Http\Controllers\MasterDataController::class, 'menus'])->name('master.menus');
    Route::post('master/menus', [App\Http\Controllers\MasterDataController::class, 'storeMenu']);
    // Master menus AJAX for filtering by vendor
    Route::get('api/master/menus', [App\Http\Controllers\MasterDataController::class, 'menusAjax']);
    // Edit/update master menu (admin)
    Route::get('master/menus/{id}/edit', [App\Http\Controllers\MasterDataController::class, 'editMenu'])->name('master.menus.edit');
    Route::put('master/menus/{id}', [App\Http\Controllers\MasterDataController::class, 'updateMenu'])->name('master.menus.update');
    // Delete menu
    Route::delete('master/menus/{id}', [App\Http\Controllers\MasterDataController::class, 'destroyMenu'])->name('master.menus.destroy');

    // Named print route used by the view (accepts selected ids and start coordinates)
    Route::post('/barangs/print', [BarangController::class, 'printLabels'])->name('barangs.print');
});
// OTP routes (accessible before final login completion)
Route::get('otp', [App\Http\Controllers\OtpController::class, 'show'])->name('otp.show');
Route::post('otp/verify', [App\Http\Controllers\OtpController::class, 'verify'])->name('otp.verify');

// PDF example routes (certificate = landscape A4, announcement = portrait A4

Route::get('pdf/certificate', [PdfController::class, 'certificate'])->name('pdf.certificate');
Route::get('pdf/announcement', [PdfController::class, 'announcement'])->name('pdf.announcement');

// Wilayah demo and API (dependent selects)
Route::get('wilayah', [WilayahController::class, 'index'])->name('wilayah.index');
Route::get('api/wilayah/provinces', [WilayahController::class, 'provinces']);
Route::get('api/wilayah/regencies/{provinceId}', [WilayahController::class, 'regencies']);
Route::get('api/wilayah/districts/{regencyId}', [WilayahController::class, 'districts']);
Route::get('api/wilayah/villages/{districtId}', [WilayahController::class, 'villages']);