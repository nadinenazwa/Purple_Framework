<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$db = $app->make('db');
$schema = $db->getSchemaBuilder();
$table = $schema->hasTable('pesanan') ? 'pesanan' : ($schema->hasTable('penjualans') ? 'penjualans' : null);
if (! $table) {
    echo "No orders table found\n";
    exit(0);
}
// detect an id-like column for ordering
$cols = $schema->getColumnListing($table);
$idCandidates = ['id','id_pesanan','idpesanan','id_penjualan','idpenjualan','id_order','order_id','created_at','timestamp'];
$orderCol = null;
foreach ($idCandidates as $c) { if (in_array($c, $cols)) { $orderCol = $c; break; } }
if ($orderCol) {
    $row = $db->table($table)->orderByDesc($orderCol)->first();
} else {
    $row = $db->table($table)->first();
}

    // compute Carbon parsing result using the same robust logic as controller
    use Carbon\Carbon;
    $time = null;
    foreach (['created_at','timestamp','waktu'] as $c) { if (isset($row->{$c})) { $time = $row->{$c}; break; } }
    $parsed = null;
    if ($time) {
        try {
            if (is_numeric($time)) {
                if ((int)$time > 1000000000000) {
                    $dt = Carbon::createFromTimestampMs((int)$time);
                } else {
                    $dt = Carbon::createFromTimestamp((int)$time);
                }
                $parsed = $dt->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
            } else {
                if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', (string)$time)) {
                    $dt = Carbon::createFromFormat('Y-m-d H:i:s', (string)$time, 'UTC');
                    $parsed = $dt->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
                } else {
                    $dt = Carbon::parse((string)$time);
                    $parsed = $dt->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
                }
            }
        } catch (\Throwable $e) {
            $parsed = (string) $time;
        }
    }
    echo "\n--- Parsed result (controller): \n" . json_encode(['raw_time'=>$time, 'parsed'=>$parsed, 'php_default_tz'=>date_default_timezone_get(), 'app_timezone'=>config('app.timezone')], JSON_PRETTY_PRINT) . "\n";
echo json_encode(['table' => $table, 'row' => $row], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
