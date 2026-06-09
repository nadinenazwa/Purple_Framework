@extends('layouts.master')

@section('content')

{{-- ============================================================
     STYLES
============================================================ --}}
<style>
/* ---- Page header ---- */
.kantin-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 28px;
  flex-wrap: wrap;
}
.kantin-icon-wrap {
  width: 48px; height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #7c4dff, #448aff);
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  font-size: 1.4rem;
  flex-shrink: 0;
}

/* ---- Cards ---- */
.kantin-card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(0,0,0,.07);
}
.kantin-card .card-header {
  background: linear-gradient(135deg, #7c4dff, #448aff);
  color: #fff;
  border-radius: 16px 16px 0 0 !important;
  font-weight: 600;
  padding: 14px 20px;
  border: none;
}

/* ---- Pay button ---- */
#payBtn {
  background: linear-gradient(135deg, #00c853, #69f0ae);
  border: none;
  color: #fff;
  font-weight: 600;
  padding: 10px 28px;
  border-radius: 10px;
  transition: opacity .2s;
}
#payBtn:hover:not(:disabled) { opacity: .88; }
#payBtn:disabled { opacity: .5; }

/* ---- My-order chip ---- */
.my-order-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #ede7ff;
  color: #5c35cc;
  border-radius: 20px;
  padding: 5px 14px;
  font-size: 12px;
  font-weight: 600;
  text-decoration: none;
  transition: background .2s;
}
.my-order-chip:hover { background: #d9cfff; color: #5c35cc; }

/* ---- QR Modal ---- */
#qrModal .modal-content {
  border: none;
  border-radius: 20px;
  overflow: hidden;
}
#qrModal .modal-header {
  background: linear-gradient(135deg, #7c4dff, #448aff);
  color: #fff;
  border: none;
  padding: 18px 24px;
}
#qrModal .modal-header .btn-close { filter: invert(1); }

#qr-canvas-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px 0 8px;
}
#qr-canvas-wrap canvas {
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(124,77,255,.2);
}

.order-id-box {
  margin-top: 14px;
  padding: 8px 20px;
  background: #f5f3ff;
  border-radius: 8px;
  text-align: center;
}
.order-id-box small { color: #888; font-size: 11px; display: block; }
.order-id-box code  { font-size: 13px; color: #5c35cc; font-weight: 600; word-break: break-all; }

.qr-hint {
  text-align: center;
  color: #888;
  font-size: 12px;
  margin-top: 10px;
}
.qr-hint i { color: #7c4dff; }

.btn-dl-qr {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: linear-gradient(135deg, #7c4dff, #448aff);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 8px 20px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity .2s;
}
.btn-dl-qr:hover { opacity: .85; }

/* ---- Success icon ---- */
.success-icon-wrap {
  width: 56px; height: 56px;
  border-radius: 50%;
  background: #e8f5e9;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 8px;
  animation: popIn .4s ease;
}
@keyframes popIn {
  0%   { transform: scale(0); }
  70%  { transform: scale(1.15); }
  100% { transform: scale(1); }
}
.success-icon { font-size: 2rem; color: #00c853; }
</style>

{{-- ============================================================
     PAGE CONTENT
============================================================ --}}
<div class="kantin-header">
  <span class="kantin-icon-wrap"><i class="mdi mdi-food-apple"></i></span>
  <div>
    <h4 class="mb-0 fw-bold">Kantin Online</h4>
    <small class="text-muted">Pilih menu dan selesaikan pembayaran</small>
  </div>
  <div class="ms-auto">
    <a href="{{ route('pos.my-order') }}" class="my-order-chip" id="chip-my-order" style="display:none">
      <i class="mdi mdi-qrcode"></i> Lihat QR Pesanan Saya
    </a>
  </div>
</div>

<div class="row">

  {{-- ---- Form pesan ---- --}}
  <div class="col-md-5 mb-4">
    <div class="card kantin-card h-100">
      <div class="card-header"><i class="mdi mdi-silverware-fork-knife me-2"></i>Pesan Menu</div>
      <div class="card-body">

        <div class="mb-3">
          <label class="form-label fw-semibold">Pilih Vendor</label>
          <select id="vendorSelect" class="form-select">
            <option value="">-- Pilih Vendor --</option>
            @foreach($vendors as $v)
              <option value="{{ $v->id }}">{{ $v->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Pilih Menu</label>
          <select id="menuSelect" class="form-select" disabled>
            <option value="">-- Pilih Menu --</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Jumlah</label>
          <input id="qtyInput" type="number" min="1" value="1" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Catatan</label>
          <textarea id="noteInput" class="form-control" rows="2"></textarea>
        </div>

        <div class="d-grid">
          <button id="addBtn" class="btn btn-primary" disabled>
            <i class="mdi mdi-plus me-1"></i>Tambahkan ke Keranjang
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- ---- Keranjang ---- --}}
  <div class="col-md-7 mb-4">
    <div class="card kantin-card h-100">
      <div class="card-header"><i class="mdi mdi-cart me-2"></i>Keranjang Anda</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm mb-0" id="cartTable">
            <thead class="table-light">
              <tr>
                <th>Nama</th>
                <th class="text-end">Harga</th>
                <th class="text-center">Jml</th>
                <th>Catatan</th>
                <th class="text-end">Subtotal</th>
                <th></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <div class="card-footer bg-white border-top-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <span class="text-muted">Total:</span>
            <h5 id="totalAmount" class="mb-0 d-inline ms-2 fw-bold text-success">Rp 0</h5>
          </div>
          <button id="payBtn" class="btn" disabled>
            <i class="mdi mdi-cash-check me-2"></i>Pesan &amp; Bayar
          </button>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- ============================================================
     QR CODE MODAL  (client-side via qrcode.js)
============================================================ --}}
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="qrModalLabel">
          <i class="mdi mdi-check-circle me-2"></i>Pesanan Berhasil!
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body py-0">

        {{-- Success icon --}}
        <div class="text-center mt-3 mb-1">
          <div class="success-icon-wrap">
            <i class="mdi mdi-check-circle success-icon"></i>
          </div>
          <p class="text-muted small mb-0">Pesanan kamu telah tersimpan</p>
        </div>

        {{-- QR Canvas --}}
        <div id="qr-canvas-wrap">
          <div id="qr-canvas"></div>
        </div>

        {{-- Order ID --}}
        <div class="order-id-box">
          <small>ID Pesanan</small>
          <code id="qr-order-id-text">—</code>
        </div>

        <p class="qr-hint mt-3">
          <i class="mdi mdi-information-outline me-1"></i>
          Tunjukkan QR Code ini ke kasir untuk konfirmasi pesanan.
        </p>

        <div class="text-center mb-3">
          <button id="btn-dl-qr" class="btn-dl-qr">
            <i class="mdi mdi-download"></i> Unduh QR Code
          </button>
        </div>
      </div>

      <div class="modal-footer border-0 pt-0 justify-content-center">
        <a href="{{ route('pos.my-order') }}" class="btn btn-outline-primary btn-sm">
          <i class="mdi mdi-history me-1"></i>Lihat Semua Pesanan Saya
        </a>
        <a href="{{ route('kantin.index') }}" class="btn btn-outline-success btn-sm">
          <i class="mdi mdi-cart-plus me-1"></i>Pesan Lagi
        </a>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>

    </div>
  </div>
</div>

@endsection


@push('js-page')
{{-- qrcode.js (client-side QR generator, no server needed) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
(function () {

  /* ====================================================
     DOM refs
  ==================================================== */
  const vendorSelect   = document.getElementById('vendorSelect');
  const menuSelect     = document.getElementById('menuSelect');
  const qtyInput       = document.getElementById('qtyInput');
  const noteInput      = document.getElementById('noteInput');
  const addBtn         = document.getElementById('addBtn');
  const cartTbody      = document.querySelector('#cartTable tbody');
  const totalAmount    = document.getElementById('totalAmount');
  const payBtn         = document.getElementById('payBtn');
  const chipMyOrder    = document.getElementById('chip-my-order');
  const qrOrderIdText  = document.getElementById('qr-order-id-text');
  const btnDlQr        = document.getElementById('btn-dl-qr');

  let cart = [];
  let qrInstance = null;

  /* ====================================================
     localStorage helpers  (key sama dengan My Order page)
  ==================================================== */
  const LS_KEY = 'kantin_pesanan_history';

  function loadHistory() {
    try { return JSON.parse(localStorage.getItem(LS_KEY) || '[]'); }
    catch(e) { return []; }
  }
  function saveToHistory(entry) {
    const history = loadHistory();
    const existing = history.findIndex(h => h.order_id === entry.order_id);
    if (existing >= 0) { history[existing] = entry; }
    else { history.unshift(entry); }          // newest first
    if (history.length > 20) history.splice(20);
    localStorage.setItem(LS_KEY, JSON.stringify(history));
  }

  /* ---- Show chip if history exists ---- */
  function refreshChip() {
    chipMyOrder.style.display = loadHistory().length > 0 ? '' : 'none';
  }
  refreshChip();

  /* ====================================================
     Format helpers
  ==================================================== */
  function formatRp(v) {
    return 'Rp ' + (Number(v) || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  /* ====================================================
     Cart
  ==================================================== */
  function updateTotal() {
    const total = cart.reduce((s, i) => s + (i.price * i.jumlah), 0);
    totalAmount.textContent = formatRp(total);
    payBtn.disabled = cart.length === 0;
  }

  function renderCart() {
    cartTbody.innerHTML = '';
    cart.forEach((item, idx) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${item.name}</td>
        <td class="text-end">${formatRp(item.price)}</td>
        <td class="text-center">${item.jumlah}</td>
        <td>${item.catatan || ''}</td>
        <td class="text-end">${formatRp(item.price * item.jumlah)}</td>
        <td>
          <button data-idx="${idx}" class="btn btn-sm btn-danger btn-remove">
            <i class="mdi mdi-trash-can-outline"></i>
          </button>
        </td>
      `;
      cartTbody.appendChild(tr);
    });
    updateTotal();
  }

  cartTbody.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-remove');
    if (!btn) return;
    cart.splice(Number(btn.dataset.idx), 1);
    renderCart();
  });

  /* ====================================================
     Vendor → Menu cascade
  ==================================================== */
  vendorSelect.addEventListener('change', function () {
    const vid = this.value;
    menuSelect.innerHTML = '<option value="">-- Memuat… --</option>';
    menuSelect.disabled = true;
    addBtn.disabled = true;

    if (!vid) {
      menuSelect.innerHTML = '<option value="">-- Pilih Menu --</option>';
      return;
    }

    fetch('/api/menus?vendor_id=' + encodeURIComponent(vid))
      .then(r => r.json())
      .then(data => {
        menuSelect.innerHTML = '<option value="">-- Pilih Menu --</option>';
        if (!Array.isArray(data)) { try { data = data.data || []; } catch(e) { data = []; } }
        let added = 0;
        data.forEach(it => {
          const id    = it.id ?? it.idmenu ?? it.id_menu ?? it.id_barang ?? null;
          const name  = it.name ?? it.nama ?? it.nama_menu ?? ('Menu ' + (id ?? ''));
          const price = Number(it.price ?? it.harga ?? it.sell_price ?? 0) || 0;
          if (id === null) return;
          const opt = document.createElement('option');
          opt.value = String(id);
          opt.dataset.price = String(price);
          opt.dataset.name  = String(name);
          opt.textContent   = name + ' — ' + formatRp(price);
          menuSelect.appendChild(opt);
          added++;
        });
        menuSelect.disabled = added === 0;
        if (added === 0) menuSelect.innerHTML = '<option value="">-- Tidak ada menu --</option>';
      })
      .catch(() => {
        menuSelect.innerHTML = '<option value="">-- Gagal memuat --</option>';
        menuSelect.disabled = true;
      });
  });

  menuSelect.addEventListener('change', function () {
    addBtn.disabled = !this.value;
  });

  addBtn.addEventListener('click', function () {
    if (!menuSelect.value) return;
    const sel     = menuSelect.options[menuSelect.selectedIndex];
    const id      = sel.value;
    const name    = sel.dataset.name || sel.textContent || '';
    const price   = Number(sel.dataset.price || 0) || 0;
    const jumlah  = Math.max(1, parseInt(qtyInput.value || '1'));
    const catatan = noteInput.value || '';

    cart.push({ id, name, price, jumlah, catatan });
    qtyInput.value = 1;
    noteInput.value = '';
    menuSelect.selectedIndex = 0;
    addBtn.disabled = true;
    renderCart();
  });

  /* ====================================================
     QR CODE  (qrcode.js — client-side, no server needed)
  ==================================================== */
  function generateQr(orderId) {
    qrOrderIdText.textContent = orderId;

    // Clear previous
    const container = document.getElementById('qr-canvas');
    container.innerHTML = '';

    qrInstance = new QRCode(container, {
      text:         orderId,
      width:        220,
      height:       220,
      colorDark:    '#3d1fa3',
      colorLight:   '#ffffff',
      correctLevel: QRCode.CorrectLevel.H,
    });
  }

  /* ---- Download button ---- */
  btnDlQr.addEventListener('click', function () {
    const canvas = document.querySelector('#qr-canvas canvas');
    if (!canvas) return;
    const link      = document.createElement('a');
    link.download   = 'QR-Pesanan-' + (qrOrderIdText.textContent || 'order') + '.png';
    link.href       = canvas.toDataURL('image/png');
    link.click();
  });

  function openQrModal(orderId) {
    generateQr(orderId);
    const modal = new bootstrap.Modal(document.getElementById('qrModal'));
    modal.show();
  }

  /* ====================================================
     PAYMENT  →  simpan localStorage  →  tampilkan QR
  ==================================================== */
  payBtn.addEventListener('click', function () {
    if (cart.length === 0) return;

    const items = cart.map(it => ({
      id_barang: it.id,
      jumlah:    it.jumlah,
      subtotal:  it.price * it.jumlah,
      catatan:   it.catatan || '',
    }));
    const total = items.reduce((s, i) => s + i.subtotal, 0);
    const csrf  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    payBtn.disabled = true;
    payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses…';

    fetch('/api/pos/penjualan', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      body:    JSON.stringify({ items, total }),
    })
    .then(r => r.json())
    .then(data => {
      if (!data.success) throw { message: data.message || 'Gagal menyimpan pesanan', id: data.id };

      // === ID pesanan yang akan di-encode ke QR ===
      const orderId   = data.order_id ?? ('PESANAN-' + (data.id ?? Date.now()));
      const snapToken = data.snap_token ?? null;

      // === Simpan ke localStorage ===
      saveToHistory({
        order_id:   orderId,
        db_id:      data.id ?? null,
        snap_token: snapToken,
        total:      total,
        items:      cart.map(c => ({ name: c.name, jumlah: c.jumlah, subtotal: c.price * c.jumlah })),
        created_at: new Date().toISOString(),
      });
      refreshChip();

      // === Reset cart ===
      cart = [];
      renderCart();
      payBtn.disabled = true;
      payBtn.innerHTML = '<i class="mdi mdi-cash-check me-2"></i>Pesan &amp; Bayar';

      // === Jika ada snap_token, buka Midtrans — QR hanya muncul setelah bayar sukses/pending ===
      if (snapToken) {
        const clientKey = '{{ config("services.midtrans.client_key") }}';
        const isProd    = {{ config('services.midtrans.is_production') ? 'true' : 'false' }};
        const snapSrc   = (isProd
          ? 'https://app.midtrans.com/snap/snap.js'
          : 'https://app.sandbox.midtrans.com/snap/snap.js')
          + '?client-key=' + encodeURIComponent(clientKey);

        function doSnap() {
          window.snap.pay(snapToken, {
            onSuccess: () => openQrModal(orderId),   // ✅ Bayar sukses → tampilkan QR
            onPending: () => openQrModal(orderId),   // ✅ Pending → tampilkan QR
            onError: (r) => {
              Swal.fire({ icon: 'error', title: 'Pembayaran Gagal', text: JSON.stringify(r) });
              payBtn.disabled = false;
              payBtn.innerHTML = '<i class="mdi mdi-cash-check me-2"></i>Pesan &amp; Bayar';
            },
            onClose: () => {
              // ❌ User tutup popup tanpa bayar — TIDAK tampilkan QR
              Swal.fire({
                icon: 'info',
                title: 'Pembayaran Dibatalkan',
                text: 'Silakan selesaikan pembayaran terlebih dahulu untuk mendapatkan QR Code.',
                confirmButtonText: 'OK',
              });
              payBtn.disabled = false;
              payBtn.innerHTML = '<i class="mdi mdi-cash-check me-2"></i>Pesan &amp; Bayar';
            },
          });
        }

        if (window.snap) {
          doSnap();
        } else {
          const s = document.createElement('script');
          s.src = snapSrc;
          s.onload = doSnap;
          document.body.appendChild(s);
        }

      } else {
        // Tidak ada Midtrans — tampilkan notif bahwa pembayaran diperlukan
        Swal.fire({
          icon: 'warning',
          title: 'Pembayaran Diperlukan',
          text: 'QR Code hanya akan diberikan setelah pembayaran berhasil. Hubungi kasir untuk proses pembayaran.',
          confirmButtonText: 'OK',
        });
        payBtn.disabled = false;
        payBtn.innerHTML = '<i class="mdi mdi-cash-check me-2"></i>Pesan &amp; Bayar';
      }
    })
    .catch(err => {
      console.error(err);
      // Error server — TIDAK tampilkan QR (pembayaran belum terkonfirmasi)
      Swal.fire({ icon: 'error', title: 'Error', text: err.message || String(err) });
      payBtn.disabled = false;
      payBtn.innerHTML = '<i class="mdi mdi-cash-check me-2"></i>Pesan &amp; Bayar';
    });
  });

})();
</script>
@endpush
