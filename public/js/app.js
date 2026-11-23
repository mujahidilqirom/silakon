document.addEventListener('DOMContentLoaded', function () {
  // Email mahasiswa cukup diisi bagian depannya. Domain institusi ditambahkan
  // sebelum validasi dan pengiriman form agar data yang tersimpan tetap lengkap.
  document.querySelectorAll('input[data-email-domain]').forEach(function (input) {
    var domain = input.dataset.emailDomain || '';
    var normalize = function () {
      var value = (input.value || '').trim();
      if (domain && value.toLowerCase().endsWith(domain.toLowerCase())) {
        input.value = value.slice(0, -domain.length);
      }
    };
    input.addEventListener('blur', normalize);
    input.closest('form')?.addEventListener('submit', function () {
      var value = (input.value || '').trim();
      if (value && !value.includes('@')) input.value = value + domain;
    }, true);
  });

  // Mobile navbar toggle
  var toggle = document.querySelector('.navbar-toggle');
  var navbar = document.querySelector('.navbar');
  var navLinks = document.querySelector('.navbar-links');
  var overlay = document.querySelector('[data-navbar-overlay]');

  function setNavbarOpen(isOpen) {
    if (!navbar || !toggle) return;
    navbar.classList.toggle('open', isOpen);
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    toggle.setAttribute('aria-label', isOpen ? 'Tutup menu' : 'Buka menu');
    document.body.classList.toggle('no-scroll', isOpen);
    if (overlay) overlay.hidden = !isOpen;
  }

  if (toggle && navbar) {
    toggle.addEventListener('click', function () {
      setNavbarOpen(!navbar.classList.contains('open'));
    });
  }

  if (overlay) {
    overlay.addEventListener('click', function () {
      setNavbarOpen(false);
    });
  }

  if (navLinks) {
    navLinks.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        setNavbarOpen(false);
      });
    });
  }

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && navbar && navbar.classList.contains('open')) {
      setNavbarOpen(false);
      toggle && toggle.focus();
    }
  });

  // Close the mobile menu if the viewport is resized back up to desktop width
  window.addEventListener('resize', function () {
    if (window.innerWidth > 860 && navbar && navbar.classList.contains('open')) {
      setNavbarOpen(false);
    }
  });

  // FAQ accordion
  var faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(function (item) {
    var question = item.querySelector('.faq-question');
    var answer = item.querySelector('.faq-answer');

    question.addEventListener('click', function () {
      var isOpen = item.classList.contains('open');

      faqItems.forEach(function (other) {
        other.classList.remove('open');
        other.querySelector('.faq-answer').style.maxHeight = null;
      });

      if (!isOpen) {
        item.classList.add('open');
        answer.style.maxHeight = answer.scrollHeight + 'px';
      }
    });
  });
});
/* =========================================================
   SILAKON LPPP UNNES — Ajukan Konseling
   Frontend only: multi-step interaction + basic validation.
   ========================================================= */
(function () {
  'use strict';

  const form = document.querySelector('[data-konseling-form]');
  if (!form) return;

  const panels = Array.from(form.querySelectorAll('[data-step-panel]'));
  const steps = Array.from(document.querySelectorAll('[data-step]'));
  const nextButton = form.querySelector('[data-next]');
  const prevButton = form.querySelector('[data-prev]');
  const submitButton = form.querySelector('[data-submit]');
  const successMessage = form.querySelector('[data-success]');
  const typeCards = Array.from(form.querySelectorAll('[data-konseli-type]'));
  const conditionalGroups = Array.from(form.querySelectorAll('[data-konseli-fields]'));
  const fileInput = form.querySelector('[data-upload]');
  const fileName = form.querySelector('[data-file-name]');

  let currentStep = 1;

  function showStep(step, shouldScroll = true) {
    currentStep = step;

    panels.forEach(panel => {
      panel.classList.toggle('is-active', Number(panel.dataset.stepPanel) === step);
    });

    steps.forEach(item => {
      const number = Number(item.dataset.step);
      item.classList.toggle('is-active', number === step);
      item.classList.toggle('is-complete', number < step);
    });

    if (prevButton) prevButton.hidden = step === 1;
    if (nextButton) nextButton.hidden = step === panels.length;
    if (submitButton) submitButton.hidden = step !== panels.length;

    const activePanel = form.querySelector(`[data-step-panel="${step}"]`);
    if (shouldScroll && activePanel) {
      activePanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function selectedType() {
    const checked = form.querySelector('input[name="jenis_konseli"]:checked');
    return checked ? checked.value : '';
  }

  function updateTypeFields() {
    const type = selectedType();
    conditionalGroups.forEach(group => {
      const visible = group.dataset.konseliFields === type;
      group.hidden = !visible;
      group.querySelectorAll('input, select, textarea').forEach(input => {
        input.disabled = !visible;
      });
    });
  }

  function clearErrors(scope) {
    scope.querySelectorAll('.konseling-field.has-error').forEach(field => field.classList.remove('has-error'));
  }

  function markError(input) {
    const field = input.closest('.konseling-field');
    if (field) field.classList.add('has-error');
  }

  function validatePanel(panel) {
    clearErrors(panel);
    let valid = true;

    const requiredInputs = Array.from(panel.querySelectorAll('input[required], select[required], textarea[required]'))
      .filter(input => {
        if (input.disabled) return false;
        // Radio sering disembunyikan lewat CSS, jadi cukup pastikan tidak berada di dalam elemen [hidden].
        if (input.type === 'radio') return !input.closest('[hidden]');
        return input.offsetParent !== null;
      });

    requiredInputs.forEach(input => {
      if (input.type === 'radio') {
        // Radio: valid jika salah satu radio dalam grup (name yang sama) terpilih.
        const groupChecked = Array.from(form.querySelectorAll('input[type="radio"]'))
          .some(radio => radio.name === input.name && radio.checked);
        if (!groupChecked) {
          markError(input);
          valid = false;
        }
      } else if (input.type === 'checkbox' && !input.checked) {
        markError(input);
        valid = false;
      } else if (input.type === 'file' && input.files.length === 0) {
        markError(input);
        valid = false;
      } else if (input.type !== 'checkbox' && input.type !== 'file' && !input.value.trim()) {
        markError(input);
        valid = false;
      } else if (
        input.type !== 'checkbox' && input.type !== 'file' &&
        (input.validity.patternMismatch || input.validity.typeMismatch)
      ) {
        // Format tidak sesuai: email UNNES, format email, atau kode verifikasi 6 digit.
        markError(input);
        valid = false;
      }
    });

    // Step 1: a konseli type must be selected.
    if (Number(panel.dataset.stepPanel) === 1 && !selectedType()) {
      const typeWrap = panel.querySelector('[data-type-wrap]');
      if (typeWrap) typeWrap.classList.add('has-error');
      valid = false;
    }

    // Panel yang memuat lingkup hambatan: minimal satu harus dipilih.
    // Dicek lewat keberadaan [data-obstacle-wrap], bukan nomor step, supaya tidak rusak saat urutan step berubah.
    const obstacleWrap = panel.querySelector('[data-obstacle-wrap]');
    if (obstacleWrap) {
      const checkedObstacles = panel.querySelectorAll('input[name="lingkup_hambatan[]"]:checked').length;
      if (checkedObstacles === 0) {
        obstacleWrap.classList.add('has-error');
        valid = false;
      } else {
        obstacleWrap.classList.remove('has-error');
      }
    }

    return valid;
  }

  typeCards.forEach(card => {
    const radio = card.querySelector('input[type="radio"]');
    if (!radio) return;

    card.addEventListener('click', () => {
      radio.checked = true;
      typeCards.forEach(item => item.classList.remove('is-selected'));
      card.classList.add('is-selected');
      updateTypeFields();

      const wrap = form.querySelector('[data-type-wrap]');
      if (wrap) wrap.classList.remove('has-error');
    });
  });

  form.querySelectorAll('.konseling-check input').forEach(input => {
    input.addEventListener('change', () => {
      input.closest('.konseling-check')?.classList.toggle('is-checked', input.checked);
      const obstacleWrap = form.querySelector('[data-obstacle-wrap]');
      if (obstacleWrap && input.checked) obstacleWrap.classList.remove('has-error');
    });
  });

  form.querySelectorAll('input, select, textarea').forEach(input => {
    input.addEventListener('input', () => {
      const field = input.closest('.konseling-field');
      if (field && input.value.trim()) field.classList.remove('has-error');
    });
    input.addEventListener('change', () => {
      const field = input.closest('.konseling-field');
      if (field && (input.type !== 'checkbox' || input.checked)) field.classList.remove('has-error');
    });
  });

  if (fileInput && fileName) {
    fileInput.addEventListener('change', () => {
      fileName.textContent = fileInput.files.length ? fileInput.files[0].name : 'Belum ada file dipilih';
      const field = fileInput.closest('.konseling-field');
      if (field && fileInput.files.length) field.classList.remove('has-error');
    });
  }

  nextButton?.addEventListener('click', () => {
    const panel = panels[currentStep - 1];
    if (!panel || !validatePanel(panel)) return;
    showStep(Math.min(currentStep + 1, panels.length));
  });

  prevButton?.addEventListener('click', () => {
    showStep(Math.max(currentStep - 1, 1));
  });

  form.addEventListener('submit', (event) => {
    const panel = panels[currentStep - 1];
    if (!panel || !validatePanel(panel)) {
      event.preventDefault();
      return;
    }

    // Biarkan browser mengirim form ke endpoint Laravel. Tombol dinonaktifkan
    // hanya setelah validasi lolos untuk mencegah double submit.
    submitButton?.setAttribute('disabled', 'disabled');
  });

  updateTypeFields();
  showStep(1, false);
})();
