<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class WilayahController extends Controller
{
    protected function loadData(string $name): array
    {
        $path = storage_path('app/wilayah/' . $name . '.json');
        if (!file_exists($path)) {
            // try to build from upstream CSVs if missing
            $this->buildFromCsvIfMissing($name);
        }
        if (!file_exists($path)) {
            return [];
        }
        $json = file_get_contents($path);
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    protected function buildFromCsvIfMissing(string $name)
    {
        $csvUrls = [
            'provinces' => 'https://raw.githubusercontent.com/guzfirdaus/Wilayah-Administrasi-Indonesia/master/csv/provinces.csv',
            'regencies' => 'https://raw.githubusercontent.com/guzfirdaus/Wilayah-Administrasi-Indonesia/master/csv/regencies.csv',
            'districts' => 'https://raw.githubusercontent.com/guzfirdaus/Wilayah-Administrasi-Indonesia/master/csv/districts.csv',
            'villages'  => 'https://raw.githubusercontent.com/guzfirdaus/Wilayah-Administrasi-Indonesia/master/csv/villages.csv',
        ];

        if (!isset($csvUrls[$name])) return;

        $dir = storage_path('app/wilayah');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $csvPath = $dir . '/' . $name . '.csv';
        $jsonPath = $dir . '/' . $name . '.json';

        // If json already exists, nothing to do
        if (file_exists($jsonPath)) return;

        try {
            $resp = Http::timeout(30)->get($csvUrls[$name]);
            if (!$resp->ok()) return;
            $content = $resp->body();
            // save CSV to disk (so fgetcsv can handle quoted newlines)
            file_put_contents($csvPath, $content);

            $handle = fopen($csvPath, 'r');
            if (!$handle) return;

            $rows = [];
            // read header
            $header = null;
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                if ($header === null) {
                    $header = $data;
                    continue;
                }
                // some rows might be malformed/empty
                if (count($data) < 2) continue;
                // normalize columns
                $row = [];
                foreach ($header as $i => $col) {
                    $colKey = trim($col);
                    $row[$colKey] = isset($data[$i]) ? $data[$i] : null;
                }
                $rows[] = $row;
            }
            fclose($handle);

            // Convert to expected JSON shape based on file name
            $out = [];
            if ($name === 'provinces') {
                foreach ($rows as $r) {
                    $out[] = ['id' => (string)($r['id'] ?? ''), 'name' => ($r['name'] ?? '')];
                }
            } elseif ($name === 'regencies') {
                foreach ($rows as $r) {
                    $out[] = ['id' => (string)($r['id'] ?? ''), 'province_id' => (string)($r['province_id'] ?? ''), 'name' => ($r['name'] ?? '')];
                }
            } elseif ($name === 'districts') {
                foreach ($rows as $r) {
                    $out[] = ['id' => (string)($r['id'] ?? ''), 'regency_id' => (string)($r['regency_id'] ?? ''), 'name' => ($r['name'] ?? '')];
                }
            } elseif ($name === 'villages') {
                foreach ($rows as $r) {
                    $out[] = ['id' => (string)($r['id'] ?? ''), 'district_id' => (string)($r['district_id'] ?? ''), 'name' => ($r['name'] ?? '')];
                }
            }

            // write JSON
            file_put_contents($jsonPath, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            // optionally remove CSV to save space
            @unlink($csvPath);
        } catch (\Exception $e) {
            // ignore - leave missing
            return;
        }
    }

    public function index()
    {
        return view('wilayah');
    }

    public function provinces()
    {
        $provinces = $this->loadData('provinces');
        return response()->json($provinces);
    }

    public function regencies($provinceId)
    {
        $regencies = $this->loadData('regencies');
        $filtered = array_values(array_filter($regencies, function ($r) use ($provinceId) {
            return (string)$r['province_id'] === (string)$provinceId;
        }));
        return response()->json($filtered);
    }

    public function districts($regencyId)
    {
        $districts = $this->loadData('districts');
        $filtered = array_values(array_filter($districts, function ($d) use ($regencyId) {
            return (string)$d['regency_id'] === (string)$regencyId;
        }));
        return response()->json($filtered);
    }

    public function villages($districtId)
    {
        $villages = $this->loadData('villages');
        $filtered = array_values(array_filter($villages, function ($v) use ($districtId) {
            return (string)$v['district_id'] === (string)$districtId;
        }));
        return response()->json($filtered);
    }
}
