<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $totalBooks = Buku::count();
        $totalCategories = Kategori::count();

        // simple category distribution for optional charts
        $categoryDistribution = DB::table('kategori')
            ->leftJoin('buku', 'kategori.idkategori', '=', 'buku.idkategori')
            ->select('kategori.nama_kategori', DB::raw('COUNT(buku.idbuku) as total'))
            ->groupBy('kategori.nama_kategori')
            ->pluck('total', 'nama_kategori')
            ->toArray();

        return view('home', compact('totalBooks', 'totalCategories', 'categoryDistribution'));
    }
}
