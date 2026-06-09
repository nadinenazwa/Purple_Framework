<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    @auth
    <li class="nav-item nav-profile">
      <a href="#" class="nav-link">
        <div class="nav-profile-image">
          <img src="{{ asset('assets/images/faces/face1.jpg') }}" alt="image">
          <span class="login-status online"></span>
        </div>
        <div class="nav-profile-text d-flex flex-column">
          <span class="font-weight-bold mb-2">{{ Auth::user()->name }}</span>
          <span class="text-secondary text-small">Administrator</span>
        </div>
        <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
      </a>
    </li>
    @endauth

    <li class="nav-item {{ request()->is('dashboard*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('dashboard') }}">
        <span class="menu-title">Dashboard</span>
        <i class="mdi mdi-home menu-icon"></i>
      </a>
    </li>

    <li class="nav-item {{ request()->is('buku*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('buku.index') }}">
        <span class="menu-title">Buku</span>
        <i class="mdi mdi-book-open-page-variant menu-icon"></i>
      </a>
    </li>

    <li class="nav-item {{ request()->is('kategori*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('kategori.index') }}">
        <span class="menu-title">Kategori</span>
        <i class="mdi mdi-format-list-bulleted menu-icon"></i>
      </a>
    </li>

    <li class="nav-item {{ request()->is('barangs*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('barang.index') }}">
        <span class="menu-title">Barang</span>
        <i class="mdi mdi-tag-multiple menu-icon"></i>
      </a>
    </li>

    <li class="nav-item {{ request()->is('barcode*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('barcode.index') }}">
        <span class="menu-title">Barcode Scanner</span>
        <i class="mdi mdi-barcode-scan menu-icon"></i>
      </a>
    </li>

    <li class="nav-item {{ request()->is('toko*') || request()->is('kunjungan*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('toko.index') }}">
        <span class="menu-title">Kunjungan Toko</span>
        <i class="mdi mdi-store-marker menu-icon"></i>
      </a>
    </li>

    <li class="nav-item {{ request()->is('admin-antrian*') || request()->is('guest*') || request()->is('papan*') ? 'active' : '' }}">
      <a class="nav-link" data-bs-toggle="collapse" href="#antrianMenu"
         aria-expanded="{{ request()->is('admin-antrian*') || request()->is('guest*') ? 'true' : 'false' }}"
         aria-controls="antrianMenu">
        <span class="menu-title">Antrian Digital</span>
        <i class="menu-arrow"></i>
        <i class="mdi mdi-ticket-account menu-icon"></i>
      </a>
      <div class="collapse {{ request()->is('admin-antrian*') || request()->is('guest*') ? 'show' : '' }}" id="antrianMenu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item {{ request()->is('guest') ? 'active' : '' }}">
            <a class="nav-link" href="/guest">Daftar Antrian</a>
          </li>
          <li class="nav-item {{ request()->is('admin-antrian') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('antrian.admin') }}">Admin Antrian</a>
          </li>
          <li class="nav-item {{ request()->is('papan') ? 'active' : '' }}">
            <a class="nav-link" href="/papan" target="_blank">Papan Antrian</a>
          </li>
        </ul>
      </div>
    </li>

    <li class="nav-item {{ request()->is('POS*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('POS.menu') }}">
        <span class="menu-title">POS</span>
        <i class="mdi mdi-cash-register menu-icon"></i>
      </a>
    </li>

    <!-- Kantin Online (Customer) -->
    <li class="nav-item {{ request()->is('kantin*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('kantin.index') }}">
        <span class="menu-title">Kantin Online (Customer)</span>
        <i class="mdi mdi-food-apple menu-icon"></i>
      </a>
    </li>

    <!-- My Order - QR Code pesanan saya -->
    <li class="nav-item {{ request()->is('my-order*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('pos.my-order') }}">
        <span class="menu-title">My Order / QR Saya</span>
        <i class="mdi mdi-qrcode-scan menu-icon"></i>
      </a>
    </li>

    <!-- Kelola Kantin (Master links) - new dropdown for admin/master pages -->
    <li class="nav-item {{ request()->is('master/*') || request()->is('pesanan*') ? 'active' : '' }}">
      <a class="nav-link" data-bs-toggle="collapse" href="#masterKantin" aria-expanded="{{ (request()->is('master/*') || request()->is('pesanan*')) ? 'true' : 'false' }}" aria-controls="masterKantin">
        <span class="menu-title">Kelola Kantin</span>
        <i class="menu-arrow"></i>
        <i class="mdi mdi-store menu-icon"></i>
      </a>
      <div class="collapse {{ (request()->is('master/*') || request()->is('pesanan*')) ? 'show' : '' }}" id="masterKantin">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item {{ request()->is('master/vendors*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('master.vendors') }}">Master Vendor</a>
          </li>
          <li class="nav-item {{ request()->is('master/menus*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('master.menus') }}">Master Menu</a>
          </li>
          <li class="nav-item {{ request()->is('pesanan*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('pesanan.index') }}">Data Pesanan</a>
          </li>
        </ul>
      </div>
    </li>

    {{-- Vendor: Scan QR Pesanan --}}
    <li class="nav-item {{ request()->is('vendor/scan*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('vendor.scan') }}">
        <span class="menu-title">Vendor — Scan QR</span>
        <i class="mdi mdi-qrcode-scan menu-icon"></i>
      </a>
    </li>

    <li class="nav-item {{ request()->is('wilayah*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('wilayah.index') }}">
        <span class="menu-title">Wilayah</span>
        <i class="mdi mdi-map-marker menu-icon"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#barangDemoMenu" aria-expanded="{{ request()->is('barang/demo*') ? 'true' : 'false' }}" aria-controls="barangDemoMenu">
        <span class="menu-title">Demo Barang</span>
        <i class="menu-arrow"></i>
        <i class="mdi mdi-tag-multiple menu-icon"></i>
      </a>
      <div class="collapse {{ request()->is('barang/demo*') ? 'show' : '' }}" id="barangDemoMenu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item {{ request()->is('barang/demo') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('barang.demo_plain') }}">Demo (Plain)</a>
          </li>
          <li class="nav-item {{ request()->is('barang/demo-dt') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('barang.demo_datatables') }}">Demo (DataTables)</a>
          </li>
          <li class="nav-item {{ request()->is('kota*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('kota.index') }}">Kota</a>
          </li>
        </ul>
      </div>
    </li>

    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#pdfMenu" aria-expanded="{{ request()->is('pdf*') ? 'true' : 'false' }}" aria-controls="pdfMenu">
        <span class="menu-title">Generate PDF</span>
        <i class="menu-arrow"></i>
        <i class="mdi mdi-file-pdf menu-icon"></i>
      </a>
      <div class="collapse {{ request()->is('pdf*') ? 'show' : '' }}" id="pdfMenu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item {{ request()->is('pdf/certificate') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('pdf.certificate') }}" target="_blank">Sertifikat (Landscape)</a>
          </li>
          <li class="nav-item {{ request()->is('pdf/announcement') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('pdf.announcement') }}" target="_blank">Pengumuman (Portrait)</a>
          </li>
        </ul>
      </div>
    </li>

    <li class="nav-item {{ request()->is('customer*') ? 'active' : '' }}">
      <a class="nav-link" data-bs-toggle="collapse" href="#customerMenu" aria-expanded="{{ request()->is('customer*') ? 'true' : 'false' }}" aria-controls="customerMenu">
        <span class="menu-title">Customer</span>
        <i class="menu-arrow"></i>
        <i class="mdi mdi-account-multiple menu-icon"></i>
      </a>
      <div class="collapse {{ request()->is('customer*') ? 'show' : '' }}" id="customerMenu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item {{ request()->is('customer') ? 'active' : '' }}"><a class="nav-link" href="{{ route('customer.index') }}">Data Customer</a></li>
          <li class="nav-item {{ request()->is('customer/create-blob') ? 'active' : '' }}"><a class="nav-link" href="{{ route('customer.create.blob') }}">Tambah Customer 1 (blob)</a></li>
          <li class="nav-item {{ request()->is('customer/create-file') ? 'active' : '' }}"><a class="nav-link" href="{{ route('customer.create.file') }}">Tambah Customer 2 (file)</a></li>
        </ul>
      </div>
    </li>

  </ul>
</nav>