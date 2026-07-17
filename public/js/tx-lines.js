(function () {
  function moneyDigits(v) {
    return globalThis.MoneyInput ? MoneyInput.digits(v) : String(v ?? '').replace(/\D/g, '');
  }
  function moneyFormat(v) {
    return globalThis.MoneyInput ? MoneyInput.format(v) : moneyDigits(v);
  }
  function notifyError(msg) {
    if (globalThis.AppFeedback && typeof AppFeedback.showToast === 'function') {
      AppFeedback.showToast('error', msg);
    }
  }

  function escapeHtml(unsafe) {
    return String(unsafe || '').replace(/[&<"'>]/g, (m) => {
      switch (m) {
        case '&':
          return '&amp;';
        case '<':
          return '&lt;';
        case '>':
          return '&gt;';
        case '"':
          return '&quot;';
        case "'":
          return '&#039;';
        default:
          return m;
      }
    });
  }

  function validateRow(tr, { hargaEditable, showStok, moneyDigits }) {
    const barangSel = tr.querySelector('[name="id_barang[]"]');
    const qty = tr.querySelector('[name="quantity[]"]');
    if (!barangSel?.value) {
      notifyError('Pilih barang pada setiap baris.');
      barangSel?.focus();
      return false;
    }
    if (!qty?.value || Number(qty.value) < 1) {
      notifyError('Qty harus minimal 1.');
      qty?.focus();
      return false;
    }
    if (hargaEditable) {
      const harga = tr.querySelector('[name="harga_satuan[]"]');
      if (harga && (moneyDigits(harga.value) === '' || Number(moneyDigits(harga.value)) < 0)) {
        notifyError('Harga wajib diisi dan tidak boleh negatif.');
        harga.focus();
        return false;
      }
    }
    if (showStok) {
      const stok = Number(barangSel.selectedOptions[0]?.dataset?.stok ?? Number.NaN);
      if (Number.isFinite(stok) && Number(qty.value) > stok) {
        notifyError('Qty melebihi stok tersedia: ' + barangSel.selectedOptions[0].textContent.trim());
        qty.focus();
        return false;
      }
    }
    return true;
  }

  function init(opts) {
    const barang = opts.barang || [];
    const existing = opts.existing || [];
    const hargaEditable = opts.hargaEditable !== false;
    const showStok = !!opts.showStok;
    const tbody = document.querySelector('#tblItems tbody');
    const form = typeof opts.form === 'string' ? document.getElementById(opts.form) : opts.form;
    if (!tbody || !form) return;

    function optionsFor(selectedId) {
      return barang
        .map((i) => {
          const safeNama = escapeHtml(i.nama);
          const label = showStok ? `${safeNama} (stok ${i.stok})` : safeNama;
          const sel = String(i.id) === String(selectedId) ? 'selected' : '';
          return `<option value="${i.id}" data-harga="${i.harga}" data-stok="${i.stok ?? ''}" ${sel}>${label}</option>`;
        })
        .join('');
    }

    function recalcTotal() {
      let total = 0;
      tbody.querySelectorAll('tr').forEach((tr) => {
        const q = Number(tr.querySelector('[name="quantity[]"]')?.value || 0);
        const h = Number(moneyDigits(tr.querySelector('.harga')?.value || '') || 0);
        const sub = q * h;
        total += sub;
        const cell = tr.querySelector('.subtotal');
        if (cell) cell.textContent = moneyFormat(sub) || '0';
      });
      const el = document.getElementById('grandTotal');
      if (el) el.textContent = moneyFormat(total) || '0';
    }

    function syncHarga(sel) {
      const opt = sel.selectedOptions[0];
      const el = sel.closest('tr')?.querySelector('.harga');
      if (!opt || !el) return;
      el.value = moneyFormat(opt.dataset.harga || 0);
      recalcTotal();
    }

    function addRow(pre) {
      const id = pre?.id_barang || '';
      const qty = pre?.quantity || 1;
      const hargaName = hargaEditable ? 'name="harga_satuan[]"' : '';
      const hargaRo = hargaEditable ? 'required' : 'readonly';
      const hargaLabel = hargaEditable ? 'Harga' : 'Harga';
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td data-label="Barang">
          <div class="line-field-label">Barang</div>
          <select name="id_barang[]" class="form-select barang" aria-label="Barang">
            <option value="">Pilih barang…</option>
            ${optionsFor(id)}
          </select>
        </td>
        <td data-label="Qty">
          <div class="line-field-label">Qty</div>
          <input type="number" name="quantity[]" class="form-control text-end" min="1" value="${qty}" required aria-label="Qty" inputmode="numeric">
        </td>
        <td data-label="${hargaLabel}">
          <div class="line-field-label">${hargaLabel}</div>
          <div class="input-group field-money">
            <span class="input-group-text">Rp</span>
            <input type="text" ${hargaName} class="form-control harga input-money text-end" inputmode="numeric" autocomplete="off" ${hargaRo} aria-label="Harga" placeholder="0">
          </div>
        </td>
        <td class="text-end" data-label="Subtotal">
          <div class="line-field-label">Subtotal</div>
          <span class="subtotal">0</span>
        </td>
        <td class="text-center">
          <button type="button" class="btn btn-outline-danger btn-rm" aria-label="Hapus baris"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </td>`;
      tbody.appendChild(tr);

      tr.querySelector('.barang').addEventListener('change', (e) => syncHarga(e.target));
      tr.querySelectorAll('[name="quantity[]"], .harga').forEach((el) => {
        el.addEventListener('input', recalcTotal);
        el.addEventListener('change', recalcTotal);
      });
      tr.querySelector('.btn-rm').addEventListener('click', () => {
        tr.remove();
        if (!tbody.querySelectorAll('tr').length) addRow();
        recalcTotal();
      });

      const hargaEl = tr.querySelector('.harga');
      if (globalThis.MoneyInput && hargaEl) MoneyInput.bind(hargaEl);
      if (pre?.harga_satuan) {
        hargaEl.value = moneyFormat(pre.harga_satuan);
      } else if (id) {
        syncHarga(tr.querySelector('.barang'));
      } else {
        recalcTotal();
      }
    }

    document.getElementById('btnAddRow')?.addEventListener('click', () => addRow());
    if (existing.length) existing.forEach(addRow);
    else addRow();

    form.addEventListener('submit', (e) => {
      if (typeof opts.validate === 'function' && !opts.validate(e, { moneyDigits, tbody })) {
        return;
      }
      const rows = tbody.querySelectorAll('tr');
      if (!rows.length) {
        e.preventDefault();
        notifyError('Minimal satu baris item detail.');
        return;
      }
      for (const tr of rows) {
        if (!validateRow(tr, { hargaEditable, showStok, moneyDigits })) {
          e.preventDefault();
          return;
        }
      }
    });
  }

  globalThis.TxLines = { init };
})();
