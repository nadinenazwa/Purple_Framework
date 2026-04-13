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
$backupTable = 'timestamp_backup_log';
// create a small backup table if not exists
if (! $schema->hasTable($backupTable)) {
    $schema->create($backupTable, function($t) {
        $t->increments('id');
        $t->string('table');
        $t->string('pk_col')->nullable();
        $t->string('pk_val')->nullable();
        $t->string('col');
        $t->text('old_value')->nullable();
        $t->text('new_value')->nullable();
        $t->timestamp('created_at')->useCurrent();
    });
}

$report = [];
foreach ($tables as $table) {
    $cols = $schema->getColumnListing($table);
    $found = array_values(array_intersect($colsToCheck, $cols));
    if (empty($found)) { $report[$table] = ['rows'=>0,'updated'=>0]; continue; }

    // detect primary key-like column for WHERE updates and identification
    $idCandidates = ['id','id_pesanan','idpesanan','id_penjualan','idpenjualan','id_order','order_id'];
    $pk = null;
    foreach ($idCandidates as $c) { if (in_array($c,$cols)) { $pk = $c; break; } }
    // fetch rows
    $rows = $db->table($table)->get();
    $updated = 0; $processed = 0;
    foreach ($rows as $r) {
        $processed++;
        foreach ($found as $c) {
            $raw = isset($r->{$c}) ? (string)$r->{$c} : null;
            if (! $raw) continue;
            // only convert naive Y-m-d H:i:s strings
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $raw)) {
                try {
                    $dt = Carbon::createFromFormat('Y-m-d H:i:s', $raw, 'UTC');
                    $converted = $dt->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
                    if ($converted !== $raw) {
                        // backup
                        $db->table($backupTable)->insert(['table'=>$table,'pk_col'=>$pk,'pk_val'=>$pk ? (string)($r->{$pk} ?? '') : null,'col'=>$c,'old_value'=>$raw,'new_value'=>$converted]);
                        // update row
                        $updateQuery = $db->table($table);
                        if ($pk) $updateQuery->where($pk, $r->{$pk});
                        else {
                            // best-effort: try to match by all numeric columns
                            $updateQuery->where(function($q) use ($r, $table, $schema) {
                                $cols2 = $schema->getColumnListing($table);
                                foreach ($cols2 as $col) {
                                    try { $type = $schema->getColumnType($table, $col); } catch (\Throwable $e) { $type = null; }
                                    if (in_array($type, ['integer','bigint','tinyint','smallint','mediumint'])) {
                                        $q->orWhere($col, $r->{$col});
                                    }
                                }
                            });
                        }
                        $updateQuery->update([$c => $converted]);
                        $updated++;
                    }
                } catch (\Throwable $e) {
                    // ignore parse issues
                }
            }
        }
    }
    $report[$table] = ['rows'=>$processed,'updated'=>$updated];
}

echo "Conversion complete:\n" . json_encode($report, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) . "\n";
