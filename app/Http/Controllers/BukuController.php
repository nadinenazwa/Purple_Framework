<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buku;
use App\Models\Kategori;


class BukuController extends Controller
{
    public function index()
    {
        $bukus = Buku::with('kategori')->paginate(10);
        return view('buku.index', compact('bukus'));
    }

    public function create()
    {
        $kategoris = Kategori::all();
        return view('buku.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:100',
            'judul' => 'required|string|max:255',
            'pengarang' => 'required|string|max:255',
            'idkategori' => 'required|exists:kategori,idkategori',
        ]);

        Buku::create($data);

        return redirect()->route('buku.index')->with('success', 'Buku berhasil dibuat.');
    }

    public function edit(Buku $buku)
    {
        $kategoris = Kategori::all();
        return view('buku.edit', compact('buku', 'kategoris'));
    }

    public function update(Request $request, Buku $buku)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:100',
            'judul' => 'required|string|max:255',
            'pengarang' => 'required|string|max:255',
            'idkategori' => 'required|exists:kategori,idkategori',
        ]);

        $buku->update($data);

        return redirect()->route('buku.index')->with('success', 'Buku berhasil diupdate.');
    }

    public function destroy(Buku $buku)
    {
        $buku->delete();
        return redirect()->route('buku.index')->with('success', 'Buku berhasil dihapus.');
    }
}
