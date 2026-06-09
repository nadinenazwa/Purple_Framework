<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * Data dummy mahasiswa (tanpa tabel DB).
     */
    private static function getMahasiswas(): array
    {
        return [
            ['id' => 1, 'nim' => '2021001', 'nama' => 'Andi Pratama',    'nfc_serial' => '04:ab:cd:ef:11:22:33'],
            ['id' => 2, 'nim' => '2021002', 'nama' => 'Budi Santoso',    'nfc_serial' => '04:ab:cd:ef:44:55:66'],
            ['id' => 3, 'nim' => '2021003', 'nama' => 'Citra Dewi',      'nfc_serial' => '04:ab:cd:ef:77:88:99'],
            ['id' => 4, 'nim' => '2021004', 'nama' => 'Doni Firmansyah', 'nfc_serial' => '04:ab:cd:ef:aa:bb:cc'],
            ['id' => 5, 'nim' => '2021005', 'nama' => 'Eka Putri',       'nfc_serial' => '04:ab:cd:ef:dd:ee:ff'],
        ];
    }

    /**
     * Data dummy matakuliah (tanpa tabel DB).
     */
    private static function getMatakuliahs(): array
    {
        return [
            ['id' => 1, 'kode' => 'PWA001', 'nama' => 'Pemrograman Web Aplikasi'],
            ['id' => 2, 'kode' => 'MBL002', 'nama' => 'Pemrograman Mobile'],
            ['id' => 3, 'kode' => 'BDS003', 'nama' => 'Basis Data Lanjut'],
        ];
    }

    /**
     * Data dummy riwayat absensi awal.
     */
    private static function getDefaultAbsensi(): array
    {
        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        return [
            ['mahasiswa_id' => 1, 'matakuliah_id' => 1, 'waktu' => $today . ' 08:00:00', 'status' => 'hadir'],
            ['mahasiswa_id' => 2, 'matakuliah_id' => 1, 'waktu' => $today . ' 08:20:00', 'status' => 'terlambat'],
            ['mahasiswa_id' => 3, 'matakuliah_id' => 1, 'waktu' => $today . ' 07:58:00', 'status' => 'hadir'],
            ['mahasiswa_id' => 4, 'matakuliah_id' => 2, 'waktu' => $yesterday . ' 09:00:00', 'status' => 'hadir'],
            ['mahasiswa_id' => 5, 'matakuliah_id' => 2, 'waktu' => $yesterday . ' 09:30:00', 'status' => 'terlambat'],
        ];
    }

    /**
     * Ambil riwayat absensi dari session, atau inisialisasi dengan data dummy.
     */
    private function getAbsensiList(Request $request): array
    {
        if (!$request->session()->has('absensi_nfc')) {
            $request->session()->put('absensi_nfc', self::getDefaultAbsensi());
        }
        return $request->session()->get('absensi_nfc', []);
    }

    /**
     * Helper: cari mahasiswa by key.
     */
    private function findMahasiswa(string $key, $value): ?array
    {
        foreach (self::getMahasiswas() as $mhs) {
            if (strtolower((string) $mhs[$key]) === strtolower((string) $value)) {
                return $mhs;
            }
        }
        return null;
    }

    /**
     * Helper: cari matakuliah by id.
     */
    private function findMatakuliah(int $id): ?array
    {
        foreach (self::getMatakuliahs() as $mk) {
            if ($mk['id'] === $id) {
                return $mk;
            }
        }
        return null;
    }

    // ─── HALAMAN SCAN ABSENSI (/absensi) ────────────────────

    public function index()
    {
        $matakuliahs = collect(self::getMatakuliahs());
        return view('absensi.scan', compact('matakuliahs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nfc_serial'    => 'required|string',
            'matakuliah_id' => 'required|integer',
        ]);

        // Normalize serial
        $nfcSerial = strtolower(str_replace(['-', ' '], ':', $request->nfc_serial));

        // Cari mahasiswa
        $mahasiswa = $this->findMahasiswa('nfc_serial', $nfcSerial);

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu NFC belum terdaftar. Silakan daftarkan kartu terlebih dahulu.',
            ], 404);
        }

        // Cari matakuliah
        $matakuliah = $this->findMatakuliah((int) $request->matakuliah_id);
        if (!$matakuliah) {
            return response()->json([
                'success' => false,
                'message' => 'Matakuliah tidak ditemukan.',
            ], 404);
        }

        // Cek apakah sudah absen hari ini untuk matakuliah yang sama
        $absensiList = $this->getAbsensiList($request);
        $today = Carbon::today()->format('Y-m-d');

        foreach ($absensiList as $a) {
            if (
                $a['mahasiswa_id'] === $mahasiswa['id'] &&
                $a['matakuliah_id'] === $matakuliah['id'] &&
                str_starts_with($a['waktu'], $today)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sudah absen hari ini untuk matakuliah ini.',
                ], 409);
            }
        }

        // Tentukan status: hadir jika menit <= 15, terlambat jika lewat
        $now = Carbon::now();
        $status = $now->minute <= 15 ? 'hadir' : 'terlambat';

        // Simpan ke session
        $absensiList[] = [
            'mahasiswa_id'  => $mahasiswa['id'],
            'matakuliah_id' => $matakuliah['id'],
            'waktu'         => $now->format('Y-m-d H:i:s'),
            'status'        => $status,
        ];
        $request->session()->put('absensi_nfc', $absensiList);

        return response()->json([
            'success'    => true,
            'nama'       => $mahasiswa['nama'],
            'nim'        => $mahasiswa['nim'],
            'waktu'      => $now->format('H:i:s'),
            'tanggal'    => $now->format('d M Y'),
            'status'     => $status,
            'matakuliah' => $matakuliah['nama'],
        ]);
    }

    // ─── HALAMAN DAFTAR KARTU (/daftar-kartu) ───────────────

    public function daftarKartu()
    {
        $mahasiswas = collect(self::getMahasiswas());
        return view('absensi.daftar_kartu', compact('mahasiswas'));
    }

    public function simpanKartu(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|integer',
            'nfc_serial'   => 'required|string',
        ]);

        $mahasiswa = $this->findMahasiswa('id', (int) $request->mahasiswa_id);

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan.',
            ], 404);
        }

        // Karena pakai data dummy (bukan DB), kita simulasi sukses saja
        return response()->json([
            'success'    => true,
            'message'    => 'Kartu NFC berhasil didaftarkan untuk ' . $mahasiswa['nama'] . '.',
            'nama'       => $mahasiswa['nama'],
            'nim'        => $mahasiswa['nim'],
            'nfc_serial' => $request->nfc_serial,
        ]);
    }

    // ─── HALAMAN RIWAYAT (/riwayat) ─────────────────────────

    public function riwayat(Request $request)
    {
        $matakuliahs = collect(self::getMatakuliahs());
        $mahasiswas  = collect(self::getMahasiswas());
        $absensiList = collect($this->getAbsensiList($request));

        // Filter
        if ($request->filled('matakuliah_id')) {
            $absensiList = $absensiList->where('matakuliah_id', (int) $request->matakuliah_id);
        }
        if ($request->filled('tanggal')) {
            $absensiList = $absensiList->filter(fn($a) => str_starts_with($a['waktu'], $request->tanggal));
        }
        if ($request->filled('status')) {
            $absensiList = $absensiList->where('status', $request->status);
        }

        // Sort terbaru dulu
        $absensiList = $absensiList->sortByDesc('waktu')->values();

        // Gabungkan data relasi (join manual)
        $absensis = $absensiList->map(function ($a) use ($mahasiswas, $matakuliahs) {
            $mhs = $mahasiswas->firstWhere('id', $a['mahasiswa_id']);
            $mk  = $matakuliahs->firstWhere('id', $a['matakuliah_id']);
            return array_merge($a, [
                'nim'             => $mhs['nim'] ?? '-',
                'nama_mahasiswa'  => $mhs['nama'] ?? '-',
                'kode_matakuliah' => $mk['kode'] ?? '-',
                'nama_matakuliah' => $mk['nama'] ?? '-',
                'waktu_carbon'    => Carbon::parse($a['waktu']),
            ]);
        });

        // Total hadir per matakuliah (tanpa filter)
        $allAbsensi = collect($this->getAbsensiList($request));
        $totalHadir = $allAbsensi->where('status', 'hadir')
            ->groupBy('matakuliah_id')
            ->map(function ($group, $mkId) use ($matakuliahs) {
                $mk = $matakuliahs->firstWhere('id', $mkId);
                return [
                    'total' => $group->count(),
                    'nama'  => $mk['nama'] ?? '-',
                ];
            });

        return view('absensi.riwayat', compact('absensis', 'matakuliahs', 'totalHadir'));
    }
}
