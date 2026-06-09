<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LokasiToko;
use App\Models\KunjunganToko;

class TokoController extends Controller
{
    /* =====================================================
       index() — Halaman utama Kunjungan Toko
    ===================================================== */
    public function index()
    {
        $tokos = LokasiToko::orderBy('nama_toko')->get();
        return view('toko.index', compact('tokos'));
    }

    /* =====================================================
       show($barcode) — Ambil data toko by barcode (JSON)
    ===================================================== */
    public function show($barcode)
    {
        $toko = LokasiToko::find($barcode);

        if (! $toko) {
            return response()->json([
                'success' => false,
                'message' => "Toko dengan barcode \"$barcode\" tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'success'   => true,
            'barcode'   => $toko->barcode,
            'nama_toko' => $toko->nama_toko,
            'latitude'  => $toko->latitude,
            'longitude' => $toko->longitude,
            'accuracy'  => $toko->accuracy,
        ]);
    }

    /* =====================================================
       store() — Simpan data toko baru
    ===================================================== */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'barcode'   => 'required|string|max:8|unique:lokasi_toko,barcode',
            'nama_toko' => 'required|string|max:50',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy'  => 'required|numeric|min:0',
        ], [
            'barcode.unique'    => 'Barcode sudah terdaftar. Gunakan barcode lain.',
            'barcode.max'       => 'Barcode maksimal 8 karakter.',
            'latitude.required' => 'Lokasi GPS belum diambil. Klik tombol Geoloc terlebih dahulu.',
        ]);

        LokasiToko::create($validated);

        return redirect()->route('toko.index')
                         ->with('success', "Toko \"{$validated['nama_toko']}\" berhasil disimpan.");
    }

    /* =====================================================
       kunjungan() — Validasi lokasi sales vs toko
    ===================================================== */
    public function kunjungan(Request $request)
    {
        $request->validate([
            'barcode'   => 'required|string|exists:lokasi_toko,barcode',
            'lat_sales' => 'required|numeric',
            'lng_sales' => 'required|numeric',
            'acc_sales' => 'required|numeric|min:0',
        ]);

        $toko = LokasiToko::findOrFail($request->barcode);

        $jarak = $this->haversine(
            $toko->latitude,
            $toko->longitude,
            $request->lat_sales,
            $request->lng_sales
        );

        $threshold          = 300; // meter
        $threshold_efektif  = $threshold + $toko->accuracy + $request->acc_sales;
        $status             = ($jarak <= $threshold_efektif) ? 'DITERIMA' : 'DITOLAK';

        // Simpan log kunjungan
        KunjunganToko::create([
            'barcode_toko'      => $toko->barcode,
            'nama_toko'         => $toko->nama_toko,
            'lat_toko'          => $toko->latitude,
            'lng_toko'          => $toko->longitude,
            'acc_toko'          => $toko->accuracy,
            'lat_sales'         => $request->lat_sales,
            'lng_sales'         => $request->lng_sales,
            'acc_sales'         => $request->acc_sales,
            'jarak_meter'       => round($jarak, 2),
            'threshold_efektif' => round($threshold_efektif, 2),
            'status'            => $status,
        ]);

        return response()->json([
            'success'           => true,
            'status'            => $status,
            'jarak_meter'       => round($jarak, 2),
            'threshold'         => $threshold,
            'threshold_efektif' => round($threshold_efektif, 2),
            'acc_toko'          => $toko->accuracy,
            'acc_sales'         => $request->acc_sales,
            'lat_sales'         => $request->lat_sales,
            'lng_sales'         => $request->lng_sales,
            'nama_toko'         => $toko->nama_toko,
        ]);
    }

    /* =====================================================
       haversine() — Hitung jarak dua koordinat (meter)
    ===================================================== */
    private function haversine($lat1, $lng1, $lat2, $lng2): float
    {
        $R    = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c    = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}
