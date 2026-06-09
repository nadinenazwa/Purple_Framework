<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;

class BarcodeController extends Controller
{
    /**
     * Tampilkan halaman scanner barcode/QR.
     */
    public function index()
    {
        return view('barcode.scanner');
    }

    /**
     * API: Cari barang berdasarkan id_barang yang di-scan.
     * GET /api/barcode/barang/{id}
     */
    public function findBarang($id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => "Barang dengan ID \"$id\" tidak ditemukan di database.",
            ], 404);
        }

        return response()->json([
            'success'   => true,
            'id_barang' => $barang->id_barang,
            'nama'      => $barang->nama,
            'harga'     => $barang->harga,
        ]);
    }
}
