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
use App\Http\Controllers\BarcodeController;

// Halaman awal: arahkan ke dashboard (yang dilindungi middleware)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ── Antrian: public routes (tidak butuh login) ─────────────
Route::get('/guest',         [App\Http\Controllers\AntrianController::class, 'guestForm'])->name('antrian.guest.pub');
Route::post('/guest',        [App\Http\Controllers\AntrianController::class, 'guestStore'])->name('antrian.guest.store.pub');
Route::get('/antrian/{id}',  [App\Http\Controllers\AntrianController::class, 'tiket'])->name('antrian.tiket.pub');
Route::get('/papan',         [App\Http\Controllers\AntrianController::class, 'papan'])->name('antrian.papan.pub');
Route::get('/api/antrian/poll', [App\Http\Controllers\AntrianController::class, 'poll'])->name('antrian.poll');

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
    Route::get('barang/labels', function () { 
        $barangs = \App\Models\Barang::all();
        return view('barang.labels', compact('barangs'));
    })->name('barang.labels');
    // POS (Point of Sales) page and API
    Route::get('pos', [POSController::class, 'index'])->name('pos.index');
    // Public POS ordering UI (separate file)
    Route::get('POS', function () { return view('pos_baru'); })->name('POS.menu');
    // My Order: customer view their saved QR codes
    Route::get('my-order', function () { return view('pos.my_order'); })->name('pos.my-order');

    // Public-friendly kantin (customer-facing) route -> POS ordering UI
    Route::get('kantin', [POSController::class, 'index'])->name('kantin.index');
    Route::get('api/pos/barang/{kode}', [POSController::class, 'getBarang']);
    // Midtrans: obtain snap token (returns JSON { snap_token })
    Route::get('midtrans/snap/{id}', [POSController::class, 'getSnapToken']);
    // Semua Pesanan Kantin (list)
    Route::get('pesanan', [POSController::class, 'allPesanan'])->name('pesanan.index');
    // Endpoint to serve order QR (image/png) for a given order id
    Route::get('pos/{id}/qr', [POSController::class, 'orderQr']);
    // Sync status endpoint (AJAX)
    Route::post('api/pesanan/sync/{id}', [POSController::class, 'syncStatus']);
    // Sync status endpoint (AJAX)
    Route::post('api/pesanan/sync/{id}', [POSController::class, 'syncStatus']);
    // Midtrans webhook/callback (public endpoint)
    Route::post('midtrans/callback', [POSController::class, 'midtransCallback'])->name('midtrans.callback');
    Route::post('api/pos/penjualan', [POSController::class, 'storePenjualan']);
        // API for vendor menus (used by ordering page via AJAX)
        Route::get('api/menus', [POSController::class, 'getMenusByVendor']);
    // Barcode & QR Code Scanner
    Route::get('barcode', [BarcodeController::class, 'index'])->name('barcode.index');
    Route::get('api/barcode/barang/{id}', [BarcodeController::class, 'findBarang'])->name('barcode.find');
    // Vendor: scan QR pesanan customer
    Route::get('api/vendor/scan/{orderId}', [App\Http\Controllers\VendorController::class, 'scanOrder'])->name('vendor.scan.order');

    // ---- Kunjungan Toko ----
    Route::get('toko',          [App\Http\Controllers\TokoController::class, 'index'])->name('toko.index');
    Route::get('toko/{barcode}',[App\Http\Controllers\TokoController::class, 'show'])->name('toko.show');
    Route::post('toko',         [App\Http\Controllers\TokoController::class, 'store'])->name('toko.store');
    Route::post('kunjungan',    [App\Http\Controllers\TokoController::class, 'kunjungan'])->name('toko.kunjungan');

    // ---- Antrian Digital SSE ----
    Route::get('guest',                    [App\Http\Controllers\AntrianController::class, 'guestForm'])->name('antrian.guest');
    Route::post('guest',                   [App\Http\Controllers\AntrianController::class, 'guestStore'])->name('antrian.guest.store');
    Route::get('antrian/{id}',             [App\Http\Controllers\AntrianController::class, 'tiket'])->name('antrian.tiket');
    Route::get('admin-antrian',            [App\Http\Controllers\AntrianController::class, 'adminDashboard'])->name('antrian.admin');
    Route::post('admin/panggil',           [App\Http\Controllers\AntrianController::class, 'panggil'])->name('antrian.panggil');
    Route::post('admin/panggil/{id}',      [App\Http\Controllers\AntrianController::class, 'panggilById'])->name('antrian.panggil.id');
    Route::post('admin/terlambat/{id}',    [App\Http\Controllers\AntrianController::class, 'terlambat'])->name('antrian.terlambat');
    Route::post('admin/panggil-terlambat/{id}', [App\Http\Controllers\AntrianController::class, 'panggilTerlambat'])->name('antrian.panggil.terlambat');
    Route::get('papan',                    [App\Http\Controllers\AntrianController::class, 'papan'])->name('antrian.papan');

    // Customer management (camera capture examples)
    Route::get('customer', [App\Http\Controllers\CustomerController::class, 'index'])->name('customer.index');
    Route::get('customer/create-blob', [App\Http\Controllers\CustomerController::class, 'createBlob'])->name('customer.create.blob');
    Route::post('customer/store-blob', [App\Http\Controllers\CustomerController::class, 'storeBlob'])->name('customer.store.blob');
    Route::get('customer/create-file', [App\Http\Controllers\CustomerController::class, 'createFile'])->name('customer.create.file');
    Route::post('customer/store-file', [App\Http\Controllers\CustomerController::class, 'storeFile'])->name('customer.store.file');
    // Combined form with address + modal camera (saves as BLOB)
    Route::get('customer/create', [App\Http\Controllers\CustomerController::class, 'createFull'])->name('customer.create');
    // Dedicated Kota page
    Route::get('kota', function () { return view('kota.index'); })->name('kota.index');

    // Use resource routes (map parameter to id_barang to match controller signatures)
    Route::resource('barang', BarangController::class)->parameters(['barang' => 'id_barang']);

    // Vendor area: dashboard & menu management
    Route::prefix('vendor')->name('vendor.')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\VendorController::class, 'dashboard'])->name('dashboard');
        // Scan QR Code customer
        Route::get('scan', [App\Http\Controllers\VendorController::class, 'scanPage'])->name('scan');
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

// ── NFC Absensi Routes ────────────────────────────────────────
use App\Http\Controllers\AbsensiController;

Route::get('/absensi',      [AbsensiController::class, 'index'])->name('absensi.index');
Route::post('/absensi',     [AbsensiController::class, 'store'])->name('absensi.store');
Route::get('/daftar-kartu', [AbsensiController::class, 'daftarKartu'])->name('absensi.daftarKartu');
Route::post('/daftar-kartu',[AbsensiController::class, 'simpanKartu'])->name('absensi.simpanKartu');
Route::get('/riwayat',      [AbsensiController::class, 'riwayat'])->name('absensi.riwayat');