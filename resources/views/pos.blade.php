@extends('layouts.master')

@section('content')
<div class="container py-4">
  <h3 class="mb-4">Point of Sale - Pesan</h3>
  <div class="row">
    <div class="col-md-5">
      <div class="card">
        <div class="card-header">Pesan Menu</div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Pilih Vendor</label>
            <select id="vendorSelect" class="form-select">
              <option value="">-- Pilih Vendor --</option>
              @foreach($vendors as $v)
                <option value="{{ $v->id }}">{{ $v->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Pilih Menu</label>
            <select id="menuSelect" class="form-select" disabled>
              <option value="">-- Pilih Menu --</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Jumlah</label>
            <input id="qtyInput" type="number" min="1" value="1" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Catatan</label>
            <textarea id="noteInput" class="form-control" rows="2"></textarea>
          </div>

          <div class="d-grid">
            <button id="addBtn" class="btn btn-primary" disabled>Tambahkan ke Keranjang</button>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-7">
      <div class="card">
        <div class="card-header">Keranjang Anda</div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm mb-0" id="cartTable">
              <thead class="table-light">
                <tr>
                  <th>Nama</th>
                  <th class="text-end">Harga</th>
                  <th class="text-center">Jumlah</th>
                  <th>Catatan</th>
                  <th class="text-end">Subtotal</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
        <div class="card-footer">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <strong>Total:</strong>
              <h5 id="totalAmount" class="mb-0 d-inline ms-2">Rp 0</h5>
            </div>
            <div>
              <button id="payBtn" class="btn btn-success" disabled>Pesan &amp; Bayar</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('js-page')
<script>
(function(){
  const vendorSelect = document.getElementById('vendorSelect');
  const menuSelect = document.getElementById('menuSelect');
  const qtyInput = document.getElementById('qtyInput');
  const noteInput = document.getElementById('noteInput');
  const addBtn = document.getElementById('addBtn');
  const cartTbody = document.querySelector('#cartTable tbody');
  const totalAmount = document.getElementById('totalAmount');
  const payBtn = document.getElementById('payBtn');

  let cart = [];

  function formatRp(v){
    return 'Rp ' + v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function updateTotal(){
    const total = cart.reduce((s,i)=> s + (i.price * i.jumlah), 0);
    totalAmount.textContent = formatRp(total);
    payBtn.disabled = cart.length === 0;
  }

  function renderCart(){
    cartTbody.innerHTML = '';
    cart.forEach((item, idx)=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${item.name}</td>
        <td class="text-end">${formatRp(item.price)}</td>
        <td class="text-center">${item.jumlah}</td>
        <td>${item.catatan || ''}</td>
        <td class="text-end">${formatRp(item.price * item.jumlah)}</td>
        <td><button data-idx="${idx}" class="btn btn-sm btn-danger btn-remove">Hapus</button></td>
      `;
      cartTbody.appendChild(tr);
    });
    updateTotal();
  }

  // remove handler
  cartTbody.addEventListener('click', function(e){
    if (e.target && e.target.matches('.btn-remove')){
      const idx = Number(e.target.dataset.idx);
      cart.splice(idx,1);
      renderCart();
    }
  });

  vendorSelect.addEventListener('change', function(){
    const vid = this.value;
    menuSelect.innerHTML = '<option value="">-- Memuat... --</option>';
    menuSelect.disabled = true;
    addBtn.disabled = true;
    if (!vid) {
      menuSelect.innerHTML = '<option value="">-- Pilih Menu --</option>';
      return;
    }
    // fetch menus for vendor
    fetch('/api/menus?vendor_id=' + encodeURIComponent(vid))
      .then(r => r.json())
      .then(data => {
        menuSelect.innerHTML = '<option value="">-- Pilih Menu --</option>';
        if (!Array.isArray(data)) {
          // if returned object with data property
          try { data = data.data || []; } catch(e) { data = []; }
        }
        data.forEach(it => {
          // Try common column names
          const id = it.id ?? it.idmenu ?? it.ID ?? it.id_menu ?? it.id_menu;
          const name = it.name ?? it.nama ?? it.nama_menu ?? it.title ?? 'Menu';
          const price = it.price ?? it.harga ?? it.price ?? 0;
          const opt = document.createElement('option');
          opt.value = JSON.stringify({id:id,price:price,name:name});
          opt.textContent = name + ' — ' + formatRp(price);
          menuSelect.appendChild(opt);
        });
        menuSelect.disabled = false;
      }).catch(err => {
        console.error(err);
        menuSelect.innerHTML = '<option value="">-- Gagal memuat --</option>';
      });
  });

  menuSelect.addEventListener('change', function(){
    addBtn.disabled = !this.value;
  });

  addBtn.addEventListener('click', function(){
    if (!menuSelect.value) return;
    const payload = JSON.parse(menuSelect.value);
    const jumlah = Math.max(1, parseInt(qtyInput.value || '1'));
    const catatan = noteInput.value || '';
    cart.push({
      id: payload.id,
      name: payload.name,
      price: Number(payload.price) || 0,
      jumlah: jumlah,
      catatan: catatan,
    });
    // reset qty and note
    qtyInput.value = 1;
    noteInput.value = '';
    menuSelect.selectedIndex = 0;
    addBtn.disabled = true;
    renderCart();
  });

  // POST order and open Midtrans snap
  payBtn.addEventListener('click', function(){
    if (cart.length === 0) return;
    const items = cart.map(it => ({
      id_barang: it.id,
      jumlah: it.jumlah,
      subtotal: it.price * it.jumlah,
      catatan: it.catatan || ''
    }));
    const total = items.reduce((s,i)=> s + i.subtotal, 0);

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    payBtn.disabled = true;
    payBtn.textContent = 'Memproses...';

    fetch('/api/pos/penjualan', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ items: items, total: total })
    }).then(r => r.json())
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Gagal menyimpan pesanan');
        const token = data.snap_token;
        if (!token) throw new Error('Snap token tidak tersedia');

        // Midtrans client key and environment
        const midtransClientKey = '{{ config("services.midtrans.client_key") }}';
        const isProd = {{ config('services.midtrans.is_production') ? 'true' : 'false' }};
        const snapSrc = (isProd ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js') + '?client-key=' + encodeURIComponent(midtransClientKey);

        function openSnap(){
          if (!window.snap) {
            const s = document.createElement('script');
            s.src = snapSrc;
            s.onload = () => window.snap.pay(token, {
              onSuccess: function(result){ window.location.href = '/pesanan'; },
              onPending: function(result){ window.location.href = '/pesanan'; },
              onError: function(result){ alert('Pembayaran gagal: ' + JSON.stringify(result)); payBtn.disabled = false; payBtn.textContent = 'Pesan & Bayar'; },
              onClose: function(){ payBtn.disabled = false; payBtn.textContent = 'Pesan & Bayar'; }
            });
            document.body.appendChild(s);
          } else {
            window.snap.pay(token, {
              onSuccess: function(result){ window.location.href = '/pesanan'; },
              onPending: function(result){ window.location.href = '/pesanan'; },
              onError: function(result){ alert('Pembayaran gagal: ' + JSON.stringify(result)); payBtn.disabled = false; payBtn.textContent = 'Pesan & Bayar'; },
              onClose: function(){ payBtn.disabled = false; payBtn.textContent = 'Pesan & Bayar'; }
            });
          }
        }

        openSnap();
      }).catch(err => {
        console.error(err);
        alert('Error: ' + (err.message || err));
        payBtn.disabled = false;
        payBtn.textContent = 'Pesan & Bayar';
      });
  });

})();
</script>
@endpush
