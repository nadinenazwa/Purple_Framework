@extends('layouts.master')

@section('content')
<div class="container py-4">
  <h3 class="mb-4">Semua Pesanan Kantin</h3>
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm mb-0" id="ordersTable">
          <thead class="table-light">
            <tr>
              <th>Waktu &amp; ID TRX</th>
              <th>Guest</th>
              <th>Detail Pesanan</th>
              <th class="text-end">Total Nominal</th>
              <th>Status Pembayaran</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($orders as $o)
              <tr data-id="{{ $o['id'] }}">
                <td style="min-width:200px">
                  <div>{{ $o['time'] }}</div>
                  <div style="font-size:12px;color:#666">{{ $o['order_id'] ?? '-' }}</div>
                  @if(!empty($o['qr']))
                    <div style="margin-top:6px">
                      <img src="{{ $o['qr'] }}" alt="QR" style="height:48px;width:48px;display:block;border:1px solid #eee;padding:4px;background:#fff" />
                    </div>
                  @endif
                </td>
                <td>{{ $o['guest'] }}</td>
                <td>
                  @if(count($o['details']) === 0)
                    -
                  @else
                    <ul class="mb-0">
                      @foreach($o['details'] as $d)
                        @php
                          $displayName = $d['name'] ?? '';
                          if (strpos((string)$displayName, 'Item') === 0) {
                            $displayName = trim($displayName) !== '' ? $displayName : ('Item ID: ' . ($d['id'] ?? '-'));
                            // if generic 'Item' without useful id, show a clearer fallback
                            if ($displayName === 'Item') {
                              $displayName = 'Item ID: ' . ($d['id'] ?? '-');
                            }
                          }
                        @endphp
                        <li>
                          {{ $displayName }}@if(!empty($d['vendor'])) - {{ $d['vendor'] }}@endif
                          (x{{ $d['jumlah'] ?? 1 }})
                          @if(!empty($d['subtotal'])) - Rp {{ number_format($d['subtotal'],0,',','.') }}@endif
                        </li>
                      @endforeach
                    </ul>
                  @endif
                </td>
                <td class="text-end">Rp {{ number_format($o['total'] ?? 0,0,',','.') }}</td>
                <td class="status-cell">
                  @php
                    $st = $o['status'] ?? 'Belum';
                    $isPaid = ($st === 'Lunas' || $st === '1' || $st === 1);
                  @endphp
                  @if($isPaid)
                    <span class="badge bg-success">Lunas</span>
                  @else
                    <span class="badge bg-warning text-dark">Pending</span>
                  @endif
                </td>
                <td class="text-end">
                  <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-secondary btn-sync">Sync Status</button>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection

@push('js-page')
<script>
(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  document.querySelectorAll('.btn-sync').forEach(btn=>{
    btn.addEventListener('click', function(){
      const tr = this.closest('tr');
      const id = tr.dataset.id;
      const statusCell = tr.querySelector('.status-cell');
      this.disabled = true;
      this.textContent = 'Syncing...';
      fetch('{{ url('api/pesanan/sync') }}/' + encodeURIComponent(id), {
        method: 'POST',
        // ensure same-origin cookies are sent (important when app runs in a subfolder)
        credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(async r => {
        const status = r.status;
        const text = await r.text();
        let data;
        try { data = JSON.parse(text); } catch (e) { data = { __raw: text }; }
        console.log('Sync response debug', { url: '{{ url('api/pesanan/sync') }}/' + encodeURIComponent(id), status, data });
        return data;
      }).then(data=>{
        if (!data.success) {
          alert('Sync gagal: ' + (data.message || 'Unknown'));
        } else {
          const st = data.status;
          const mid = data.midtrans || {};
          const rawTx = mid.transaction_status || mid.status_code || null;
          // Normalize midtrans status display: avoid showing raw numeric 404
          let tx = null;
          if (rawTx === '404' || rawTx === 404) tx = 'Transaksi tidak ditemukan';
          else if (rawTx) tx = rawTx;
          const isPaid = (st === 'Lunas' || st === '1' || st === 1);
          let badgeHtml = isPaid ? '<span class="badge bg-success">Lunas</span>' : '<span class="badge bg-warning text-dark">Pending</span>';
          // show a small line with Midtrans transaction status when available (friendly text)
          if (tx) badgeHtml += '<div style="font-size:12px;color:#666;margin-top:4px">' + tx + '</div>';
          // also show the raw status value for clarity when it's numeric
          if (!tx && st !== undefined) badgeHtml += '<div style="font-size:12px;color:#666;margin-top:4px">Status: ' + String(st) + '</div>';
          statusCell.innerHTML = badgeHtml;
          // brief visual feedback on the button
          this.textContent = 'Synced';
          setTimeout(()=>{ this.textContent = 'Sync Status'; }, 1500);
        }
      }).catch(err=>{ console.error(err); alert('Gagal saat sinkronisasi'); })
      .finally(()=>{ this.disabled = false; this.textContent = 'Sync Status'; });
    });
  });
  // Mark Lunas and Link Midtrans UI removed — sync is Midtrans-driven only.
})();
</script>
@endpush
