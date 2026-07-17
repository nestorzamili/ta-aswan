(function () {
  function digitsOnly(v) {
    return String(v ?? '').replace(/\D/g, '');
  }

  function formatId(v) {
    let raw = String(v ?? '').trim();
    if (raw === '') return '';

    if (/^\d+([.,]\d+)?$/.test(raw)) {
      const n = Math.round(Number.parseFloat(raw.replace(',', '.')));
      if (!Number.isFinite(n) || n < 0) return '';
      return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    if (/[.,]\d{1,2}$/.test(raw) && (raw.match(/[.,]/g) || []).length > 1) {
      raw = raw.replace(/[.,]\d{1,2}$/, '');
    }

    const n = digitsOnly(raw);
    if (n === '') return '';
    const normalized = String(Number.parseInt(n, 10));
    if (!Number.isFinite(Number(normalized))) return '';
    return normalized.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function bindMoneyInput(el) {
    if (el.dataset.moneyBound === '1') return;
    el.dataset.moneyBound = '1';
    el.setAttribute('inputmode', 'numeric');
    el.setAttribute('autocomplete', 'off');

    if (el.value) {
      el.value = formatId(el.value);
    }

    el.addEventListener('input', () => {
      const start = el.selectionStart;
      const prevLen = el.value.length;
      const digits = digitsOnly(el.value);
      el.value = digits === '' ? '' : digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      const nextLen = el.value.length;
      const pos = Math.max(0, (start ?? nextLen) + (nextLen - prevLen));
      if (typeof el.setSelectionRange === 'function') {
        el.setSelectionRange(pos, pos);
      }
    });

    el.addEventListener('blur', () => {
      el.value = formatId(el.value);
    });
  }

  function init(root = document) {
    root.querySelectorAll('.input-money').forEach(bindMoneyInput);
  }

  function stripFormMoney(form) {
    form.querySelectorAll('.input-money').forEach((el) => {
      el.value = digitsOnly(el.value);
    });
  }

  function restoreFormMoney(form) {
    form.querySelectorAll('.input-money').forEach((el) => {
      el.value = formatId(el.value);
    });
  }

  function onFormSubmit(form, e) {
    stripFormMoney(form);
    setTimeout(() => {
      if (e.defaultPrevented) {
        restoreFormMoney(form);
      }
    }, 0);
  }

  document.addEventListener('DOMContentLoaded', () => {
    init();
    document.querySelectorAll('form').forEach((form) => {
      form.addEventListener('submit', (e) => onFormSubmit(form, e));
    });
  });

  globalThis.MoneyInput = {
    format: formatId,
    digits: digitsOnly,
    bind: bindMoneyInput,
    init,
    stripForm: stripFormMoney,
  };
})();
