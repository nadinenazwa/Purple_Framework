// order.js
// Listens to a vendor select and loads the vendor's menu via Axios

document.addEventListener('DOMContentLoaded', function () {
    const vendorSelect = document.getElementById('vendorSelect');
    const menuContainer = document.getElementById('menuList');

    if (!vendorSelect || !menuContainer) return;

    function renderMenus(items) {
        menuContainer.innerHTML = '';
        if (!items || items.length === 0) {
            menuContainer.innerHTML = '<div class="alert alert-secondary">Tidak ada menu untuk vendor ini.</div>';
            return;
        }
        const list = document.createElement('div');
        list.className = 'row g-2';
        items.forEach(it => {
            const col = document.createElement('div');
            col.className = 'col-12 col-md-6';
            col.innerHTML = `
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">${escapeHtml(it.nama || it.name || '')}</h5>
                        <p class="card-text">${escapeHtml(it.keterangan || it.description || '')}</p>
                        <p class="card-text"><strong>Harga:</strong> ${escapeHtml(it.harga || it.price || '-')}</p>
                        <button class="btn btn-sm btn-primary btn-add" data-id="${escapeHtml(it.id || '')}" data-name="${escapeHtml(it.nama || it.name || '')}" data-price="${escapeHtml(it.harga || it.price || '')}">Tambah</button>
                    </div>
                </div>
            `;
            list.appendChild(col);
        });
        menuContainer.appendChild(list);
    }

    function escapeHtml(unsafe) {
        if (unsafe === undefined || unsafe === null) return '';
        return String(unsafe)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function loadMenus(vendorId) {
        // show loading state
        menuContainer.innerHTML = '<div class="text-center py-4">Memuat menu&hellip;</div>';
        try {
            const resp = await window.axios.get('/api/menus', { params: { vendor_id: vendorId } });
            renderMenus(resp.data || []);
        } catch (err) {
            console.error(err);
            menuContainer.innerHTML = '<div class="alert alert-danger">Gagal memuat menu. Coba lagi.</div>';
        }
    }

    vendorSelect.addEventListener('change', function () {
        const val = this.value;
        if (!val) {
            menuContainer.innerHTML = '';
            return;
        }
        loadMenus(val);
    });

    // Optional: load initial selection if present
    if (vendorSelect.value) {
        loadMenus(vendorSelect.value);
    }

    // Delegate click for "Tambah" buttons (example: add to cart)
    menuContainer.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-add');
        if (!btn) return;
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        const price = btn.getAttribute('data-price');
        // simple event: dispatch a custom event so host page can handle adding to cart
        const ev = new CustomEvent('menu:add', { detail: { id, name, price } });
        document.dispatchEvent(ev);
    });
});
