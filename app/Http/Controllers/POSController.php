<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Barang;

class POSController extends Controller
{
    public function index()
    {
        // Load vendors for the POS page. Prefer an explicit `vendor` table when present.
        $vendors = [];
        if (Schema::hasTable('vendor')) {
            $vendors = DB::table('vendor')
                ->whereNotNull('nama_vendor')
                ->where('nama_vendor', '<>', '')
                ->select('idvendor as id', 'nama_vendor as name')
                ->orderBy('nama_vendor')
                ->get();
        } elseif (Schema::hasTable('menus')) {
            // modern menus table references users
            $vendors = DB::table('menus')
                ->join('users', 'users.id', '=', 'menus.user_id')
                ->whereNotNull('users.name')
                ->where('users.name', '<>', '')
                ->select('users.id', 'users.name')
                ->groupBy('users.id', 'users.name')
                ->orderBy('users.name')
                ->get();
        } elseif (Schema::hasTable('menu')) {
            // legacy `menu` table uses idvendor foreign key
            $vendors = DB::table('menu')
                ->join('users', 'users.id', '=', 'menu.idvendor')
                ->whereNotNull('users.name')
                ->where('users.name', '<>', '')
                ->select('users.id', 'users.name')
                ->groupBy('users.id', 'users.name')
                ->orderBy('users.name')
                ->get();
        }

        return view('pos', ['vendors' => $vendors]);
    }

    // Return barang by kode (id_barang)
    public function getBarang($kode)
    {
        $barang = Barang::where('id_barang', $kode)->first();
        if (! $barang) {
            return response()->json(['found' => false], 404);
        }
        return response()->json([
            'found' => true,
            'data' => [
                'id_barang' => $barang->id_barang,
                'nama' => $barang->nama,
                'harga' => (int) $barang->harga,
            ],
        ]);
    }

    // Return menus for a vendor (API)
    public function getMenusByVendor(Request $request)
    {
        $vendorId = $request->query('vendor_id');

        // Prefer modern `menus` table, but support legacy `menu` table.
        if (Schema::hasTable('menus')) {
            $q = DB::table('menus');
            if ($vendorId) {
                if (Schema::hasColumn('menus', 'user_id')) {
                    $q->where('menus.user_id', $vendorId);
                } elseif (Schema::hasColumn('menus', 'idvendor')) {
                    $q->where('menus.idvendor', $vendorId);
                }
            }
            $items = $q->get();
            return response()->json($items);
        }

        if (Schema::hasTable('menu')) {
            $q = DB::table('menu');
            if ($vendorId) $q->where('menu.idvendor', $vendorId);
            $items = $q->get();
            return response()->json($items);
        }

        return response()->json([]);
    }

    // Store penjualan and its details
    public function storePenjualan(Request $request)
    {
        $payload = $request->validate([
            'items' => 'required|array|min:1',
            'total' => 'required|numeric',
        ]);

        $items = $payload['items'];
        $total = $payload['total'];
        // Support both `pesanan`/`detail_pesanan` and legacy `penjualans`/`penjualan_detail` tables.
        $orderTable = Schema::hasTable('pesanan') ? 'pesanan' : 'penjualans';
        $detailTable = $orderTable === 'pesanan' ? 'detail_pesanan' : 'penjualan_detail';

        DB::beginTransaction();
        try {
            // generate guest name based on count in chosen table
            $guestName = $this->generateGuestNameForTable($orderTable);

            // insert order and get id (handle different table schemas)
            $orderData = [];
            // timestamp column detection
            if (Schema::hasColumn($orderTable, 'created_at')) {
                $orderData['created_at'] = now();
            } elseif (Schema::hasColumn($orderTable, 'timestamp')) {
                $orderData['timestamp'] = now();
            } elseif (Schema::hasColumn($orderTable, 'waktu')) {
                $orderData['waktu'] = now();
            }

            // total/amount column detection
            if (Schema::hasColumn($orderTable, 'total')) {
                $orderData['total'] = $total;
            } elseif (Schema::hasColumn($orderTable, 'grand_total')) {
                $orderData['grand_total'] = $total;
            } elseif (Schema::hasColumn($orderTable, 'amount')) {
                $orderData['amount'] = $total;
            }

            // name column
            if (Schema::hasColumn($orderTable, 'nama')) {
                $orderData['nama'] = $guestName;
            } elseif (Schema::hasColumn($orderTable, 'name')) {
                $orderData['name'] = $guestName;
            }

            // fallback: if nothing to insert yet, try conservative defaults
            if (empty($orderData)) {
                // try to insert minimal fields that are likely to exist
                $orderData = ['total' => $total, 'nama' => $guestName];
            }

            $id = DB::table($orderTable)->insertGetId($orderData);

            // insert details (map columns based on actual detail table schema)
            // detect order FK column in detail table
            $orderFkCandidates = ['id_pesanan','idpesanan','pesanan_id','id_penjualan','idpenjualan','penjualan_id','id_order','order_id'];
            $orderFk = null;
            foreach ($orderFkCandidates as $col) {
                if (Schema::hasColumn($detailTable, $col)) { $orderFk = $col; break; }
            }
            // detect menu/item FK column in detail table
            $menuFkCandidates = ['id_menu','menu_id','id_barang','idmenu','menuid','item_id'];
            $menuFk = null;
            foreach ($menuFkCandidates as $col) {
                if (Schema::hasColumn($detailTable, $col)) { $menuFk = $col; break; }
            }
            // detect jumlah, price and subtotal columns
            $jumlahCol = Schema::hasColumn($detailTable, 'jumlah') ? 'jumlah' : (Schema::hasColumn($detailTable, 'qty') ? 'qty' : null);
            $subtotalCol = Schema::hasColumn($detailTable, 'subtotal') ? 'subtotal' : (Schema::hasColumn($detailTable, 'total') ? 'total' : null);
            $priceCol = null;
            foreach (['harga','price','harga_menu','unit_price','price_item'] as $c) {
                if (Schema::hasColumn($detailTable, $c)) { $priceCol = $c; break; }
            }

            foreach ($items as $it) {
                $id_barang = $it['id_barang'] ?? $it['id'] ?? $it['idmenu'] ?? $it['menu_id'] ?? null;
                $jumlah = $it['jumlah'] ?? $it['qty'] ?? 1;
                $subtotal = $it['subtotal'] ?? (($it['price'] ?? $it['harga'] ?? 0) * $jumlah);

                $detailInsert = [];
                if ($orderFk) {
                    $detailInsert[$orderFk] = $id;
                } else {
                    // fallback: try common names
                    $detailInsert[$orderTable === 'pesanan' ? 'id_pesanan' : 'id_penjualan'] = $id;
                }

                if ($menuFk) {
                    $detailInsert[$menuFk] = $id_barang;
                } else {
                    $detailInsert[$orderTable === 'pesanan' ? 'id_menu' : 'id_barang'] = $id_barang;
                }

                if ($jumlahCol) $detailInsert[$jumlahCol] = $jumlah; else $detailInsert['jumlah'] = $jumlah;
                // include unit price if table requires it
                $unitPrice = $it['price'] ?? $it['harga'] ?? $it['unit_price'] ?? null;
                if (!$unitPrice && $subtotal && $jumlah) {
                    $unitPrice = $subtotal / $jumlah;
                }
                if ($priceCol) {
                    // ensure numeric
                    $detailInsert[$priceCol] = $unitPrice !== null ? (int)$unitPrice : 0;
                } elseif ($unitPrice !== null && !isset($detailInsert['harga']) && !isset($detailInsert['price'])) {
                    // best-effort: also set 'harga' column if exists but wasn't detected earlier
                    if (Schema::hasColumn($detailTable, 'harga')) $detailInsert['harga'] = (int)$unitPrice;
                }
                if ($subtotalCol) $detailInsert[$subtotalCol] = $subtotal; else $detailInsert['subtotal'] = $subtotal;

                DB::table($detailTable)->insert($detailInsert);
            }

            DB::commit();

            // After saving, ensure order_id (invoice) is stored on the order row so
            // future syncs and callbacks can find it. Use common order_id column names.
            $orderIdColCandidates = ['order_id','id_order','idorder'];
            $orderIdCol = null;
            foreach ($orderIdColCandidates as $c) {
                if (Schema::hasColumn($orderTable, $c)) { $orderIdCol = $c; break; }
            }
            // find an id column to locate this row
            $idColCandidates = ['id','id_pesanan','idpesanan','id_penjualan','idpenjualan','id_order'];
            $idCol = null;
            foreach ($idColCandidates as $c) { if (Schema::hasColumn($orderTable, $c)) { $idCol = $c; break; } }

            // generate an order id that Midtrans will reference. Use the same
            // pattern as getSnapToken ('order-{table}-{id}-{timestamp}').
            // Use Laravel timezone-aware timestamp so the embedded time matches app timezone.
            $generatedOrderId = 'order-' . $orderTable . '-' . $id . '-' . now()->timestamp;
            if ($orderIdCol && $idCol) {
                DB::table($orderTable)->where($idCol, $id)->update([$orderIdCol => $generatedOrderId]);
            }

            // After saving, request snap token from Midtrans using existing helper
            // getSnapToken expects an order row to have order_id (so we updated it above)
            $snapResp = $this->getSnapToken($id);
            $content = $snapResp instanceof \Illuminate\Http\JsonResponse ? $snapResp->getData(true) : json_decode($snapResp->getContent(), true);
            $snapToken = $content['snap_token'] ?? null;

            if (! $snapToken) {
                // Midtrans token generation failed — return clear error and include order id so it can be recovered/checked later
                $message = $content['error'] ?? ($content['message'] ?? 'Snap token tidak tersedia');
                $details = $content['details'] ?? $content['raw'] ?? null;
                return response()->json([
                    'success' => false,
                    'id' => $id,
                    'message' => $message,
                    'details' => $details,
                ], 500);
            }

            return response()->json(['success' => true, 'id' => $id, 'snap_token' => $snapToken]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Generate Guest_<7-digit> name for anonymous customers
    public function generateGuestName()
    {
        // keep backward-compatible behaviour
        return $this->generateGuestNameForTable(Schema::hasTable('pesanan') ? 'pesanan' : 'penjualans');
    }

    // Generate Guest name based on the number of rows in a table (next index)
    public function generateGuestNameForTable($table)
    {
        if ($table === 'pesanan' && Schema::hasTable('pesanan')) {
            $count = DB::table('pesanan')->count();
            $next = $count + 1;
            return 'Guest_' . str_pad($next, 7, '0', STR_PAD_LEFT);
        }
        // fallback to penjualans
        $last = DB::table('penjualans')->max('id_penjualan');
        $next = $last ? ($last + 1) : (DB::table('penjualans')->count() + 1);
        return 'Guest_' . str_pad($next, 7, '0', STR_PAD_LEFT);
    }

    // Return a Midtrans snap token for an order id (checks `pesanan` or `penjualans` table)
    public function getSnapToken($id)
    {
        // prefer `pesanan` table if exists, otherwise `penjualans`
        $table = Schema::hasTable('pesanan') ? 'pesanan' : (Schema::hasTable('penjualans') ? 'penjualans' : null);
        if (! $table) {
            return response()->json(['error' => 'No pesanan or penjualans table found'], 500);
        }

        // Find the order row using an id-like column that actually exists on the table
        $idCandidates = ['id','id_pesanan','idpesanan','pesanan_id','id_penjualan','idpenjualan','penjualan_id','id_order','order_id'];
        $row = null;
        foreach ($idCandidates as $col) {
            if (Schema::hasColumn($table, $col)) {
                $row = DB::table($table)->where($col, $id)->first();
                break;
            }
        }

        if (! $row) {
            return response()->json(['error' => 'Order not found or id column not available'], 404);
        }

        // detect total-like column
        $total = $row->total ?? $row->grand_total ?? $row->amount ?? null;
        if (! $total) {
            return response()->json(['error' => 'Order found but total is zero or missing'], 404);
        }

        // Prefer existing order_id if present (we stored it on creation); otherwise generate one.
        // Use Laravel timezone-aware timestamp to keep the embedded time consistent with app timezone.
        $orderId = $row->order_id ?? ('order-' . $table . '-' . $id . '-' . now()->timestamp);
        // If we generated a new order_id and the table supports the column, persist it
        if (!empty($orderId) && empty($row->order_id)) {
            $orderIdColCandidates = ['order_id','id_order','idorder'];
            foreach ($orderIdColCandidates as $c) {
                if (Schema::hasColumn($table, $c)) { DB::table($table)->where($col, $id)->update([$c => $orderId]); break; }
            }
        }
        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $total,
            ],
            'item_details' => [
                [
                    'id' => 'item-1',
                    'price' => (int) $total,
                    'quantity' => 1,
                    'name' => 'Pembayaran',
                ],
            ],
        ];

        $serverKey = config('services.midtrans.server_key');
        $isProduction = config('services.midtrans.is_production');
        $url = $isProduction ? 'https://app.midtrans.com/snap/v1/transactions' : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        try {
            // Log payload and request info for debugging (do not log server_key value)
            Log::debug('Midtrans request preparing', [
                'url' => $url,
                'isProduction' => $isProduction,
                'server_key_present' => !empty($serverKey),
                'payload' => $payload,
            ]);
            // Increase timeout and add a small retry to tolerate transient network issues
            $resp = Http::withBasicAuth($serverKey, '')
                ->accept('application/json')
                ->timeout(30)
                ->retry(2, 100) // 2 retries with 100ms backoff
                ->post($url, $payload);

            if ($resp->successful()) {
                $data = $resp->json();
                $token = $data['token'] ?? ($data['snap_token'] ?? null);
                return response()->json(['snap_token' => $token, 'raw' => $data]);
            }

            // Non-200 response from Midtrans
            return response()->json(['error' => 'Midtrans API error', 'status' => $resp->status(), 'details' => $resp->body()], $resp->status());
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Network/connectivity issues
            return response()->json(['error' => 'Connection error: ' . $e->getMessage()], 502);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Exception: ' . $e->getMessage()], 500);
        }
    }

    // Midtrans webhook/callback handler
    public function midtransCallback(Request $request)
    {
        $payload = $request->json()->all();

        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? ($payload['status_code'] ?? null);
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? ($payload['gross_amount'] ?? '');
        $receivedSignature = $payload['signature_key'] ?? $request->header('signature_key');

        $serverKey = config('services.midtrans.server_key');
        $expectedSignature = null;
        if ($orderId !== null) {
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        }

        if ($receivedSignature && $expectedSignature && !hash_equals($expectedSignature, $receivedSignature)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Treat 'settlement' as paid. Also accept 'capture' with fraud_status='accept'
        $isPaid = false;
        if (isset($payload['transaction_status']) && $payload['transaction_status'] === 'settlement') {
            $isPaid = true;
        }
        if (isset($payload['transaction_status']) && $payload['transaction_status'] === 'capture' && isset($payload['fraud_status']) && $payload['fraud_status'] === 'accept') {
            $isPaid = true;
        }

        if ($isPaid && $orderId) {
            // Update pesanan table by order_id
            if (Schema::hasTable('pesanan')) {
                $updatePayload = ['status_bayar' => (Schema::getColumnType('pesanan', 'status_bayar') ?? '')];
                // choose string 'Lunas' or numeric 1 based on column type
                try { $colTypePesanan = Schema::getColumnType('pesanan', 'status_bayar'); } catch (\Throwable $e) { $colTypePesanan = null; }
                $updatePayload['status_bayar'] = in_array($colTypePesanan, ['integer','bigint','tinyint','smallint','mediumint','boolean']) ? 1 : 'Lunas';
                if (Schema::hasColumn('pesanan', 'metode_bayar') && isset($payload['payment_type'])) {
                    $updatePayload['metode_bayar'] = $payload['payment_type'];
                }
                $updated = DB::table('pesanan')->where('order_id', $orderId)->update($updatePayload);
                if ($updated) { return response('OK', 200); }
            }
            // fallback: attempt to update penjualans if pesanan not found
            if (Schema::hasTable('penjualans')) {
                try { $colTypePenj = Schema::getColumnType('penjualans', 'status_bayar'); } catch (\Throwable $e) { $colTypePenj = null; }
                $updatePayload2 = ['status_bayar' => in_array($colTypePenj, ['integer','bigint','tinyint','smallint','mediumint','boolean']) ? 1 : 'Lunas'];
                if (Schema::hasColumn('penjualans', 'metode_bayar') && isset($payload['payment_type'])) {
                    $updatePayload2['metode_bayar'] = $payload['payment_type'];
                }
                $updated2 = DB::table('penjualans')->where('order_id', $orderId)->update($updatePayload2);
                if ($updated2) { return response('OK', 200); }
            }
            // nothing updated
            return response()->json(['warning' => 'Order not found to update'], 404);
        }

        return response('OK', 200);
    }

    // Show all kantin orders page
    public function allPesanan()
    {
        $table = Schema::hasTable('pesanan') ? 'pesanan' : (Schema::hasTable('penjualans') ? 'penjualans' : null);
        if (! $table) {
            // return empty view with message
            return view('pesanan.index', ['orders' => []]);
        }

        $orders = [];
        if ($table === 'pesanan') {
            // choose a safe order column depending on table schema
            $cols = Schema::getColumnListing('pesanan');
            $preferred = ['created_at', 'id', 'id_pesanan', 'order_id', 'timestamp'];
            $orderCol = null;
            foreach ($preferred as $c) { if (in_array($c, $cols)) { $orderCol = $c; break; } }
            if ($orderCol) {
                $rows = DB::table('pesanan')->orderBy($orderCol, 'desc')->get();
            } else {
                $rows = DB::table('pesanan')->get();
            }
        } else {
            // penjualans: prefer timestamp, fallback to id_penjualan or id
            $cols2 = Schema::getColumnListing('penjualans');
            $preferred2 = ['timestamp', 'id_penjualan', 'id', 'created_at'];
            $orderCol2 = null;
            foreach ($preferred2 as $c) { if (in_array($c, $cols2)) { $orderCol2 = $c; break; } }
            if ($orderCol2) {
                $rows = DB::table('penjualans')->orderBy($orderCol2, 'desc')->get();
            } else {
                $rows = DB::table('penjualans')->get();
            }
        }

        foreach ($rows as $r) {
            // resolve id and timestamps
            $id = null;
            $idCandidates = ['id','id_pesanan','idpesanan','id_penjualan','idpenjualan','id_order','order_id'];
            foreach ($idCandidates as $c) {
                if (isset($r->{$c})) { $id = $r->{$c}; break; }
            }
            $time = $r->created_at ?? ($r->timestamp ?? ($r->waktu ?? null));
            // Normalize time to app timezone and format for display.
            // Handle several storage formats: numeric timestamps (s or ms),
            // naive datetime strings stored in UTC, and timezone-aware strings.
            $timeStr = null;
            if ($time) {
                try {
                    if (is_numeric($time)) {
                        // millisecond vs second detection
                        if ((int)$time > 1000000000000) {
                            $dt = Carbon::createFromTimestampMs((int)$time);
                        } else {
                            $dt = Carbon::createFromTimestamp((int)$time);
                        }
                        $timeStr = $dt->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
                    } else {
                        // If DB stores naive datetimes (Y-m-d H:i:s) and they are already
                        // in the app timezone, prefer showing them as-is to avoid double-shifting.
                        // Only parse+convert when the value contains timezone info or other formats.
                        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', (string)$time)) {
                            // treat naive datetime as already in DB's timezone (display raw)
                            $timeStr = (string) $time;
                        } else {
                            // let Carbon parse timezone-aware strings or other formats
                            $dt = Carbon::parse((string)$time);
                            $timeStr = $dt->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
                        }
                    }
                } catch (\Throwable $e) {
                    // fallback to raw value
                    $timeStr = (string) $time;
                }
            }
            $order_id = $r->order_id ?? null;
            $guest = $r->nama ?? ($r->nama_pembeli ?? null) ?? 'Guest';
            $total = $r->total ?? 0;
            $status = $r->status_bayar ?? ($r->status ?? 'Belum');

            // detail rows
            $detailTable = $table === 'pesanan' ? 'detail_pesanan' : 'penjualan_detail';
            $details = [];
            if (Schema::hasTable($detailTable)) {
                // detect foreign key column in detail table to avoid missing column errors
                $fkCandidates = $table === 'pesanan'
                    ? ['id_pesanan','idpesanan','pesanan_id','id_order','order_id','id']
                    : ['id_penjualan','idpenjualan','penjualan_id','id_order','order_id','id'];
                $fkCol = null;
                foreach ($fkCandidates as $c) {
                    if (Schema::hasColumn($detailTable, $c)) { $fkCol = $c; break; }
                }

                if ($fkCol) {
                    $q = DB::table($detailTable)->where($fkCol, $id)->get();
                } else {
                    // fallback: try to match by any numeric column equal to id (best-effort)
                    $q = DB::table($detailTable)->where(function($q2) use ($id, $detailTable) {
                        $cols = Schema::getColumnListing($detailTable);
                        foreach ($cols as $col) {
                            if (in_array(Schema::getColumnType($detailTable, $col), ['integer','bigint'])) {
                                $q2->orWhere($col, $id);
                            }
                        }
                    })->get();
                }

                foreach ($q as $d) {
                    // attempt to get menu/barang name and vendor
                    $menuName = $d->nama ?? $d->nama_menu ?? $d->nama_barang ?? null;
                    $vendorName = null;
                    // if id_menu present, try menus or menu table
                    $mid = $d->idmenu ?? $d->id_menu ?? ($d->id_barang ?? $d->idbarang ?? null);
                    if ($mid) {
                        if (Schema::hasTable('menus')) {
                            $m = DB::table('menus')->where('id', $mid)->first();
                            if ($m) { $menuName = $m->name ?? $m->nama ?? $menuName; $vendorName = DB::table('users')->where('id', $m->user_id ?? $m->idvendor ?? null)->value('name'); }
                        }
                        if (! $menuName && Schema::hasTable('menu')) {
                            $m2 = DB::table('menu')->where('idmenu', $mid)->first();
                            if ($m2) { $menuName = $m2->nama_menu ?? $menuName; $vendorName = DB::table('users')->where('id', $m2->idvendor ?? null)->value('name'); }
                        }
                        // fallback to barang table
                        if (! $menuName && Schema::hasTable('barangs')) {
                            $b = DB::table('barangs')->where('id', $mid)->first();
                            if ($b) { $menuName = $b->nama ?? $b->nama_barang ?? $menuName; $vendorName = DB::table('users')->where('id', $b->user_id ?? null)->value('name'); }
                        }
                    }

                    $details[] = [
                        'id' => $mid,
                        'name' => $menuName ?? ('Item ' . ($mid ?? '')),
                        'vendor' => $vendorName,
                        'jumlah' => $d->jumlah ?? 1,
                        'subtotal' => $d->subtotal ?? null,
                    ];
                }
            }

            $orders[] = [
                'id' => $id,
                'time' => $timeStr,
                'order_id' => $order_id,
                'guest' => $guest,
                'details' => $details,
                'total' => $total,
                'status' => $status,
                'table' => $table,
            ];
        }

        return view('pesanan.index', ['orders' => $orders]);
    }

    // Sync a single order status with Midtrans and update DB (AJAX)
    public function syncStatus(Request $request, $id)
    {
        $table = Schema::hasTable('pesanan') ? 'pesanan' : (Schema::hasTable('penjualans') ? 'penjualans' : null);
        if (! $table) return response()->json(['success' => false, 'message' => 'Orders table not found'], 404);

        // Log incoming request for debugging
        Log::debug('syncStatus called', ['incoming_id' => $id, 'table' => $table, 'ip' => $request->ip()]);

        // find the order row — only add WHERE clauses for columns that actually exist
        $idCandidates = ['id','id_pesanan','idpesanan','id_penjualan','idpenjualan','id_order','order_id'];
        $query = DB::table($table);
        $added = false;
        $query->where(function($q) use ($idCandidates, $id, $table, &$added) {
            foreach ($idCandidates as $col) {
                if (Schema::hasColumn($table, $col)) {
                    if (! $added) {
                        $q->where($col, $id);
                        $added = true;
                    } else {
                        $q->orWhere($col, $id);
                    }
                }
            }
        });

        if (! $added) {
            return response()->json(['success' => false, 'message' => 'No suitable id column found on orders table'], 400);
        }

        $order = $query->first();

        if (! $order) return response()->json(['success' => false, 'message' => 'Order not found'], 404);

        $orderId = $order->order_id ?? null;
        Log::debug('Order lookup result', ['id' => $id, 'order_row' => $order, 'order_id' => $orderId]);
        if (! $orderId) {
            return response()->json(['success' => false, 'message' => 'No order_id available for this order'], 400);
        }

        $serverKey = config('services.midtrans.server_key');
        $isProd = config('services.midtrans.is_production');
        $url = ($isProd ? 'https://api.midtrans.com/v2/' : 'https://api.sandbox.midtrans.com/v2/') . urlencode($orderId) . '/status';

        try {
            $resp = Http::withBasicAuth($serverKey, '')->accept('application/json')->get($url);
            // Log Midtrans request/response for diagnostics (avoid logging server key)
            Log::debug('Midtrans status request', ['url' => $url, 'response_status' => $resp->status(), 'response_body_snippet' => substr($resp->body(), 0, 1000)]);
            if (! $resp->successful()) {
                return response()->json(['success' => false, 'message' => 'Midtrans API error', 'details' => $resp->body()], $resp->status());
            }
            $body = $resp->json();
            $txStatus = $body['transaction_status'] ?? $body['status_code'] ?? null;
            $paymentType = $body['payment_type'] ?? $body['payment_method'] ?? null;

            // Determine update value for status_bayar
            $newStatusValue = null;
            if ($txStatus === 'settlement' || $txStatus === 'capture') {
                // set to numeric 1 if column is any integer-like type, otherwise 'Lunas'
                $colToUpdate = Schema::hasColumn($table, 'status_bayar') ? 'status_bayar' : (Schema::hasColumn($table, 'status') ? 'status' : 'status_bayar');
                $colType = null;
                try { $colType = Schema::getColumnType($table, $colToUpdate); } catch (\Throwable $e) { $colType = null; }
                // treat tinyint/boolean/smallint/mediumint as integer-like
                $intLike = ['integer','bigint','tinyint','smallint','mediumint','boolean'];
                if (in_array($colType, $intLike)) $newStatusValue = 1;
                else $newStatusValue = 'Lunas';

                // update using only existing id-like columns to avoid unknown column SQL errors
                $updateQuery = DB::table($table);
                $addedWhere = false;
                $idCols = ['id','id_pesanan','idpesanan','id_penjualan','idpenjualan','id_order','order_id'];
                $updateQuery->where(function($q) use ($idCols, $id, $table, &$addedWhere) {
                    foreach ($idCols as $col) {
                        if (Schema::hasColumn($table, $col)) {
                            if (! $addedWhere) { $q->where($col, $id); $addedWhere = true; }
                            else { $q->orWhere($col, $id); }
                        }
                    }
                });
                if ($addedWhere) {
                    // prepare update payload and include metode_bayar if the column exists
                    $payload = [$colToUpdate => $newStatusValue];
                    if (Schema::hasColumn($table, 'metode_bayar') && $paymentType) {
                        $payload['metode_bayar'] = $paymentType;
                    }
                    // update the detected column name (either status_bayar or status) and metode_bayar when available
                    $updateQuery->update($payload);
                }

                return response()->json(['success' => true, 'status' => $newStatusValue, 'midtrans' => $body]);
            }

            return response()->json(['success' => true, 'status' => $order->status_bayar ?? 'Belum', 'midtrans' => $body]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    }
