@extends('layouts.master')

@section('content')

<style>
/* ---- Page header ---- */
.myorder-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 28px;
  flex-wrap: wrap;
}
.myorder-icon-wrap {
  width: 48px; height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #7c4dff, #448aff);
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  font-size: 1.4rem;
  flex-shrink: 0;
}

/* ---- Empty state ---- */
.empty-state {
  text-align: center;
  padding: 60px 20px;
}
.empty-state-icon {
  font-size: 5rem;
  color: #d0c8ff;
  display: block;
  margin-bottom: 16px;
}
.empty-state h5 { color: #666; margin-bottom: 8px; }
.empty-state p  { color: #aaa; font-size: 14px; }

/* ---- Order cards ---- */
.order-card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 3px 16px rgba(0,0,0,.07);
  transition: box-shadow .2s, transform .2s;
  overflow: hidden;
  margin-bottom: 20px;
}
.order-card:hover { box-shadow: 0 6px 28px rgba(124,77,255,.15); transform: translateY(-2px); }

.order-card-header {
  background: linear-gradient(135deg, #7c4dff18, #448aff10);
  border-bottom: 1px solid #ede7ff;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  flex-wrap: wrap;
}
.order-number {
  font-weight: 700;
  color: #5c35cc;
  font-size: 14px;
  word-break: break-all;
}
.order-time {
  font-size: 11px;
  color: #aaa;
}
.badge-status {
  font-size: 11px;
  padding: 4px 10px;
  border-radius: 20px;
  background: #ede7ff;
  color: #5c35cc;
  font-weight: 600;
}

.order-card-body {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 16px 18px;
  flex-wrap: wrap;
}

/* ---- QR thumbnail ---- */
.qr-thumb-wrap {
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}
.qr-thumb-wrap canvas {
  border-radius: 8px;
  box-shadow: 0 2px 12px rgba(124,77,255,.15);
  cursor: pointer;
  transition: transform .2s;
}
.qr-thumb-wrap canvas:hover { transform: scale(1.05); }
.qr-thumb-label {
  font-size: 10px;
  color: #aaa;
  text-align: center;
}

/* ---- Order info ---- */
.order-info { flex: 1; min-width: 0; }
.order-info .info-row {
  display: flex;
  gap: 6px;
  font-size: 13px;
  color: #666;
  margin-bottom: 4px;
  align-items: flex-start;
}
.order-info .info-row i { color: #7c4dff; margin-top: 2px; flex-shrink: 0; }
.order-info .info-row strong { color: #333; }
.order-total {
  font-size: 18px;
  font-weight: 700;
  color: #00c853;
  margin-top: 8px;
}

/* ---- Actions ---- */
.order-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.btn-show-qr {
  background: linear-gradient(135deg, #7c4dff, #448aff);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 6px 14px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity .2s;
  display: inline-flex; align-items: center; gap: 5px;
}
.btn-show-qr:hover { opacity: .85; }
.btn-dl-qr-sm {
  background: #f0ebff;
  color: #5c35cc;
  border: none;
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: background .2s;
  display: inline-flex; align-items: center; gap: 5px;
}
.btn-dl-qr-sm:hover { background: #ddd4ff; }
.btn-del-order {
  background: #fff0f0;
  color: #e53935;
  border: none;
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: background .2s;
}
.btn-del-order:hover { background: #ffd6d6; }

/* ---- Full QR modal ---- */
#qrFullModal .modal-content { border: none; border-radius: 20px; overflow: hidden; }
#qrFullModal .modal-header {
  background: linear-gradient(135deg, #7c4dff, #448aff);
  color: #fff; border: none;
}
#qrFullModal .modal-header .btn-close { filter: invert(1); }
#qr-full-canvas-wrap {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 20px 0 8px;
}
#qr-full-canvas-wrap canvas {
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(124,77,255,.2);
}
.order-id-box {
  margin-top: 12px;
  padding: 8px 20px;
  background: #f5f3ff;
  border-radius: 8px;
  text-align: center;
}
.order-id-box small { color: #888; font-size: 11px; display: block; }
.order-id-box code  { font-size: 13px; color: #5c35cc; font-weight: 600; word-break: break-all; }
.qr-hint {
  text-align: center; color: #888; font-size: 12px; margin-top: 10px;
}
.btn-clear-all {
  background: #fff0f0; color: #e53935;
  border: 1px solid #ffcdd2;
  border-radius: 8px; padding: 6px 14px;
  font-size: 12px; font-weight: 600;
  transition: background .2s;
}
.btn-clear-all:hover { background: #ffd6d6; }
</style>

{{-- ---- Header ---- --}}
<div class="myorder-header">
  <span class="myorder-icon-wrap"><i class="mdi mdi-qrcode-scan"></i></span>
  <div>
    <h4 class="mb-0 fw-bold">Pesanan Saya</h4>
    <small class="text-muted">Riwayat pesanan &amp; QR Code tersimpan di perangkat ini</small>
  </div>
  <div class="ms-auto d-flex gap-2 align-items-center">
    <a href="{{ route('kantin.index') }}" class="btn btn-sm btn-outline-primary">
      <i class="mdi mdi-plus me-1"></i>Buat Pesanan Baru
    </a>
    <button id="btn-clear-all" class="btn-clear-all d-none">
      <i class="mdi mdi-delete-sweep me-1"></i>Hapus Semua
    </button>
  </div>
</div>

{{-- ---- Order list (di-render oleh JS) ---- --}}
<div id="order-list"></div>

{{-- ---- Empty state ---- --}}
<div id="empty-state" class="empty-state d-none">
  <i class="mdi mdi-clipboard-text-off-outline empty-state-icon"></i>
  <h5>Belum ada pesanan</h5>
  <p>Riwayat pesanan Anda akan muncul di sini setelah melakukan transaksi.</p>
  <a href="{{ route('kantin.index') }}" class="btn btn-gradient-primary">
    <i class="mdi mdi-cart-plus me-2"></i>Mulai Pesan Sekarang
  </a>
</div>

{{-- ============================================================
     FULL-SIZE QR MODAL
============================================================ --}}
<div class="modal fade" id="qrFullModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">
          <i class="mdi mdi-qrcode me-2"></i>QR Code Pesanan
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body py-0">
        <div id="qr-full-canvas-wrap">
          <div id="qr-full-canvas"></div>
        </div>
        <div class="order-id-box">
          <small>ID Pesanan</small>
          <code id="qr-full-order-id-text">—</code>
        </div>
        <p class="qr-hint mt-3">
          <i class="mdi mdi-information-outline me-1"></i>
          Tunjukkan QR Code ini ke kasir/vendor untuk konfirmasi.
        </p>
        <div class="text-center mb-3">
          <button id="btn-dl-qr-full" class="btn-show-qr">
            <i class="mdi mdi-download"></i>Unduh QR Code
          </button>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0 justify-content-center">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@endsection


@push('js-page')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
(function () {

  /* ====================================================
     localStorage helpers
  ==================================================== */
  const LS_KEY = 'kantin_pesanan_history';

  function loadHistory() {
    try { return JSON.parse(localStorage.getItem(LS_KEY) || '[]'); }
    catch(e) { return []; }
  }
  function saveHistory(arr) {
    localStorage.setItem(LS_KEY, JSON.stringify(arr));
  }
  function deleteEntry(orderId) {
    const h = loadHistory().filter(e => e.order_id !== orderId);
    saveHistory(h);
    renderPage();
  }
  function clearAll() {
    localStorage.removeItem(LS_KEY);
    renderPage();
  }

  /* ====================================================
     Format helpers
  ==================================================== */
  function formatRp(v) {
    return 'Rp ' + Number(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }
  function formatDate(iso) {
    if (!iso) return '—';
    try {
      const d = new Date(iso);
      return d.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
    } catch(e) { return iso; }
  }

  /* ====================================================
     QR Code full modal
  ==================================================== */
  let fullQrInstance = null;
  const qrFullCanvas     = document.getElementById('qr-full-canvas');
  const qrFullOrderText  = document.getElementById('qr-full-order-id-text');
  const btnDlFull        = document.getElementById('btn-dl-qr-full');

  function openFullQr(orderId) {
    qrFullOrderText.textContent = orderId;
    qrFullCanvas.innerHTML = '';
    fullQrInstance = new QRCode(qrFullCanvas, {
      text:         orderId,
      width:        240,
      height:       240,
      colorDark:    '#3d1fa3',
      colorLight:   '#ffffff',
      correctLevel: QRCode.CorrectLevel.H,
    });
    const modal = new bootstrap.Modal(document.getElementById('qrFullModal'));
    modal.show();
  }

  btnDlFull.addEventListener('click', function () {
    const canvas = qrFullCanvas.querySelector('canvas');
    if (!canvas) return;
    const link = document.createElement('a');
    link.download = 'QR-' + (qrFullOrderText.textContent || 'pesanan') + '.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
  });

  /* ====================================================
     Render order list
  ==================================================== */
  function renderPage() {
    const history = loadHistory();
    const container   = document.getElementById('order-list');
    const emptyState  = document.getElementById('empty-state');
    const btnClearAll = document.getElementById('btn-clear-all');

    container.innerHTML = '';

    if (history.length === 0) {
      emptyState.classList.remove('d-none');
      btnClearAll.classList.add('d-none');
      return;
    }

    emptyState.classList.add('d-none');
    btnClearAll.classList.remove('d-none');

    history.forEach((entry, idx) => {
      const orderId   = entry.order_id || '—';
      const createdAt = formatDate(entry.created_at);
      const total     = entry.total ? formatRp(entry.total) : '—';
      const items     = entry.items || [];

      // Build items summary
      const itemsSummary = items.length > 0
        ? items.map(i => `${i.name} ×${i.jumlah}`).join(', ')
        : 'Detail tidak tersedia';

      // Card wrapper
      const card = document.createElement('div');
      card.className = 'order-card card';
      card.dataset.orderId = orderId;

      card.innerHTML = `
        <div class="order-card-header">
          <div>
            <div class="order-number"><i class="mdi mdi-receipt me-1"></i>${orderId}</div>
            <div class="order-time"><i class="mdi mdi-clock-outline me-1"></i>${createdAt}</div>
          </div>
          <span class="badge-status"><i class="mdi mdi-check-circle me-1"></i>Tersimpan</span>
        </div>
        <div class="order-card-body">
          <!-- QR thumb (will be appended by JS) -->
          <div class="qr-thumb-wrap" id="qr-thumb-${idx}">
            <div id="qr-thumb-canvas-${idx}"></div>
            <span class="qr-thumb-label">Klik untuk perbesar</span>
          </div>
          <!-- Info -->
          <div class="order-info">
            <div class="info-row">
              <i class="mdi mdi-food-outline"></i>
              <span>${itemsSummary}</span>
            </div>
            <div class="order-total">${total}</div>
          </div>
          <!-- Actions -->
          <div class="order-actions">
            <button class="btn-show-qr" data-order-id="${orderId}">
              <i class="mdi mdi-qrcode"></i> Lihat QR
            </button>
            <button class="btn-dl-qr-sm" data-idx="${idx}">
              <i class="mdi mdi-download"></i>
            </button>
            <button class="btn-del-order" data-order-id="${orderId}" title="Hapus dari riwayat">
              <i class="mdi mdi-delete-outline"></i>
            </button>
          </div>
        </div>
      `;

      container.appendChild(card);

      // Generate thumbnail QR
      const thumbContainer = document.getElementById(`qr-thumb-canvas-${idx}`);
      const thumbQr = new QRCode(thumbContainer, {
        text:         orderId,
        width:        90,
        height:       90,
        colorDark:    '#3d1fa3',
        colorLight:   '#ffffff',
        correctLevel: QRCode.CorrectLevel.M,
      });

      // Click on thumb → open full modal
      thumbContainer.style.cursor = 'pointer';
      thumbContainer.addEventListener('click', () => openFullQr(orderId));

      // Btn show QR
      card.querySelector('.btn-show-qr').addEventListener('click', () => openFullQr(orderId));

      // Btn download thumb QR
      card.querySelector('.btn-dl-qr-sm').addEventListener('click', () => {
        // Use a timeout to ensure canvas is rendered
        setTimeout(() => {
          const canvas = thumbContainer.querySelector('canvas');
          if (!canvas) return;
          // Draw bigger version for download
          const big = document.createElement('canvas');
          big.width = big.height = 240;
          const ctx = big.getContext('2d');
          ctx.drawImage(canvas, 0, 0, 240, 240);
          const link = document.createElement('a');
          link.download = 'QR-' + orderId + '.png';
          link.href = big.toDataURL('image/png');
          link.click();
        }, 100);
      });

      // Btn delete
      card.querySelector('.btn-del-order').addEventListener('click', () => {
        Swal.fire({
          icon: 'warning',
          title: 'Hapus pesanan ini?',
          text: 'Riwayat pesanan akan dihapus dari perangkat ini.',
          showCancelButton: true,
          confirmButtonText: 'Ya, Hapus',
          cancelButtonText: 'Batal',
          confirmButtonColor: '#e53935',
        }).then(r => { if (r.isConfirmed) deleteEntry(orderId); });
      });
    });
  }

  /* ====================================================
     Clear all button
  ==================================================== */
  document.getElementById('btn-clear-all').addEventListener('click', () => {
    Swal.fire({
      icon: 'warning',
      title: 'Hapus Semua Riwayat?',
      text: 'Semua data pesanan yang tersimpan di perangkat ini akan dihapus.',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus Semua',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#e53935',
    }).then(r => { if (r.isConfirmed) clearAll(); });
  });

  /* ====================================================
     Init
  ==================================================== */
  renderPage();

})();
</script>
@endpush
