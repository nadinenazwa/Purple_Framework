<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MasterDataController extends Controller
{
    // Master Data Vendor: form + list
    public function vendors()
    {
        // Prefer an explicit vendor table if present (legacy schema uses `vendor` with idvendor/nama_vendor)
        if (Schema::hasTable('vendor') || Schema::hasTable('vendors')) {
            $vendorTable = Schema::hasTable('vendor') ? 'vendor' : 'vendors';
            // normalize id and name alias for the view
            if ($vendorTable === 'vendor') {
                $vendors = DB::table('vendor')->select('idvendor as id', 'nama_vendor as name')->get();
            } else {
                $vendors = DB::table('vendors')->select('id', 'name')->get();
            }
        } else {
            // fallback to users table and try to infer vendors from menus/menu relations
            if (Schema::hasTable('menus')) {
                $vendors = DB::table('users')
                    ->join('menus', 'users.id', '=', 'menus.user_id')
                    ->select('users.id', 'users.name', 'users.email')
                    ->distinct()
                    ->get();
            } elseif (Schema::hasTable('menu')) {
                $vendors = DB::table('users')
                    ->join('menu', 'users.id', '=', 'menu.idvendor')
                    ->select('users.id', 'users.name', 'users.email')
                    ->distinct()
                    ->get();
            } else {
                $vendors = DB::table('users')->select('id','name','email')->get();
            }
        }

        return view('master.vendors', ['vendors' => $vendors]);
    }

    // store vendor (creates a user record as vendor)
    public function storeVendor(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);
        // If a vendor table exists, insert there. Support legacy `vendor` (idvendor, nama_vendor)
        if (Schema::hasTable('vendor') || Schema::hasTable('vendors')) {
            if (Schema::hasTable('vendor')) {
                DB::table('vendor')->insert([
                    'nama_vendor' => $data['name'],
                ]);
            } else {
                DB::table('vendors')->insert([
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                ]);
            }
            return redirect()->route('master.vendors')->with('success', 'Vendor ditambahkan');
        }

        // fallback: create a user record as vendor
        $email = $data['email'] ?? ('vendor+' . time() . '@example.test');
        $password = Str::random(10);

        $id = DB::table('users')->insertGetId([
            'name' => $data['name'],
            'email' => $email,
            'password' => Hash::make($password),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.vendors')->with('success', 'Vendor ditambahkan');
    }

    // Master Data Menu: form + list (show vendor name)
    public function menus()
    {
        // collect vendors for select (prefer explicit vendor table)
        if (Schema::hasTable('vendor')) {
            $vendors = DB::table('vendor')->select('idvendor as id', 'nama_vendor as name')->get();
        } elseif (Schema::hasTable('vendors')) {
            $vendors = DB::table('vendors')->select('id','name')->get();
        } else {
            $vendors = DB::table('users')->select('id','name')->get();
        }

        // list menus from available tables
        $menus = [];
        if (Schema::hasTable('menus')) {
            // modern menus table references users
            $menus = DB::table('menus')
                ->leftJoin('users', 'menus.user_id', '=', 'users.id')
                ->select('menus.*', 'users.name as vendor_name')
                ->orderBy('menus.id', 'desc')
                ->get();
        } elseif (Schema::hasTable('menu')) {
            // legacy `menu` table: vendor may be in `vendor` table or users
            if (Schema::hasTable('vendor')) {
                $menus = DB::table('menu')
                    ->leftJoin('vendor', 'menu.idvendor', '=', 'vendor.idvendor')
                    ->select('menu.*', 'vendor.nama_vendor as vendor_name')
                    ->orderBy('menu.idmenu', 'desc')
                    ->get();
            } else {
                $menus = DB::table('menu')
                    ->leftJoin('users', 'menu.idvendor', '=', 'users.id')
                    ->select('menu.*', 'users.name as vendor_name')
                    ->orderBy('menu.idmenu', 'desc')
                    ->get();
            }
        }

        return view('master.menus', ['vendors' => $vendors, 'menus' => $menus]);
    }

    public function editMenu(Request $request, $id)
    {
        $vendorTable = Schema::hasTable('vendor') ? 'vendor' : null;
        $menusTable = Schema::hasTable('menus') ? 'menus' : (Schema::hasTable('menu') ? 'menu' : null);

        if (!$menusTable) {
            return redirect()->route('master.menus')->with('error', 'Tabel menu tidak ditemukan');
        }

        // Try to load record using common id column names, but only on columns that exist
        $idCandidates = ['id','idmenu','menu_id','id_menu'];
        $qBuilder = DB::table($menusTable);
        $foundKey = false;
        foreach ($idCandidates as $col) {
            if (Schema::hasColumn($menusTable, $col)) {
                $qBuilder->where($col, $id);
                $foundKey = true;
                break;
            }
        }

        if (!$foundKey) {
            return redirect()->route('master.menus')->with('error', 'Menu tidak ditemukan (kolom kunci tidak tersedia)');
        }

        $record = $qBuilder->first();

        if (!$record) {
            return redirect()->route('master.menus')->with('error', 'Menu tidak ditemukan');
        }

        // Normalize record into an object for the view
        $menu = (object) [
            'id' => $record->id ?? $record->idmenu ?? $record->menu_id ?? $record->id_menu,
            'nama' => $record->nama ?? $record->nama_menu ?? $record->name ?? null,
            'harga' => $record->harga ?? $record->price ?? $record->harga_menu ?? null,
            'deskripsi' => $record->deskripsi ?? $record->description ?? null,
            'gambar' => $record->gambar ?? $record->image ?? null,
            'vendor_id' => $record->idvendor ?? $record->vendor_id ?? $record->user_id ?? null,
        ];

        // Load vendors for select (normalize to id/name)
        $vendors = [];
        if ($vendorTable) {
            $vendors = DB::table('vendor')->select('idvendor as id', 'nama_vendor as name')->get();
        } elseif (Schema::hasTable('users')) {
            $vendors = DB::table('users')->select('id as id', 'name as name')->get();
        }

        return view('master.menus_edit', compact('menu', 'vendors'));
    }

    public function updateMenu(Request $request, $id)
    {
        $menusTable = Schema::hasTable('menus') ? 'menus' : (Schema::hasTable('menu') ? 'menu' : null);
        if (!$menusTable) {
            return redirect()->route('master.menus')->with('error', 'Tabel menu tidak ditemukan');
        }

        $rules = [
            'nama' => 'required|string|max:255',
            'harga' => 'nullable|numeric',
            'deskripsi' => 'nullable|string',
            'vendor_id' => 'nullable',
            'gambar' => 'nullable|image|max:2048',
        ];
        $data = $request->validate($rules);

        // Locate record using available id-like column
        $idCandidates = ['id','idmenu','menu_id','id_menu'];
        $recordQuery = DB::table($menusTable);
        $foundKey = false;
        foreach ($idCandidates as $col) {
            if (Schema::hasColumn($menusTable, $col)) {
                $recordQuery->where($col, $id);
                $foundKey = true;
                break;
            }
        }
        if (!$foundKey) {
            return redirect()->route('master.menus')->with('error', 'Menu tidak ditemukan (kolom kunci tidak tersedia)');
        }

        $record = $recordQuery->first();
        if (!$record) {
            return redirect()->route('master.menus')->with('error', 'Menu tidak ditemukan');
        }

        $update = [];
        // Build update map based on actual columns present in the table
        // Modern `menus` table mapping
        if (Schema::hasTable('menus') && $menusTable === 'menus') {
            if (Schema::hasColumn('menus', 'name')) $update['name'] = $data['nama'];
            if (Schema::hasColumn('menus', 'price')) $update['price'] = $data['harga'];
            if (Schema::hasColumn('menus', 'deskripsi')) $update['deskripsi'] = $data['deskripsi'] ?? null;
            if (isset($data['vendor_id']) && Schema::hasColumn('menus', 'user_id')) $update['user_id'] = $data['vendor_id'];
        } else {
            // Legacy `menu` table mapping - choose columns that exist
            if (Schema::hasColumn($menusTable, 'nama_menu')) {
                $update['nama_menu'] = $data['nama'];
            } elseif (Schema::hasColumn($menusTable, 'nama')) {
                $update['nama'] = $data['nama'];
            }

            if (Schema::hasColumn($menusTable, 'harga')) {
                $update['harga'] = $data['harga'];
            } elseif (Schema::hasColumn($menusTable, 'harga_menu')) {
                $update['harga_menu'] = $data['harga'];
            } elseif (Schema::hasColumn($menusTable, 'price')) {
                $update['price'] = $data['harga'];
            }

            if (Schema::hasColumn($menusTable, 'deskripsi')) {
                $update['deskripsi'] = $data['deskripsi'] ?? null;
            } elseif (Schema::hasColumn($menusTable, 'description')) {
                $update['description'] = $data['deskripsi'] ?? null;
            }

            if (isset($data['vendor_id'])) {
                if (Schema::hasColumn($menusTable, 'idvendor')) {
                    $update['idvendor'] = $data['vendor_id'];
                } elseif (Schema::hasColumn($menusTable, 'vendor_id')) {
                    $update['vendor_id'] = $data['vendor_id'];
                } elseif (Schema::hasColumn($menusTable, 'user_id')) {
                    $update['user_id'] = $data['vendor_id'];
                }
            }
        }

        // Handle image upload and map to an existing image column
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $path = $file->store('menus', 'public');
            if (Schema::hasColumn($menusTable, 'image_path')) {
                $update['image_path'] = $path;
            } elseif (Schema::hasColumn($menusTable, 'path_gambar')) {
                $update['path_gambar'] = $path;
            } elseif (Schema::hasColumn($menusTable, 'gambar')) {
                $update['gambar'] = $path;
            } elseif (Schema::hasColumn($menusTable, 'image')) {
                $update['image'] = $path;
            }
        }

        $recordQuery->update($update);

        return redirect()->route('master.menus')->with('success', 'Menu diperbarui');
    }

    // Delete menu across supported schemas
    public function destroyMenu(Request $request, $id)
    {
        $menusTable = Schema::hasTable('menus') ? 'menus' : (Schema::hasTable('menu') ? 'menu' : null);
        if (!$menusTable) {
            return redirect()->route('master.menus')->with('error', 'Tabel menu tidak ditemukan');
        }

        $idCandidates = ['id','idmenu','menu_id','id_menu'];
        $recordQuery = DB::table($menusTable);
        $foundKey = false;
        foreach ($idCandidates as $col) {
            if (Schema::hasColumn($menusTable, $col)) {
                $recordQuery->where($col, $id);
                $foundKey = true;
                break;
            }
        }
        if (!$foundKey) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Menu tidak ditemukan'], 404);
            }
            return redirect()->route('master.menus')->with('error', 'Menu tidak ditemukan (kolom kunci tidak tersedia)');
        }

        $record = $recordQuery->first();
        if (!$record) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Menu tidak ditemukan'], 404);
            }
            return redirect()->route('master.menus')->with('error', 'Menu tidak ditemukan');
        }

        $recordQuery->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Menu dihapus']);
        }

        return redirect()->route('master.menus')->with('success', 'Menu dihapus');
    }

    // AJAX endpoint: return menus JSON, optionally filtered by vendor_id
    public function menusAjax(Request $request)
    {
        $vendorId = $request->query('vendor_id');
        $list = [];
        if (Schema::hasTable('menus')) {
            $q = DB::table('menus')->leftJoin('users', 'menus.user_id', '=', 'users.id')
                ->select('menus.*', 'users.name as vendor_name');
            if ($vendorId) $q->where('menus.user_id', $vendorId);
            $list = $q->orderBy('menus.id', 'desc')->get();
        } elseif (Schema::hasTable('menu')) {
            // legacy menu table: prefer vendor table if present
            if (Schema::hasTable('vendor')) {
                $q = DB::table('menu')->leftJoin('vendor', 'menu.idvendor', '=', 'vendor.idvendor')
                    ->select('menu.*', 'vendor.nama_vendor as vendor_name');
                if ($vendorId) $q->where('menu.idvendor', $vendorId);
                $list = $q->orderBy('menu.idmenu', 'desc')->get();
            } else {
                $q = DB::table('menu')->leftJoin('users', 'menu.idvendor', '=', 'users.id')
                    ->select('menu.*', 'users.name as vendor_name');
                if ($vendorId) $q->where('menu.idvendor', $vendorId);
                $list = $q->orderBy('menu.idmenu', 'desc')->get();
            }
        }

        return response()->json($list);
    }

    // store menu (supports menus or legacy menu table)
    public function storeMenu(Request $request)
    {
        $data = $request->validate([
            'vendor_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'photo' => 'nullable|file|image|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('menu_images', 'public');
        }

        if (Schema::hasTable('menus')) {
            DB::table('menus')->insert([
                'user_id' => $data['vendor_id'],
                'name' => $data['name'],
                'price' => (int)$data['price'],
                'image_path' => $path,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // legacy `menu` table
            DB::table('menu')->insert([
                'idvendor' => $data['vendor_id'],
                'nama_menu' => $data['name'],
                'harga' => (int)$data['price'],
                'path_gambar' => $path,
            ]);
        }

        return redirect()->route('master.menus')->with('success', 'Menu ditambahkan');
    }
}
