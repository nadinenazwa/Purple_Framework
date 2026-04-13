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
}
