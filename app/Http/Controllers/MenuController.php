<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class MenuController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // table may use different column names (idvendor, idmenu, created_at may be absent)
        $table = (new Menu)->getTable();
        $orderCandidates = ['created_at', 'idmenu', 'id'];
        $orderBy = null;
        foreach ($orderCandidates as $col) {
            if (Schema::hasColumn($table, $col)) {
                $orderBy = $col;
                break;
            }
        }

        $query = Menu::where('idvendor', $user->id);
        if ($orderBy) {
            $query->orderBy($orderBy, 'desc');
        }
        $menus = $query->get();
        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        return view('menus.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);
        // Map incoming fields to existing DB columns
        $create = [
            'nama_menu' => $data['name'],
            'harga' => $data['price'],
            'idvendor' => Auth::id(),
            'path_gambar' => null,
        ];
        Menu::create($create);
        return redirect()->route('vendor.menus.index')->with('success', 'Menu ditambahkan');
    }

    public function edit(Menu $menu)
    {
        $this->authorizeOwner($menu);
        return view('menus.edit', compact('menu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $this->authorizeOwner($menu);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);
        $update = [
            'nama_menu' => $data['name'],
            'harga' => $data['price'],
        ];
        $menu->update($update);
        return redirect()->route('vendor.menus.index')->with('success', 'Menu diperbarui');
    }

    public function destroy(Menu $menu)
    {
        $this->authorizeOwner($menu);
        $menu->delete();
        return redirect()->route('vendor.menus.index')->with('success', 'Menu dihapus');
    }

    protected function authorizeOwner(Menu $menu)
    {
        $ownerId = $menu->idvendor ?? $menu->user_id ?? null;
        if ($ownerId !== Auth::id()) {
            abort(403);
        }
    }
}
