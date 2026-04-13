<?php
// Usage: php scripts/fetch_wilayah.php
// This script clones the Wilayah-Administrasi-Indonesia repo, searches for JSON files,
// and produces tmp_provinces.json, tmp_regencies.json, tmp_districts.json at project root.

$repo = 'https://github.com/guzfirdaus/Wilayah-Administrasi-Indonesia.git';
$dest = __DIR__ . '/wilayah_source';

echo "Fetching repo $repo\n";
if (is_dir($dest)) {
    echo "Removing existing $dest\n";
    exec('rm -rf ' . escapeshellarg($dest));
}

$cmd = "git clone --depth 1 $repo " . escapeshellarg($dest) . " 2>&1";
echo "Running: $cmd\n";
exec($cmd, $out, $rc);
if ($rc !== 0) {
    echo "git clone failed. Make sure git is installed. Output:\n" . implode("\n", $out) . "\n";
    exit(1);
}

function findJsonFiles($dir) {
    $files = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $f) {
        if ($f->isFile() && strtolower($f->getExtension()) === 'json') {
            $files[] = $f->getPathname();
        }
    }
    return $files;
}

$jsonFiles = findJsonFiles($dest);
echo "Found " . count($jsonFiles) . " JSON files.\n";

$provinces = [];
$regencies = [];
$districts = [];

foreach ($jsonFiles as $jf) {
    $content = @file_get_contents($jf);
    if ($content === false) continue;
    $data = json_decode($content, true);
    if (!is_array($data)) continue;

    // try to detect type by keys in first element
    $first = reset($data);
    if (!is_array($first)) continue;

    $keys = array_keys($first);
    $k = implode(',', $keys);

    // provinces: id,name (no province_id)
    if (in_array('province_id', $keys) === false && in_array('regency_id', $keys) === false && isset($first['id']) && isset($first['name'])) {
        // assume provinces list
        foreach ($data as $row) {
            if (isset($row['id']) && isset($row['name'])) $provinces[] = ['id' => (string)$row['id'], 'name' => $row['name']];
        }
        continue;
    }

    // regencies: have province_id
    if (in_array('province_id', $keys) && isset($first['id']) && isset($first['name'])) {
        foreach ($data as $row) {
            if (isset($row['id']) && isset($row['name']) && isset($row['province_id'])) $regencies[] = ['id' => (string)$row['id'], 'province_id' => (string)$row['province_id'], 'name' => $row['name']];
        }
        continue;
    }

    // districts: have regency_id or kecamatan
    if (in_array('regency_id', $keys) && isset($first['id']) && isset($first['name'])) {
        foreach ($data as $row) {
            if (isset($row['id']) && isset($row['name']) && isset($row['regency_id'])) $districts[] = ['id' => (string)$row['id'], 'regency_id' => (string)$row['regency_id'], 'name' => $row['name']];
        }
        continue;
    }
}

// deduplicate by id
$unique = function($arr){
    $map = [];
    foreach ($arr as $r) $map[$r['id']] = $r;
    return array_values($map);
};

$provinces = $unique($provinces);
$regencies = $unique($regencies);
$districts = $unique($districts);

file_put_contents(__DIR__ . '/../tmp_provinces.json', json_encode(array_values($provinces), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
file_put_contents(__DIR__ . '/../tmp_regencies.json', json_encode(array_values($regencies), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
file_put_contents(__DIR__ . '/../tmp_districts.json', json_encode(array_values($districts), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

echo "Wrote tmp_provinces.json (" . count($provinces) . "), tmp_regencies.json (" . count($regencies) . "), tmp_districts.json (" . count($districts) . ")\n";
echo "Done.\n";
