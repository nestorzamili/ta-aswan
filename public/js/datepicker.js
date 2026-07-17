(function () {
  if (typeof flatpickr === 'undefined') return;

  const localeId = {
    weekdays: {
      shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
      longhand: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
    },
    months: {
      shorthand: [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'Mei',
        'Jun',
        'Jul',
        'Agu',
        'Sep',
        'Okt',
        'Nov',
        'Des',
      ],
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

  const defaults = {
    dateFormat: 'd-m-Y',
    allowInput: false,
    disableMobile: true,
    locale: localeId,
    animate: true,
    monthSelectorType: 'static',
    clickOpens: true,
    static: false,
    appendTo: document.body,
    position: 'auto',
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
