<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Mahasiswa;
use App\Models\Absensi;

class AbsensiController extends Controller
{
    // ─── HALAMAN SCAN ABSENSI (/absensi) ────────────────────

    public function index()
    {
        return view('absensi.scan');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nfc_serial' => 'required|string',
        ]);

        // Normalize serial
        $nfcSerial = strtolower(str_replace(['-', ' '], ':', $request->nfc_serial));

        // Cari mahasiswa berdasarkan NFC serial
        $mahasiswa = Mahasiswa::whereRaw('LOWER(nfc_serial) = ?', [$nfcSerial])->first();

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu NFC belum terdaftar. Silakan daftarkan kartu terlebih dahulu.',
            ], 404);
        }

        // Cek apakah sudah absen hari ini
        $today = Carbon::today();
        $sudahAbsen = Absensi::where('mahasiswa_id', $mahasiswa->id)
            ->whereDate('waktu', $today)
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah absen hari ini.',
            ], 409);
        }

        $now = Carbon::now();

        // Simpan ke database
        Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'waktu'        => $now,
        ]);

        return response()->json([
            'success' => true,
            'nama'    => $mahasiswa->nama,
            'nim'     => $mahasiswa->nim,
            'waktu'   => $now->format('H:i:s'),
            'tanggal' => $now->format('d M Y'),
        ]);
    }

    // ─── HALAMAN DAFTAR KARTU (/daftar-kartu) ───────────────

    public function daftarKartu()
    {
        $mahasiswas = Mahasiswa::all();
        return view('absensi.daftar_kartu', compact('mahasiswas'));
    }

    public function simpanKartu(Request $request)
    {
        $request->validate([
            'nim'        => 'required|string',
            'nama'       => 'required|string',
            'nfc_serial' => 'required|string',
        ]);

        $nfcSerial = strtolower(str_replace(['-', ' '], ':', $request->nfc_serial));

        // Cek apakah serial sudah dipakai mahasiswa lain
        $existing = Mahasiswa::whereRaw('LOWER(nfc_serial) = ?', [$nfcSerial])->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu NFC ini sudah terdaftar untuk ' . $existing->nama . ' (' . $existing->nim . ').',
            ], 409);
        }

        // Cari mahasiswa by NIM, atau buat baru
        $mahasiswa = Mahasiswa::firstOrNew(['nim' => $request->nim]);
        $mahasiswa->nama       = $request->nama;
        $mahasiswa->nfc_serial = $nfcSerial;
        $mahasiswa->save();

        return response()->json([
            'success'    => true,
            'message'    => 'Kartu NFC berhasil didaftarkan untuk ' . $mahasiswa->nama . '.',
            'nama'       => $mahasiswa->nama,
            'nim'        => $mahasiswa->nim,
            'nfc_serial' => $mahasiswa->nfc_serial,
        ]);
    }

    // ─── HALAMAN RIWAYAT (/riwayat) ─────────────────────────

    public function riwayat(Request $request)
    {
        $query = Absensi::with('mahasiswa');

        // Filter
        if ($request->filled('tanggal')) {
            $query->whereDate('waktu', $request->tanggal);
        }

        // Sort terbaru dulu
        $absensis = $query->orderByDesc('waktu')->get();

        // Total
        $totalAbsensi = Absensi::count();

        return view('absensi.riwayat', compact('absensis', 'totalAbsensi'));
    }
}
