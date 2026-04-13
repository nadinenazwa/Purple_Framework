<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
$db = $app->make('db');
$schema = $db->getSchemaBuilder();
$tables = [];
if ($schema->hasTable('pesanan')) $tables[] = 'pesanan';
if ($schema->hasTable('penjualans')) $tables[] = 'penjualans';
if (empty($tables)) { echo "No orders tables found\n"; exit(0); }

$colsToCheck = ['created_at','timestamp','waktu'];
$summary = [];
foreach ($tables as $table) {
    $cols = $schema->getColumnListing($table);
    $found = array_values(array_intersect($colsToCheck, $cols));
    if (empty($found)) continue;
    $rows = $db->table($table)->get();
    $count = 0; $changed = 0; $samples = [];
    foreach ($rows as $r) {
        $count++;
        foreach ($found as $c) {
            if (empty($r->{$c})) continue;
            $raw = (string)$r->{$c};
            // only attempt naive Y-m-d H:i:s values
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $raw)) {
                // parse as UTC and convert to app timezone
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $raw, 'UTC');
                $converted = $dt->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
                if ($converted !== $raw) {
                    $changed++;
                    if (count($samples) < 5) $samples[] = ['table'=>$table,'col'=>$c,'id'=>$r->id ?? ($r->idpesanan ?? ($r->id_penjualan ?? null)),'raw'=>$raw,'converted'=>$converted];
                }
            }
        }
    }
    $summary[$table] = ['rows'=>$count,'will_change'=>$changed,'samples'=>$samples];
}

echo json_encode($summary, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) . "\n";
