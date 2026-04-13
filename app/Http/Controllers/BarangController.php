<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class BarangController extends Controller
{
    public function index()
    {
        $barang = Barang::all();
        return view('barang.index', compact('barang'));
    }

    // DataTables JSON endpoint
    public function data()
    {
        $barang = Barang::all();

        // Prepare plain array compatible with DataTables AJAX (expects { data: [...] })
        $data = $barang->map(function ($b) {
            return [
                // use id_barang as the checkbox value because the table primary key is `id_barang`
                'checkbox' => '<input type="checkbox" class="select-item" value="' . e($b->id_barang) . '">',
                'id_barang' => $b->id_barang,
                'nama' => $b->nama,
                'harga' => number_format($b->harga, 0, ',', '.'),
                // use id_barang when generating resource routes
                'actions' => '<a href="' . route('barang.edit', $b->id_barang) . '" class="btn btn-sm btn-primary">Edit</a> '
                    . '<form method="POST" action="' . route('barang.destroy', $b->id_barang) . '" style="display:inline">'
                    . csrf_field() . method_field('DELETE')
                    . '<button class="btn btn-sm btn-danger" onclick="return confirm(\'Hapus?\')">Hapus</button></form>'
            ];
        })->values()->all();

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        return view('barang.create');
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'nama' => 'required|string',
            'harga' => 'required|numeric',
        ]);
        if ($v->fails()) return redirect()->back()->withErrors($v)->withInput();

        Barang::create($request->only(['nama','kategori','harga']));
        return redirect()->route('barang.index')->with('success','Barang ditambahkan');
    }

    public function edit(Barang $id_barang)
    {
        $barang = $id_barang;
        return view('barang.edit', compact('barang'));
    }

    public function update(Request $request, Barang $id_barang)
    {
        $barang = $id_barang;
        $v = Validator::make($request->all(), [
            'nama' => 'required|string',
            'harga' => 'required|numeric',
        ]);
        if ($v->fails()) return redirect()->back()->withErrors($v)->withInput();

        $barang->update($request->only(['nama','kategori','harga']));
        return redirect()->route('barang.index')->with('success','Barang diupdate');
    }

    public function destroy(Barang $id_barang)
    {
        $barang = $id_barang;
        $barang->delete();
        return redirect()->route('barang.index')->with('success','Barang dihapus');
    }

    // Generate PDF labels; accepts selected ids and start coordinates x,y (1-based)
    public function printLabels(Request $request)
    {
        $ids = $request->input('ids', []);
        $startX = max(1, (int)$request->input('start_x', 1));
        $startY = max(1, (int)$request->input('start_y', 1));

        // select by primary key `id_barang`
        $items = Barang::whereIn('id_barang', $ids)->get();

        // Generate barcode images (base64 PNG) for each item when generator is available.
        foreach ($items as $it) {
            try {
                if (class_exists(\Picqer\Barcode\BarcodeGeneratorPNG::class)) {
                    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                    // Use CODE 128 for compact alphanumeric barcodes
                    $png = $generator->getBarcode((string)$it->id_barang, $generator::TYPE_CODE_128);
                    $it->barcode = 'data:image/png;base64,' . base64_encode($png);
                } else {
                    // If Picqer not installed, set null and log a hint
                    \Log::warning('Picqer barcode generator not installed. Run: composer require picqer/php-barcode-generator');
                    $it->barcode = null;
                }
            } catch (\Throwable $e) {
                \Log::error('Barcode generation failed', ['id' => $it->id_barang, 'error' => $e->getMessage()]);
                $it->barcode = null;
            }
        }

        // Desired cell size in mm: 4cm x 1.9cm => 40mm x 19mm
        // Fixed grid: 5 columns x 8 rows as requested
        $cols = 5;
        $rows = 8;
        $total = $cols * $rows;

        $offset = ($startY - 1) * $cols + ($startX - 1);

        // Prepare cells
        $cells = array_fill(0, $total, null);

        $index = $offset;
        foreach ($items as $it) {
            if ($index >= $total) break;
            $cells[$index] = $it;
            $index++;
        }

        $grid = [];
        for ($r = 0; $r < $rows; $r++) {
            $row = [];
            for ($c = 0; $c < $cols; $c++) {
                $row[] = $cells[$r * $cols + $c] ?? null;
            }
            $grid[] = $row;
        }

        // Use standard A4 portrait for label printing
        $pdf = Pdf::loadView('barang.labels_pdf', [
            'grid' => $grid,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('labels.pdf');
    }
}
