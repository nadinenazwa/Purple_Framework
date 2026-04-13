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
                    <label class="form-label">Kode Barang</label>
                    <input id="kodeInput" class="form-control" placeholder="Masukkan kode barang lalu tekan Enter">
                    <div class="form-text">Tekan Enter untuk mencari barang (Ajax/Axios pilihan di bawah).</div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Nama Barang</label>
                    <input id="namaBarang" class="form-control" readonly>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Harga Barang</label>
                    <input id="hargaBarang" class="form-control" readonly>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Jumlah</label>
                    <input id="qtyInput" type="number" min="1" value="1" class="form-control">
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea id="noteInput" class="form-control" rows="2"></textarea>
                  </div>

                  <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="useAxios">
                    <label class="form-check-label" for="useAxios">Gunakan Axios (jika tidak dicentang menggunakan Fetch/Ajax)</label>
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
  const kodeInput = document.getElementById('kodeInput');
  const namaBarang = document.getElementById('namaBarang');
  const hargaBarang = document.getElementById('hargaBarang');
  const qtyInput = document.getElementById('qtyInput');
  const noteInput = document.getElementById('noteInput');
  const addBtn = document.getElementById('addBtn');
  const useAxios = document.getElementById('useAxios');
  const cartTbody = document.querySelector('#cartTable tbody');
  const totalAmount = document.getElementById('totalAmount');
  const payBtn = document.getElementById('payBtn');

  let cart = [];

  function formatRp(v){
    return 'Rp ' + Number(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
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
      tr.dataset.kode = item.kode || '';
      tr.innerHTML = `
        <td>${item.name}</td>
        <td class="text-end">${formatRp(item.price)}</td>
        <td class="text-center"><input class="form-control form-control-sm qty-cell text-center" data-idx="${idx}" type="number" min="1" value="${item.jumlah}"></td>
        <td>${item.catatan || ''}</td>
        <td class="text-end subtotal">${formatRp(item.price * item.jumlah)}</td>
        <td><button data-idx="${idx}" class="btn btn-sm btn-danger btn-remove">Hapus</button></td>
      `;
      cartTbody.appendChild(tr);
    });
    updateTotal();
  }

  // remove handler & qty change handler (event delegation)
  cartTbody.addEventListener('input', function(e){
    if (e.target && e.target.matches('.qty-cell')){
      const idx = Number(e.target.dataset.idx);
      const val = Math.max(1, parseInt(e.target.value||'1'));
      cart[idx].jumlah = val;
      // update subtotal cell
      const row = e.target.closest('tr');
      const subtotalCell = row.querySelector('.subtotal');
      subtotalCell.textContent = formatRp(cart[idx].price * cart[idx].jumlah);
      updateTotal();
      return;
    }
  });

  cartTbody.addEventListener('click', function(e){
    if (e.target && e.target.matches('.btn-remove')){
      const idx = Number(e.target.dataset.idx);
      cart.splice(idx,1);
      renderCart();
    }
  });

  // Lookup by kode when pressing Enter
  kodeInput.addEventListener('keydown', function(e){
    if (e.key === 'Enter'){
      const kode = kodeInput.value.trim();
      if (!kode) return;
      // choose fetch or axios
      if (useAxios.checked && window.axios) {
        axios.get('/api/pos/barang/' + encodeURIComponent(kode)).then(r => {
          // axios returns { data: <responseBody> } — normalize wrapper
          const resp = r.data ?? r;
          const payload = resp.data ?? resp;
          onItemFound(payload, kode);
        }).catch(err => { onItemNotFound(); });
      } else {
        fetch('/api/pos/barang/' + encodeURIComponent(kode)).then(r => {
          if (!r.ok) throw new Error('not found');
          return r.json();
        }).then(it => {
          const payload = it.data ?? it;
          onItemFound(payload, kode);
        }).catch(err => onItemNotFound());
      }
    }
  });

  function onItemFound(it, kode){
    // Expecting object with name and price (or nama/harga)
    const name = it.name ?? it.nama ?? it.nama_barang ?? '';
    const price = Number(it.price ?? it.harga ?? it.harga_jual ?? it.sell_price ?? 0);
    namaBarang.value = name;
    hargaBarang.value = price > 0 ? formatRp(price) : '';
    namaBarang.dataset.kode = kode;
    namaBarang.dataset.priceRaw = price;
    // default quantity 1
    qtyInput.value = 1;
    // enable add button only when jumlah > 0
    addBtn.disabled = !(Number(qtyInput.value) > 0 && name);
  }

  function onItemNotFound(){
    namaBarang.value = '';
    hargaBarang.value = '';
    delete namaBarang.dataset.kode;
    delete namaBarang.dataset.priceRaw;
    addBtn.disabled = true;
  }

  // enable/disable add button when qty changes
  qtyInput.addEventListener('input', function(){
    const jumlah = Math.max(0, parseInt(this.value || '0'));
    addBtn.disabled = !(jumlah > 0 && namaBarang.value);
  });

  addBtn.addEventListener('click', function(){
    const kode = namaBarang.dataset.kode || kodeInput.value.trim();
    if (!kode || !namaBarang.value) return;
    const price = Number(namaBarang.dataset.priceRaw || 0);
    const jumlah = Math.max(1, parseInt(qtyInput.value || '1'));
    const catatan = noteInput.value || '';

    // check duplicate by kode; update jumlah if exists
    const existing = cart.find(c => (c.kode || '') === kode);
    if (existing) {
      existing.jumlah = existing.jumlah + jumlah;
      existing.catatan = catatan || existing.catatan;
    } else {
      cart.push({ kode: kode, id: kode, name: namaBarang.value, price: price, jumlah: jumlah, catatan: catatan });
    }

    // reset inputs
    kodeInput.value = '';
    namaBarang.value = '';
    hargaBarang.value = '';
    qtyInput.value = 1;
    noteInput.value = '';
    addBtn.disabled = true;
    renderCart();
  });

  // POST order (supports fetch or axios) and show SweetAlert2 on success
  payBtn.addEventListener('click', function(){
    if (cart.length === 0) return;
    const items = cart.map(it => ({
      id_barang: it.id_barang ?? it.id ?? it.kode,
      jumlah: it.jumlah,
      subtotal: it.price * it.jumlah,
      catatan: it.catatan || ''
    }));
    const total = items.reduce((s,i)=> s + i.subtotal, 0);

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    payBtn.disabled = true;
    payBtn.textContent = 'Memproses...';

    const payload = { items: items, total: total };

    function handleSuccess(resp){
      const data = resp.data ?? resp;
      if (!data.success) throw new Error(data.message || 'Gagal menyimpan pesanan');
      // success: show Swal2, clear cart
      Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Transaksi berhasil disimpan' });
      cart = [];
      renderCart();
      payBtn.disabled = true;
      payBtn.textContent = 'Pesan & Bayar';
    }

    function handleError(err){
      console.error(err);
      Swal.fire({ icon: 'error', title: 'Error', text: err.message || err });
      payBtn.disabled = false;
      payBtn.textContent = 'Pesan & Bayar';
    }

    if (useAxios.checked && window.axios) {
      axios.post('/api/pos/penjualan', payload, { headers: { 'X-CSRF-TOKEN': csrf } }).then(handleSuccess).catch(handleError);
    } else {
      fetch('/api/pos/penjualan', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(payload) })
        .then(r => r.json()).then(handleSuccess).catch(handleError);
    }
  });

})();
</script>
@endpush
