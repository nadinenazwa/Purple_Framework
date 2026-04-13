<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('id', 'desc')->get();
        return view('customer.index', ['customers' => $customers]);
    }

    // Show form that captures photo and stores as BLOB
    public function createBlob()
    {
        $provinces = [];
        $regencies = [];
        $districts = [];
        try {
            // prefer tmp_provinces.json (more complete), fall back to provinces.json
            $provFile = base_path('tmp_provinces.json');
            if (! file_exists($provFile)) $provFile = base_path('provinces.json');
            $provinces = json_decode(file_get_contents($provFile), true) ?: [];
        } catch (\Exception $e) {}
        try {
            $regencies = json_decode(file_get_contents(base_path('tmp_regencies.json')), true) ?: [];
        } catch (\Exception $e) {}
        try {
            $districts = json_decode(file_get_contents(base_path('tmp_districts.json')), true) ?: [];
        } catch (\Exception $e) {}

        return view('customer.create_blob', compact('provinces','regencies','districts'));
    }

    public function storeBlob(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'photo_data' => 'required|string',
            'alamat' => 'nullable|string',
            'province_name' => 'nullable|string',
            'regency_name' => 'nullable|string',
            'district_name' => 'nullable|string',
            'kodepos' => 'nullable|string',
        ]);

        $b64 = $data['photo_data'];
        // data URL expected: data:image/png;base64,AAAA
        if (preg_match('/^data:(.*?);base64,(.+)$/', $b64, $m)) {
            $bin = base64_decode($m[2]);
        } else {
            // assume raw base64
            $bin = base64_decode($b64);
        }

        $nameVal = $data['name'] ?? null;
        $c = Customer::create([
            'name' => $nameVal,
            'nama' => $nameVal,
            'alamat' => $data['alamat'] ?? null,
            'province_name' => $data['province_name'] ?? null,
            'regency_name' => $data['regency_name'] ?? null,
            'district_name' => $data['district_name'] ?? null,
            'kodepos' => $data['kodepos'] ?? null,
        ]);
        $c->photo_blob = $bin;
        $c->save();

        return redirect()->route('customer.index')->with('success', 'Customer saved (blob).');
    }

    // Show form that captures photo and stores as file path
    public function createFile()
    {
        $provinces = [];
        $regencies = [];
        $districts = [];
        try {
            $provFile = base_path('tmp_provinces.json');
            if (! file_exists($provFile)) $provFile = base_path('provinces.json');
            $provinces = json_decode(file_get_contents($provFile), true) ?: [];
        } catch (\Exception $e) {}
        try {
            $regencies = json_decode(file_get_contents(base_path('tmp_regencies.json')), true) ?: [];
        } catch (\Exception $e) {}
        try {
            $districts = json_decode(file_get_contents(base_path('tmp_districts.json')), true) ?: [];
        } catch (\Exception $e) {}

        return view('customer.create_file', compact('provinces','regencies','districts'));
    }

    // Show combined form with address fields and camera modal (saves as BLOB)
    public function createFull()
    {
        return view('customer.create_full');
    }

    public function storeFile(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'photo' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'alamat' => 'nullable|string',
            'province_name' => 'nullable|string',
            'regency_name' => 'nullable|string',
            'district_name' => 'nullable|string',
            'kodepos' => 'nullable|string',
        ]);

        $path = $request->file('photo')->store('customers', 'public');
        $publicPath = 'storage/' . $path; // accessible via asset()

        $nameVal = $data['name'] ?? null;
        $c = Customer::create([
            'name' => $nameVal,
            'nama' => $nameVal,
            'photo_path' => $publicPath,
            'alamat' => $data['alamat'] ?? null,
            'province_name' => $data['province_name'] ?? null,
            'regency_name' => $data['regency_name'] ?? null,
            'district_name' => $data['district_name'] ?? null,
            'kodepos' => $data['kodepos'] ?? null,
        ]);

        return redirect()->route('customer.index')->with('success', 'Customer saved (file).');
    }
}
