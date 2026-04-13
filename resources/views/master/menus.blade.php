@extends('layouts.master')

@section('content')
<div class="container py-4">
  <h3 class="mb-4">Master Data Menu</h3>
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="row">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">Tambah Menu</div>
        <div class="card-body">
          <form method="POST" action="{{ url('master/menus') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label class="form-label">Pilih Vendor</label>
              <select name="vendor_id" class="form-select" required>
                <option value="">-- Pilih Vendor --</option>
                @foreach($vendors as $v)
                  <option value="{{ $v->id }}">{{ $v->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Nama Menu</label>
              <input name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Harga</label>
              <input name="price" type="number" min="0" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Foto (opsional)</label>
              <input name="photo" type="file" class="form-control">
            </div>
            <div class="d-grid">
              <button class="btn btn-primary">Tambah Menu</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card">
        <div class="card-header">Daftar Menu</div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="table-light">
                <tr><th>Nama Menu</th><th>Vendor</th><th class="text-end">Harga</th><th>Foto</th><th>Aksi</th></tr>
              </thead>
              <tbody id="menusTbody">
                @foreach($menus as $m)
                  <tr data-id="{{ $m->id ?? $m->idmenu ?? '' }}">
                    <td>{{ $m->name ?? $m->nama_menu ?? '-' }}</td>
                    <td>{{ $m->vendor_name ?? $m->nama_vendor ?? $m->vendor ?? '-' }}</td>
                    <td class="text-end">Rp {{ number_format($m->price ?? $m->harga ?? 0,0,',','.') }}</td>
                    <td>
                      @if(!empty($m->image_path) || !empty($m->path_gambar))
                        <img src="{{ asset('storage/' . ($m->image_path ?? $m->path_gambar)) }}" style="height:40px">
                      @else
                        -
                      @endif
                    </td>
                    <td>
                      <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="actionsMenu{{ $loop->index }}" data-bs-toggle="dropdown" aria-expanded="false">Aksi</button>
                        <ul class="dropdown-menu" aria-labelledby="actionsMenu{{ $loop->index }}">
                          <li><a class="dropdown-item" href="{{ route('master.menus.edit', $m->id ?? $m->idmenu ?? $m->menu_id ?? $m->id_menu ?? '') }}">Edit</a></li>
                          <li><a class="dropdown-item text-danger" href="#" onclick="deleteMenu('{{ $m->id ?? $m->idmenu ?? $m->menu_id ?? $m->id_menu ?? '' }}'); return false;">Hapus</a></li>
                        </ul>
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
  </div>
</div>
@endsection

@push('js-page')
<script>
  (function(){
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const vendorSelect = document.querySelector('select[name="vendor_id"]');
    const tbody = document.getElementById('menusTbody');
    function formatRp(n){ return 'Rp ' + Number(n).toLocaleString('id-ID'); }

    async function loadMenus(vendorId){
      const url = '/api/master/menus' + (vendorId ? ('?vendor_id=' + encodeURIComponent(vendorId)) : '');
      try{
        const res = await fetch(url, { headers: { 'Accept':'application/json' } });
        const data = await res.json();
        tbody.innerHTML = '';
        (data || []).forEach((m, idx) => {
          const id = m.id ?? m.idmenu ?? '';
          const name = m.name ?? m.nama_menu ?? '-';
          const vendor = m.vendor_name ?? m.nama_vendor ?? m.vendor ?? '-';
          const price = m.price ?? m.harga ?? 0;
          const img = (m.image_path || m.path_gambar) ? '<img src="' + ( '/storage/' + (m.image_path || m.path_gambar) ) + '" style="height:40px">' : '-';
          const tr = document.createElement('tr');
          tr.dataset.id = id;
          tr.innerHTML = `
            <td>${name}</td>
            <td>${vendor}</td>
            <td class="text-end">${formatRp(price)}</td>
            <td>${img}</td>
            <td>
              <div class="dropdown">
                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="actionsMenuAjax${idx}" data-bs-toggle="dropdown" aria-expanded="false">Aksi</button>
                <ul class="dropdown-menu" aria-labelledby="actionsMenuAjax${idx}">
                  <li><a class="dropdown-item" href="/master/menus/${id}/edit">Edit</a></li>
                  <li><a class="dropdown-item text-danger" href="#" onclick="deleteMenu(${id}); return false;">Hapus</a></li>
                </ul>
              </div>
            </td>
          `;
          tbody.appendChild(tr);
        });
      }catch(err){ console.error(err); }
    }

    vendorSelect.addEventListener('change', function(){ loadMenus(this.value); });
    // initial load
    loadMenus(vendorSelect.value || '');

    // Delete handler used by both server-rendered and AJAX rows
    window.deleteMenu = async function(id){
      if(!id) return alert('ID menu tidak tersedia');
      if(!confirm('Hapus menu ini? Aksi tidak bisa dibatalkan.')) return;
      try{
        const res = await fetch('/master/menus/' + encodeURIComponent(id), {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          }
        });
        if (res.ok) {
          // remove row from table if present
          const tr = document.querySelector('tr[data-id="' + id + '"]');
          if(tr) tr.remove();
          else location.reload();
        } else {
          const body = await res.json().catch(()=>null);
          alert(body?.message || 'Gagal menghapus menu');
        }
      }catch(e){ console.error(e); alert('Gagal menghapus menu'); }
    }
  })();
</script>
@endpush
