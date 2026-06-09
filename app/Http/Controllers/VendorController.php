<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VendorController extends Controller
{
    // Dashboard: show pesanan with status_bayar = 'Lunas' belonging to this vendor
    public function dashboard()
    {
        $user = Auth::user();
        $table = Schema::hasTable('pesanan') ? 'pesanan' : 'penjualans';

        if (! $table) {
            abort(404, 'No orders table found');
        }

        $query = DB::table($table)->where('status_bayar', 'Lunas');
        // if vendor_id column exists, filter by vendor
        if (Schema::hasColumn($table, 'vendor_id')) {
            $query->where('vendor_id', $user->id);
        }

        // choose a safe order column depending on table schema
        $columns = Schema::getColumnListing($table);
        $preferred = ['id', 'id_pesanan', 'id_penjualan', 'id_order', 'order_id', 'created_at', 'timestamp'];
        $orderCol = null;
        foreach ($preferred as $col) {
            if (in_array($col, $columns)) { $orderCol = $col; break; }
        }
        if ($orderCol) {
            $query->orderBy($orderCol, 'desc');
        }

        $orders = $query->get();
        return view('vendor.dashboard', compact('orders'));
    }

    /**
     * Halaman scanner QR untuk vendor.
     */
    public function scanPage()
    {
        return view('vendor.scan');
    }

    /**
     * API: lookup pesanan by order_id (hasil scan QR).
     * Hanya mengembalikan detail item yang dimiliki vendor yang login.
     *
     * GET /api/vendor/scan/{orderId}
     */
    public function scanOrder($orderId)
    {
        $user  = Auth::user();
        $table = Schema::hasTable('pesanan') ? 'pesanan' : (Schema::hasTable('penjualans') ? 'penjualans' : null);

        if (! $table) {
            return response()->json(['success' => false, 'message' => 'Tabel pesanan tidak ditemukan.'], 500);
        }

        // ---- Normalkan orderId dari berbagai format QR yang pernah dibuat ----
        // Format yang dikenal:
        //   1. "PESANAN-37"          -> numeric 37
        //   2. "order-penjualans-37-1716900000" -> numeric 37 (extractable)
        //   3. "37"                  -> numeric 37
        //   4. string order_id penuh (Midtrans) -> cocok langsung di kolom order_id

        $orderIdCols = ['order_id', 'id_order', 'idorder'];
        $numericCols = ['id', 'id_pesanan', 'idpesanan', 'id_penjualan', 'idpenjualan'];

        // Ekstrak angka dari format "PESANAN-{id}" atau "order-{table}-{id}-{ts}"
        $extractedNumericId = null;
        if (preg_match('/^PESANAN-(\d+)$/i', $orderId, $m)) {
            $extractedNumericId = (int) $m[1];
        } elseif (preg_match('/^order-\w+-(\d+)-\d+$/', $orderId, $m)) {
            $extractedNumericId = (int) $m[1];
        } elseif (is_numeric($orderId)) {
            $extractedNumericId = (int) $orderId;
        }

        $order = null;

        // 1) Cari exact match di kolom order_id (Midtrans order_id, dll.)
        foreach ($orderIdCols as $col) {
            if (Schema::hasColumn($table, $col)) {
                $order = DB::table($table)->where($col, $orderId)->first();
                if ($order) break;
            }
        }

        // 2) Cari dengan numeric id yang diekstrak (PESANAN-37, order-penjualans-37-..., "37")
        if (! $order && $extractedNumericId !== null) {
            foreach ($numericCols as $col) {
                if (Schema::hasColumn($table, $col)) {
                    $order = DB::table($table)->where($col, $extractedNumericId)->first();
                    if ($order) break;
                }
            }
        }

        // 3) Cari juga di kolom order_id dengan numeric (jika order_id = "37")
        if (! $order && $extractedNumericId !== null) {
            foreach ($orderIdCols as $col) {
                if (Schema::hasColumn($table, $col)) {
                    $order = DB::table($table)->where($col, 'like', '%-' . $extractedNumericId . '-%')->first();
                    if ($order) break;
                }
            }
        }

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan dengan ID "' . $orderId . '" tidak ditemukan.',
            ], 404);
        }

        // ---- Ambil detail pesanan ----
        $detailTable = $table === 'pesanan' ? 'detail_pesanan' : 'penjualan_detail';
        $details     = [];

        if (Schema::hasTable($detailTable)) {
            // Deteksi FK column
            $fkCandidates = $table === 'pesanan'
                ? ['id_pesanan', 'idpesanan', 'pesanan_id', 'id_order', 'order_id', 'id']
                : ['id_penjualan', 'idpenjualan', 'penjualan_id', 'id_order', 'order_id', 'id'];
            $fkCol = null;
            foreach ($fkCandidates as $c) {
                if (Schema::hasColumn($detailTable, $c)) { $fkCol = $c; break; }
            }

            // Gunakan numeric id order untuk FK lookup
            $numId = null;
            foreach ($numericCols as $c) {
                if (isset($order->{$c})) { $numId = $order->{$c}; break; }
            }

            $rows = $fkCol && $numId !== null
                ? DB::table($detailTable)->where($fkCol, $numId)->get()
                : collect();

            foreach ($rows as $d) {
                // Deteksi kolom menu/barang id
                $mid = $d->id_menu ?? $d->idmenu ?? $d->menu_id ?? $d->id_barang ?? $d->idbarang ?? null;

                // ---- Cari nama menu & cek kepemilikan vendor ----
                $menuName   = null;
                $menuHarga  = $d->subtotal ?? null;
                $isMyMenu   = false;
                $vendorName = null;

                // Cek tabel menus (modern)
                if ($mid && Schema::hasTable('menus')) {
                    $m = DB::table('menus')->where('id', $mid)->first();
                    if ($m) {
                        $menuName  = $m->name ?? $m->nama ?? null;
                        $menuHarga = $m->price ?? $m->harga ?? $menuHarga;
                        $ownerId   = $m->user_id ?? $m->idvendor ?? null;
                        $isMyMenu  = ($ownerId == $user->id);
                        $vendorName = DB::table('users')->where('id', $ownerId)->value('name');
                    }
                }

                // Cek tabel menu (legacy)
                if ($mid && ! $menuName && Schema::hasTable('menu')) {
                    $m2 = DB::table('menu')->where('idmenu', $mid)->first();
                    if ($m2) {
                        $menuName  = $m2->nama_menu ?? null;
                        $menuHarga = $m2->harga ?? $menuHarga;
                        $ownerId   = $m2->idvendor ?? null;
                        $isMyMenu  = ($ownerId == $user->id);
                        $vendorName = DB::table('users')->where('id', $ownerId)->value('name');
                    }
                }

                // Fallback ke tabel barang
                if ($mid && ! $menuName && Schema::hasTable('barang')) {
                    $b = DB::table('barang')->where('id_barang', $mid)->first();
                    if ($b) {
                        $menuName = $b->nama ?? $b->nama_barang ?? null;
                        $menuHarga = $b->harga ?? $menuHarga;
                        // Barang tidak terikat vendor → tampilkan semua untuk admin
                        $isMyMenu = true;
                    }
                }

                $details[] = [
                    'id_menu'    => $mid,
                    'nama'       => $menuName ?? ('Item #' . $mid),
                    'harga'      => $menuHarga ?? 0,
                    'jumlah'     => $d->jumlah ?? 1,
                    'subtotal'   => $d->subtotal ?? 0,
                    'catatan'    => $d->catatan ?? null,
                    'is_my_menu' => $isMyMenu,
                    'vendor'     => $vendorName,
                ];
            }
        }

        // Filter: hanya tampilkan menu milik vendor ini
        $myItems = array_values(array_filter($details, fn($d) => $d['is_my_menu']));
        // Jika vendor adalah admin (semua menu) atau tidak ada filter → tampilkan semua
        if (empty($myItems)) {
            $myItems = $details;
        }

        // ---- Data pesanan ----
        $statusBayar = $order->status_bayar ?? ($order->status ?? 'Belum');
        $totalOrder  = $order->total ?? $order->grand_total ?? 0;
        $namaPemesan = $order->nama ?? $order->nama_pembeli ?? 'Guest';
        $createdAt   = $order->created_at ?? $order->timestamp ?? $order->waktu ?? null;

        return response()->json([
            'success'      => true,
            'order_id'     => $orderId,
            'nama_pemesan' => $namaPemesan,
            'status_bayar' => $statusBayar,
            'total'        => $totalOrder,
            'created_at'   => $createdAt,
            'items'        => $myItems,
            'all_items'    => $details,
        ]);
    }
}
