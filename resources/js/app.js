import './bootstrap';
import './order';

// Helper to escape HTML when inserting values into the table
function escapeHtml(unsafe) {
	if (unsafe === undefined || unsafe === null) return '';
	return String(unsafe)
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#039;');
}

// Bind a form submit handler that validates, shows spinner, clears inputs, and appends to a table
function bindBarangForm(formSelector, tableSelector, submitBtnSelector) {
	const $form = $(formSelector);
	if ($form.length === 0) return;

	$form.on('submit', function (e) {
		e.preventDefault();
		const form = this;

		// 1. Check validity and report if invalid
		if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
			if (typeof form.reportValidity === 'function') form.reportValidity();
			return;
		}

		// 2. Show spinner on submit button (try to find button inside form first, otherwise globally)
		let $btn = $();
		if (submitBtnSelector) {
			$btn = $form.find(submitBtnSelector);
			if ($btn.length === 0) $btn = $(submitBtnSelector);
		} else {
			$btn = $form.find('button[type="submit"], button');
		}
		if ($btn.length) {
			const original = $btn.html();
			$btn.data('original-text', original);
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
		}

		// 3. Read input values
		const nama = $form.find('input[name="nama"]').val();
		const harga = $form.find('input[name="harga"]').val();

		// 4. Append to table (supports plain table and DataTables)
		const $table = $(tableSelector);
		if ($table.length) {
			// Generate incremental ID for the new item (stored on the table element)
			let nextId = $table.data('next-id') || 1;
			const id = nextId;
			$table.data('next-id', nextId + 1);

			// If this table is a DataTable, use the API to add the row
			if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable($table.get(0))) {
				const dt = $table.DataTable();
				const added = dt.row.add([escapeHtml(id), escapeHtml(nama), escapeHtml(harga)]).draw(false);
				// set data-id attribute on the created row node so our handlers can find it
				try {
					const node = dt.row(added.node()).node ? dt.row(added.node()).node() : dt.row(added).node();
					if (node) $(node).attr('data-id', id);
				} catch (e) {
					// fallback: try to get the last row
					const last = $table.find('tbody tr').last();
					if (last.length) last.attr('data-id', id);
				}
			} else {
				// Ensure tbody exists
				let $tbody = $table.find('tbody');
				if ($tbody.length === 0) {
					$tbody = $('<tbody/>').appendTo($table);
				}
				const row = '<tr data-id="' + id + '">'
					+ '<td>' + escapeHtml(id) + '</td>'
					+ '<td>' + escapeHtml(nama) + '</td>'
					+ '<td>' + escapeHtml(harga) + '</td>'
					+ '</tr>';
				$tbody.append(row);
			}
		}

		// 5. Clear inputs
		$form.find('input[name="nama"]').val('');
		$form.find('input[name="harga"]').val('');

		// Restore button after short delay
		if ($btn.length) {
			setTimeout(function () {
				$btn.prop('disabled', false).html($btn.data('original-text'));
			}, 700);
		}
	});
}

// Auto-bind if a form with id #formBarang exists. Otherwise call bindBarangForm manually.
$(function () {
	if ($('#formBarang').length) {
		bindBarangForm('#formBarang', '#tabelBarang');
	}

	// auto-bind demo pages if present
	if ($('#formBarangPlain').length) {
		bindBarangForm('#formBarangPlain', '#tabelBarangPlain', '#btnTambahPlain');
	}
	if ($('#formBarangDT').length) {
		bindBarangForm('#formBarangDT', '#tabelBarangDT', '#btnTambahDT');
	}
});

// Export for manual usage elsewhere
window.bindBarangForm = bindBarangForm;

// Row interaction + modal handling for #tabelBarang
(function () {
		let currentRow = null;

		function ensureModalExists() {
				if ($('#modalBarang').length) return;
				const modal = `
				<div class="modal fade" id="modalBarang" tabindex="-1" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">Detail Barang</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<div class="mb-3">
									<label class="form-label">ID Barang</label>
									<input type="text" name="modal_id" class="form-control" readonly />
								</div>
								<div class="mb-3">
									<label class="form-label">Nama</label>
									<input type="text" name="modal_nama" class="form-control" required />
								</div>
								<div class="mb-3">
									<label class="form-label">Harga</label>
									<input type="text" name="modal_harga" class="form-control" required />
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-danger" id="btnHapus">Hapus</button>
								<button type="button" class="btn btn-primary" id="btnUbah">Ubah</button>
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
							</div>
						</div>
					</div>
				</div>`;
				$('body').append(modal);
		}

		// Make rows show pointer cursor on hover for any barang table (plain / datatables / default)
		$(document).on('mouseenter', 'table[id^="tabelBarang"] tbody tr', function () {
				$(this).css('cursor', 'pointer');
		});

		// Click row -> open modal with data from columns (ID, Nama, Harga)
		$(document).on('click', 'table[id^="tabelBarang"] tbody tr', function () {
				currentRow = $(this);
				const $tds = currentRow.find('td');
				// assume columns: 0=ID, 1=Nama, 2=Harga
				const id = $tds.eq(0).text().trim();
				const nama = $tds.eq(1).text().trim();
				const harga = $tds.eq(2).text().trim();

				ensureModalExists();
				const $modal = $('#modalBarang');
				$modal.find('input[name="modal_id"]').val(id).prop('readonly', true);
				$modal.find('input[name="modal_nama"]').val(nama);
				$modal.find('input[name="modal_harga"]').val(harga);

				// show bootstrap modal
				if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						const modalObj = new bootstrap.Modal(document.getElementById('modalBarang'));
						modalObj.show();
				} else if ($modal.modal) {
						$modal.modal('show');
				}
		});

		// Hapus button: remove the current row
		$(document).on('click', '#modalBarang #btnHapus', function () {
				if (currentRow && currentRow.length) {
					const $table = currentRow.closest('table');
					if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable($table.get(0))) {
						// remove via DataTables API
						try {
							$table.DataTable().row(currentRow).remove().draw(false);
						} catch (e) {
							currentRow.remove();
						}
					} else {
						currentRow.remove();
					}
				}
				// small spinner effect to indicate action
				const $btn = $(this);
				const orig = $btn.html();
				$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
				setTimeout(function(){
					// hide modal
					if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						const modalEl = document.getElementById('modalBarang');
						const modalObj = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
						modalObj.hide();
					} else {
						$('#modalBarang').modal('hide');
					}
						// restore button
						$btn.prop('disabled', false).html(orig);
					$btn.prop('disabled', false).html(orig);
				}, 200);
		});

		// Ubah button: update the row values
		$(document).on('click', '#modalBarang #btnUbah', function () {
				if (!currentRow || !currentRow.length) return;
				const $modal = $('#modalBarang');
				const id = $modal.find('input[name="modal_id"]').val().trim();
				const nama = $modal.find('input[name="modal_nama"]').val().trim();
				const harga = $modal.find('input[name="modal_harga"]').val().trim();
				// validate modal inputs before updating (check individual required inputs)
				const $inputNama = $modal.find('input[name="modal_nama"]');
				const $inputHarga = $modal.find('input[name="modal_harga"]');
				if ($inputNama.length && typeof $inputNama[0].checkValidity === 'function' && !$inputNama[0].checkValidity()) {
					if (typeof $inputNama[0].reportValidity === 'function') $inputNama[0].reportValidity();
					return;
				}
				if ($inputHarga.length && typeof $inputHarga[0].checkValidity === 'function' && !$inputHarga[0].checkValidity()) {
					if (typeof $inputHarga[0].reportValidity === 'function') $inputHarga[0].reportValidity();
					return;
				}
				const $btn = $(this);
				const orig = $btn.html();
				$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
				const $table = currentRow.closest('table');
				if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable($table.get(0))) {
					try {
						// update via DataTables API
						$table.DataTable().row(currentRow).data([id, nama, harga]).draw(false);
						// ensure data-id present on node
						const node = $table.DataTable().row(currentRow).node();
						if (node) $(node).attr('data-id', id);
					} catch (e) {
						const $tds = currentRow.find('td');
						if ($tds.length >= 3) {
							$tds.eq(0).text(id);
							$tds.eq(1).text(nama);
							$tds.eq(2).text(harga);
						}
						currentRow.attr('data-id', id);
					}
				} else {
					const $tds = currentRow.find('td');
					if ($tds.length >= 3) {
						$tds.eq(0).text(id);
						$tds.eq(1).text(nama);
						$tds.eq(2).text(harga);
					}
					currentRow.attr('data-id', id);
				}

				// hide modal
				if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						const modalEl = document.getElementById('modalBarang');
						const modalObj = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
						modalObj.hide();
				} else {
						$('#modalBarang').modal('hide');
				}
		});
})();
