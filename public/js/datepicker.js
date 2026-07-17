(function () {
  if (typeof flatpickr === 'undefined') return;

  const localeId = {
    weekdays: {
      shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
      longhand: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
    },
    months: {
      shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
      longhand: [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
      ],
    },
    firstDayOfWeek: 1,
    rangeSeparator: ' – ',
    weekAbbreviation: 'Mgg',
    scrollTitle: 'Scroll untuk menambah',
    toggleTitle: 'Klik untuk ganti',
  };

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /**
   * Best practice: calendar always portaled to <body> (not static in parent).
   * Avoids clipping / “outside” placement inside filter panels, fieldsets, cards.
   */
  const defaults = {
    dateFormat: 'd-m-Y',
    allowInput: false,
    disableMobile: true,
    locale: localeId,
    animate: !reducedMotion,
    monthSelectorType: 'static',
    clickOpens: true,
    static: false,
    appendTo: document.body,
    position: 'auto',
    // Keep calendar above sidebars / sticky chrome
    onReady(_selected, _dateStr, instance) {
      if (instance.calendarContainer) {
        instance.calendarContainer.style.zIndex = '10050';
      }
    },
  };

  document.querySelectorAll('input.input-date').forEach((el) => {
    if (el._flatpickr) return;
    flatpickr(el, {
      ...defaults,
      onChange() {
        if (el.dataset.autosubmit === 'false') return;
        if (el.form && (el.form.method || 'get').toLowerCase() === 'get') {
          clearTimeout(el._fpTimer);
          el._fpTimer = setTimeout(() => el.form.submit(), 150);
        }
      },
    });
  });

  document.querySelectorAll('input.datepicker').forEach((el) => {
    if (el._flatpickr) return;
    flatpickr(el, {
      ...defaults,
      defaultDate: el.value || null,
    });
  });
})();
