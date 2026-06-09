@extends('layouts.master')

@section('content')

{{-- ============================================================
     STYLES
============================================================ --}}
<style>
/* ---- Page header ---- */
.pos-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 28px;
}
.pos-header-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 48px; height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #7c4dff, #448aff);
  color: #fff;
  font-size: 1.4rem;
  flex-shrink: 0;
}

/* ---- Cards ---- */
.pos-card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(0,0,0,.07);
}
.pos-card .card-header {
  background: linear-gradient(135deg, #7c4dff, #448aff);
  color: #fff;
  border-radius: 16px 16px 0 0 !important;
  font-weight: 600;
  letter-spacing: .3px;
  padding: 14px 20px;
}

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
  padding: 24px 0 12px;
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
.order-id-box code { font-size: 13px; color: #5c35cc; font-weight: 600; word-break: break-all; }

.qr-hint {
  text-align: center;
  color: #888;
  font-size: 12px;
  margin-top: 10px;
}
.qr-hint i { color: #7c4dff; }

/* ---- Download button ---- */
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
  text-decoration: none;
  transition: opacity .2s;
  cursor: pointer;
}
.btn-dl-qr:hover { opacity: .85; color: #fff; }

/* ---- My-order shortcut chip ---- */
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
</style>

{{-- ============================================================
     CONTENT
============================================================ --}}
<div class="pos-header">
  <span class="pos-header-icon"><i class="mdi mdi-cart-outline"></i></span>
  <div>
    <h4 class="mb-0 fw-bold">Point of Sale — Pesan</h4>
    <small class="text-muted">Tambahkan item ke keranjang lalu selesaikan pembayaran</small>
  </div>
  <div class="ms-auto">
    <a href="{{ route('pos.my-order') }}" class="my-order-chip" id="chip-my-order" style="display:none">
      <i class="mdi mdi-qrcode"></i> Lihat QR Pesanan Saya
    </a>
  </div>
</div>

<div class="row">
  {{-- ---- Form cari barang ---- --}}
  <div class="col-md-5 mb-4">
    <div class="card pos-card h-100">
      <div class="card-header"><i class="mdi mdi-magnify me-2"></i>Cari Menu / Barang</div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label fw-semibold">Kode Barang</label>
          <input id="kodeInput" class="form-control" placeholder="Masukkan kode lalu tekan Enter">
          <div class="form-text">Tekan <kbd>Enter</kbd> untuk mencari barang.</div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Nama Barang</label>
          <input id="namaBarang" class="form-control" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Harga Barang</label>
          <input id="hargaBarang" class="form-control" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Jumlah</label>
          <input id="qtyInput" type="number" min="1" value="1" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Catatan</label>
          <textarea id="noteInput" class="form-control" rows="2"></textarea>
        </div>
        <div class="mb-3 form-check form-switch">
          <input class="form-check-input" type="checkbox" id="useAxios">
          <label class="form-check-label" for="useAxios">Gunakan Axios</label>
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
    <div class="card pos-card h-100">
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
     QR CODE MODAL
============================================================ --}}
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="qrModalLabel">
          <i class="mdi mdi-check-circle me-2"></i>Pesanan Berhasil!
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body py-0">
        <div id="qr-canvas-wrap">
          {{-- canvas di-inject oleh qrcode.js --}}
          <div id="qr-canvas"></div>
        </div>

        <div class="order-id-box">
          <small>ID Pesanan</small>
          <code id="qr-order-id-text">—</code>
        </div>

        <p class="qr-hint mt-3">
          <i class="mdi mdi-information-outline me-1"></i>
          Tunjukkan QR Code ini ke kasir/vendor untuk konfirmasi pesanan.
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
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>

    </div>
  </div>
</div>

@endsection


@push('js-page')
{{-- qrcode.js (lightweight, no dependencies) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
(function () {

  /* ====================================================
     DOM refs
  ==================================================== */
  const kodeInput   = document.getElementById('kodeInput');
  const namaBarang  = document.getElementById('namaBarang');
  const hargaBarang = document.getElementById('hargaBarang');
  const qtyInput    = document.getElementById('qtyInput');
  const noteInput   = document.getElementById('noteInput');
  const addBtn      = document.getElementById('addBtn');
  const useAxios    = document.getElementById('useAxios');
  const cartTbody   = document.querySelector('#cartTable tbody');
  const totalAmount = document.getElementById('totalAmount');
  const payBtn      = document.getElementById('payBtn');
  const chipMyOrder = document.getElementById('chip-my-order');

  // QR modal refs
  const qrOrderIdText = document.getElementById('qr-order-id-text');
  const btnDlQr       = document.getElementById('btn-dl-qr');

  let cart = [];
  let qrInstance = null;  // qrcode.js instance

  /* ====================================================
     localStorage key
  ==================================================== */
  const LS_KEY = 'kantin_pesanan_history';

  function loadHistory() {
    try { return JSON.parse(localStorage.getItem(LS_KEY) || '[]'); }
    catch(e) { return []; }
  }
  function saveToHistory(entry) {
    const history = loadHistory();
    // prevent duplicates by order_id
    const existing = history.findIndex(h => h.order_id === entry.order_id);
    if (existing >= 0) {
      history[existing] = entry;
    } else {
      history.unshift(entry);          // newest first
    }
    // keep max 20 entries
    if (history.length > 20) history.splice(20);
    localStorage.setItem(LS_KEY, JSON.stringify(history));
  }

  /* ====================================================
     Show chip if there's history
  ==================================================== */
  function refreshChip() {
    const h = loadHistory();
    if (h.length > 0) chipMyOrder.style.display = '';
    else chipMyOrder.style.display = 'none';
  }
  refreshChip();

  /* ====================================================
     Helpers
  ==================================================== */
  function formatRp(v) {
    return 'Rp ' + Number(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

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
        <td class="text-center">
          <input class="form-control form-control-sm qty-cell text-center"
                 data-idx="${idx}" type="number" min="1" value="${item.jumlah}" style="width:60px;margin:auto">
        </td>
        <td>${item.catatan || ''}</td>
        <td class="text-end subtotal">${formatRp(item.price * item.jumlah)}</td>
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

  /* ====================================================
     Cart event delegation
  ==================================================== */
  cartTbody.addEventListener('input', function (e) {
    if (!e.target.matches('.qty-cell')) return;
    const idx = Number(e.target.dataset.idx);
    const val = Math.max(1, parseInt(e.target.value || '1'));
    cart[idx].jumlah = val;
    e.target.closest('tr').querySelector('.subtotal').textContent = formatRp(cart[idx].price * val);
    updateTotal();
  });

  cartTbody.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-remove');
    if (!btn) return;
    cart.splice(Number(btn.dataset.idx), 1);
    renderCart();
  });

  /* ====================================================
     Item lookup (Enter on kode input)
  ==================================================== */
  kodeInput.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    const kode = kodeInput.value.trim();
    if (!kode) return;

    if (useAxios.checked && window.axios) {
      axios.get('/api/pos/barang/' + encodeURIComponent(kode))
        .then(r => { const p = r.data?.data ?? r.data ?? r; onItemFound(p, kode); })
        .catch(() => onItemNotFound());
    } else {
      fetch('/api/pos/barang/' + encodeURIComponent(kode))
        .then(r => { if (!r.ok) throw new Error('not found'); return r.json(); })
        .then(it => { const p = it.data ?? it; onItemFound(p, kode); })
        .catch(() => onItemNotFound());
    }
  });

  function onItemFound(it, kode) {
    const name  = it.name ?? it.nama ?? it.nama_barang ?? '';
    const price = Number(it.price ?? it.harga ?? it.harga_jual ?? 0);
    namaBarang.value = name;
    hargaBarang.value = price > 0 ? formatRp(price) : '';
    namaBarang.dataset.kode = kode;
    namaBarang.dataset.priceRaw = price;
    qtyInput.value = 1;
    addBtn.disabled = !(Number(qtyInput.value) > 0 && name);
  }

  function onItemNotFound() {
    namaBarang.value = '';
    hargaBarang.value = '';
    delete namaBarang.dataset.kode;
    delete namaBarang.dataset.priceRaw;
    addBtn.disabled = true;
    Swal.fire({ icon: 'warning', title: 'Tidak Ditemukan', text: 'Barang dengan kode tersebut tidak ada.', timer: 2000, showConfirmButton: false });
  }

  qtyInput.addEventListener('input', function () {
    addBtn.disabled = !(Number(this.value) > 0 && namaBarang.value);
  });

  addBtn.addEventListener('click', function () {
    const kode    = namaBarang.dataset.kode || kodeInput.value.trim();
    const name    = namaBarang.value;
    if (!kode || !name) return;
    const price   = Number(namaBarang.dataset.priceRaw || 0);
    const jumlah  = Math.max(1, parseInt(qtyInput.value || '1'));
    const catatan = noteInput.value || '';

    const existing = cart.find(c => (c.kode || '') === kode);
    if (existing) {
      existing.jumlah += jumlah;
      existing.catatan = catatan || existing.catatan;
    } else {
      cart.push({ kode, id: kode, name, price, jumlah, catatan });
    }

    kodeInput.value = namaBarang.value = hargaBarang.value = '';
    qtyInput.value = 1;
    noteInput.value = '';
    addBtn.disabled = true;
    renderCart();
  });

  /* ====================================================
     QR CODE GENERATOR  (qrcode.js)
  ==================================================== */
  function showQrModal(orderId) {
    qrOrderIdText.textContent = orderId;

    // Clear previous QR
    const container = document.getElementById('qr-canvas');
    container.innerHTML = '';

    // Generate new QR
    qrInstance = new QRCode(container, {
      text:          orderId,
      width:         220,
      height:        220,
      colorDark:     '#3d1fa3',
      colorLight:    '#ffffff',
      correctLevel:  QRCode.CorrectLevel.H,
    });

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('qrModal'));
    modal.show();
  }

  /* ---- Download QR as PNG ---- */
  btnDlQr.addEventListener('click', function () {
    const canvas = document.querySelector('#qr-canvas canvas');
    if (!canvas) return;
    const link = document.createElement('a');
    link.download = 'QR-Pesanan-' + (qrOrderIdText.textContent || 'order') + '.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
  });

  /* ====================================================
     PAYMENT  — POST order → save to localStorage → show QR
  ==================================================== */
  payBtn.addEventListener('click', function () {
    if (cart.length === 0) return;

    const items = cart.map(it => ({
      id_barang: it.id_barang ?? it.id ?? it.kode,
      jumlah:    it.jumlah,
      subtotal:  it.price * it.jumlah,
      catatan:   it.catatan || '',
    }));
    const total = items.reduce((s, i) => s + i.subtotal, 0);
    const csrf  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    payBtn.disabled = true;
    payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses…';

    const payload = { items, total };

    /* ---- success handler ---- */
    function handleSuccess(resp) {
      const data = resp.data ?? resp;
      if (!data.success) throw new Error(data.message || 'Gagal menyimpan pesanan');

      // === Save to localStorage ===
      const orderId   = data.order_id ?? data.id ?? String(Date.now());
      const snapToken = data.snap_token ?? null;

      const entry = {
        order_id:   orderId,
        db_id:      data.id ?? null,
        snap_token: snapToken,
        total:      total,
        items:      cart.map(c => ({ name: c.name, jumlah: c.jumlah, subtotal: c.price * c.jumlah })),
        created_at: new Date().toISOString(),
      };
      saveToHistory(entry);
      refreshChip();

      // Reset cart
      cart = [];
      renderCart();
      payBtn.disabled = true;
      payBtn.innerHTML = '<i class="mdi mdi-cash-check me-2"></i>Pesan &amp; Bayar';

      // === Show QR Code modal ===
      showQrModal(orderId);
    }

    /* ---- error handler ---- */
    function handleError(err) {
      console.error(err);
      // Even on payment error, try to show QR with best available id
      const errMsg = err.message || String(err);

      // If server returned a partial response with id, still save & show QR
      if (err && err.id) {
        const fallbackOrderId = err.order_id ?? ('order-' + err.id);
        saveToHistory({ order_id: fallbackOrderId, db_id: err.id, total, items: cart.map(c=>({name:c.name, jumlah:c.jumlah})), created_at: new Date().toISOString() });
        refreshChip();
        showQrModal(fallbackOrderId);
      } else {
        Swal.fire({ icon: 'error', title: 'Gagal', text: errMsg });
      }
      payBtn.disabled = false;
      payBtn.innerHTML = '<i class="mdi mdi-cash-check me-2"></i>Pesan &amp; Bayar';
    }

    if (useAxios.checked && window.axios) {
      axios.post('/api/pos/penjualan', payload, { headers: { 'X-CSRF-TOKEN': csrf } })
        .then(handleSuccess)
        .catch(err => {
          const errData = err.response?.data ?? {};
          handleError({ ...errData, message: errData.message || err.message });
        });
    } else {
      fetch('/api/pos/penjualan', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify(payload),
      })
        .then(r => r.json())
        .then(handleSuccess)
        .catch(handleError);
    }
  });

})();
</script>
@endpush
