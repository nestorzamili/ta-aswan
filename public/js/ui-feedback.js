(function () {
  'use strict';

  function escHtml(str) {
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;');
  }

  function toastRoot() {
    let root = document.getElementById('toast-root');
    if (!root) {
      root = document.createElement('div');
      root.id = 'toast-root';
      root.setAttribute('aria-live', 'polite');
      root.setAttribute('aria-atomic', 'true');
      document.body.appendChild(root);
    }
    return root;
  }

  const ICONS = {
    success: 'bi-check-circle-fill',
    error: 'bi-exclamation-triangle-fill',
    warning: 'bi-exclamation-circle-fill',
    info: 'bi-info-circle-fill',
  };

  function showToast(type, message) {
    if (!message) return;

    const root = toastRoot();
    const cls = ICONS[type] ? type : 'info';
    const icon = ICONS[cls] || ICONS.info;
    const delay = cls === 'error' ? 7000 : 4500;

    const el = document.createElement('div');
    el.className = 'toast toast-' + cls + ' align-items-center border-0';
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', 'assertive');
    el.setAttribute('aria-atomic', 'true');
    el.innerHTML =
      '<div class="toast-body">' +
      '<i class="bi ' +
      icon +
      ' toast-icon" aria-hidden="true"></i>' +
      '<span>' +
      escHtml(message) +
      '</span>' +
      '<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Tutup"></button>' +
      '</div>';

    root.appendChild(el);

    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
      const t = bootstrap.Toast.getOrCreateInstance(el, { autohide: true, delay: delay });
      el.addEventListener('hidden.bs.toast', function () {
        el.remove();
      });
      t.show();
    } else {
      el.classList.add('show');
      setTimeout(function () {
        el.remove();
      }, delay);
    }
  }

  function readFlashes() {
    const body = document.body;
    if (!body) return;
    const map = [
      ['flashSuccess', 'success'],
      ['flashError', 'error'],
      ['flashWarning', 'warning'],
      ['flashInfo', 'info'],
    ];
    map.forEach(function (pair) {
      const key = pair[0];
      const type = pair[1];
      const val = body.dataset[key];
      if (val) {
        showToast(type, val);
        delete body.dataset[key];
      }
    });
  }

  function markLoading(btn) {
    if (!btn || btn.classList.contains('is-loading')) return;
    btn.classList.add('is-loading');
    btn.disabled = true;
    btn.setAttribute('aria-busy', 'true');

    if (!btn.querySelector('.btn-loading-spinner')) {
      const spin = document.createElement('span');
      spin.className = 'spinner-border btn-loading-spinner';
      spin.setAttribute('role', 'status');
      spin.setAttribute('aria-hidden', 'true');
      btn.appendChild(spin);
    }
  }

  function clearLoading(btn) {
    if (!btn) return;
    btn.classList.remove('is-loading');
    btn.disabled = false;
    btn.removeAttribute('aria-busy');
    const spin = btn.querySelector('.btn-loading-spinner');
    if (spin) spin.remove();
  }

  function bindSubmitLoading() {
    document.addEventListener(
      'submit',
      function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.noLoading !== undefined) return;
        if (form.dataset.confirm !== undefined && form.dataset.confirmOk !== '1') return;

        let btn = e.submitter;
        if (btn?.type !== 'submit') {
          btn = form.querySelector('button[type="submit"], input[type="submit"]');
        }
        if (!btn) return;
        markLoading(btn);

        // target=_blank (e.g. generate PDF) never unloads this page, so the spinner
        // would stick forever unless we release it after a short feedback delay.
        const opensNewContext =
          (form.getAttribute('target') || '').toLowerCase() === '_blank' ||
          form.hasAttribute('download') ||
          form.dataset.releaseLoading !== undefined;

        setTimeout(
          function () {
            if (e.defaultPrevented || opensNewContext) clearLoading(btn);
          },
          opensNewContext ? 700 : 0,
        );
      },
      true,
    );
  }

  function bindConfirm() {
    const modalEl = document.getElementById('confirmModal');
    if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const msgEl = document.getElementById('confirmModalMessage');
    const okBtn = document.getElementById('confirmModalOk');
    let pendingForm = null;

    document.addEventListener(
      'submit',
      function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        const message = form.dataset.confirm;
        if (!message) return;
        if (form.dataset.confirmOk === '1') {
          delete form.dataset.confirmOk;
          return;
        }
        e.preventDefault();
        e.stopPropagation();
        pendingForm = form;
        if (msgEl) msgEl.textContent = message;
        modal.show();
      },
      true,
    );

    if (okBtn) {
      okBtn.addEventListener('click', function () {
        if (!pendingForm) {
          modal.hide();
          return;
        }
        const form = pendingForm;
        pendingForm = null;
        form.dataset.confirmOk = '1';
        modal.hide();
        if (typeof form.requestSubmit === 'function') {
          form.requestSubmit();
        } else {
          form.submit();
        }
      });
    }

    modalEl.addEventListener('hidden.bs.modal', function () {
      pendingForm = null;
    });
  }

  function bindRowLinks() {
    document.addEventListener('click', function (e) {
      const tr = e.target.closest('tr.row-link[data-href]');
      if (!tr) return;
      if (e.target.closest('a, button, form, input, select, textarea, label, .table-actions, .dropdown')) {
        return;
      }
      const href = tr.getAttribute('data-href');
      if (href) {
        window.location.href = href;
      }
    });
  }

  function init() {
    readFlashes();
    bindSubmitLoading();
    bindConfirm();
    bindRowLinks();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  globalThis.AppFeedback = { showToast: showToast };
})();
