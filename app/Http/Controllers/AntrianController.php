<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AntrianController extends Controller
{
    // ── Cache key ──────────────────────────────────────────────
    const CACHE_KEY = 'antrian_data';

    // ── Rebuild & store cache from DB ──────────────────────────
    private function refreshCache(): void
    {
        $dipanggil = Antrian::where('status', 'dipanggil')
            ->orderBy('updated_at', 'desc')->first();

        $menunggu = Antrian::where('status', 'menunggu')
            ->orderBy('nomor')->get(['id','nomor','nama']);

        $terlambat = Antrian::where('status', 'terlambat')
            ->orderBy('nomor')->get(['id','nomor','nama']);

        Cache::forever(self::CACHE_KEY, [
            'sedang_dipanggil' => $dipanggil
                ? ['id' => $dipanggil->id, 'nomor' => $dipanggil->nomor, 'nama' => $dipanggil->nama]
                : null,
            'menunggu'  => $menunggu->toArray(),
            'terlambat' => $terlambat->toArray(),
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // GUEST
    // ═══════════════════════════════════════════════════════════

    public function guestForm()
    {
        return view('antrian.guest');
    }

    public function guestStore(Request $request)
    {
        $request->validate(['nama' => 'required|string|max:100']);

        $nomor = (Antrian::max('nomor') ?? 0) + 1;

        $antrian = Antrian::create([
            'nama'   => trim($request->nama),
            'nomor'  => $nomor,
            'status' => 'menunggu',
        ]);

        $this->refreshCache();

        return redirect()->route('antrian.tiket', $antrian->id)
                         ->with('success', 'Nomor antrian Anda: ' . $nomor);
    }

    public function tiket($id)
    {
        $antrian = Antrian::findOrFail($id);
        return view('antrian.tiket', compact('antrian'));
    }

    // ═══════════════════════════════════════════════════════════
    // ADMIN
    // ═══════════════════════════════════════════════════════════

    public function adminDashboard()
    {
        $this->refreshCache();
        return view('antrian.admin');
    }

    /** Panggil antrian berikutnya dari daftar menunggu */
    public function panggil(Request $request)
    {
        // Tandai yang sedang dipanggil → selesai
        Antrian::where('status', 'dipanggil')->update(['status' => 'selesai']);

        $next = Antrian::where('status', 'menunggu')->orderBy('nomor')->first();

        if (! $next) {
            return response()->json(['success' => false, 'message' => 'Antrian kosong.'], 422);
        }

        $next->update(['status' => 'dipanggil']);
        $this->refreshCache();

        return response()->json([
            'success' => true,
            'antrian' => ['id' => $next->id, 'nomor' => $next->nomor, 'nama' => $next->nama],
        ]);
    }

    /** Panggil antrian tertentu berdasarkan ID (tidak harus urut) */
    public function panggilById($id)
    {
        Antrian::where('status', 'dipanggil')->update(['status' => 'selesai']);

        $antrian = Antrian::findOrFail($id);
        $antrian->update(['status' => 'dipanggil']);
        $this->refreshCache();

        return response()->json([
            'success' => true,
            'antrian' => ['id' => $antrian->id, 'nomor' => $antrian->nomor, 'nama' => $antrian->nama],
        ]);
    }

    /** Tandai antrian sebagai terlambat */
    public function terlambat($id)
    {
        $antrian = Antrian::findOrFail($id);
        $antrian->update(['status' => 'terlambat']);
        $this->refreshCache();
        return response()->json(['success' => true]);
    }

    /** Panggil ulang antrian terlambat */
    public function panggilTerlambat($id)
    {
        Antrian::where('status', 'dipanggil')->update(['status' => 'selesai']);

        $antrian = Antrian::findOrFail($id);
        $antrian->update(['status' => 'dipanggil']);
        $this->refreshCache();

        return response()->json([
            'success' => true,
            'antrian' => ['id' => $antrian->id, 'nomor' => $antrian->nomor, 'nama' => $antrian->nama],
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // PAPAN
    // ═══════════════════════════════════════════════════════════

    public function papan()
    {
        return view('antrian.papan');
    }

    // ═══════════════════════════════════════════════════════════
    // POLLING ENDPOINT (replaces SSE — avoids blocking workers)
    // ═══════════════════════════════════════════════════════════

    public function poll(Request $request)
    {
        $data = Cache::get(self::CACHE_KEY, [
            'sedang_dipanggil' => null,
            'menunggu'         => [],
            'terlambat'        => [],
        ]);

        return response()->json($data);
    }
}
