(function () {
  'use strict';

  const forms = document.querySelectorAll('.needs-validation');

  Array.prototype.slice.call(forms).forEach(function (form) {
    form.addEventListener(
      'submit',
      function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();

          if (globalThis.AppFeedback && AppFeedback.showToast) {
            AppFeedback.showToast('error', 'Mohon periksa kembali form Anda.');
          }
        }

        form.classList.add('was-validated');
      },
      false,
    );
  });
})();
