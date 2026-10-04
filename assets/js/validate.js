// Client-side behaviour for the portal.
// The server (PHP) repeats every check, so this file is for speed and comfort only.
document.addEventListener('DOMContentLoaded', function () {

  function $(selector, root) { return (root || document).querySelector(selector); }
  function $$(selector, root) { return Array.prototype.slice.call((root || document).querySelectorAll(selector)); }

  function setError(form, name, message) {
    var box = $('[data-error-for="' + name + '"]', form);
    if (box) box.textContent = message || '';
    var field = form.elements[name];
    if (field && field.tagName) {            // single input/select (not a radio group)
      if (message) field.setAttribute('aria-invalid', 'true');
      else field.removeAttribute('aria-invalid');
    }
  }

  // ---------- Show / hide passwords ----------
  $$('[data-toggle-password]').forEach(function (toggle) {
    toggle.addEventListener('change', function () {
      var scope = toggle.closest('form') || document;
      $$('input[data-password]', scope).forEach(function (input) {
        input.type = toggle.checked ? 'text' : 'password';
      });
    });
  });

  // ---------- Password strength meter ----------
  var meter = $('[data-strength]');
  var pwInput = $('#register-form input[name="password"]');
  if (meter && pwInput) {
    var bars = $$('.sbar', meter);
    var label = $('[data-strength-label]', meter);
    var names = ['', 'Weak', 'Okay', 'Good', 'Strong'];
    pwInput.addEventListener('input', function () {
      var v = pwInput.value, score = 0;
      if (v.length >= 8) score++;
      if (v.length >= 12) score++;
      if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
      if (/[0-9]/.test(v) && /[^A-Za-z0-9]/.test(v)) score++;
      if (v.length === 0) score = 0;
      bars.forEach(function (bar, i) {
        bar.className = 'sbar' + (i < score ? ' on-' + score : '');
      });
      if (label) label.textContent = names[score];
    });
  }

  // ---------- Registration form ----------
  var reg = $('#register-form');
  if (reg) {
    var stepFields = { 1: ['full_name', 'email', 'phone', 'dob', 'gender'], 2: ['department', 'passport_photo'], 3: ['password', 'confirm_password'] };
    var val = function (name) { var el = reg.elements[name]; return el && el.value ? String(el.value).trim() : ''; };

    var rules = {
      full_name: function () { return val('full_name').length >= 3 ? '' : 'Enter your full name (at least 3 characters).'; },
      email: function () { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val('email')) ? '' : 'Enter a valid email address.'; },
      phone: function () { return /^[0-9+\-\s]{7,20}$/.test(val('phone')) ? '' : 'Enter a valid phone number.'; },
      dob: function () { return val('dob') !== '' && new Date(val('dob')) < new Date() ? '' : 'Enter a valid date of birth.'; },
      gender: function () { return val('gender') !== '' ? '' : 'Choose your gender.'; },
      department: function () { return val('department') !== '' ? '' : 'Choose your department.'; },
      passport_photo: function () {
        var file = reg.elements['passport_photo'].files[0];
        if (!file) return 'Choose a passport photograph.';
        if (['image/jpeg', 'image/png'].indexOf(file.type) === -1) return 'Only JPG or PNG images are allowed.';
        if (file.size > 2 * 1024 * 1024) return 'The photograph must not be larger than 2 MB.';
        return '';
      },
      password: function () { return reg.elements['password'].value.length >= 8 ? '' : 'Password must be at least 8 characters.'; },
      confirm_password: function () { return reg.elements['password'].value === reg.elements['confirm_password'].value ? '' : 'Passwords do not match.'; }
    };

    // Validate the fields of one step. Returns true when all are fine.
    function validateStep(n) {
      var firstBad = null;
      stepFields[n].forEach(function (name) {
        var msg = rules[name]();
        setError(reg, name, msg);
        if (msg && !firstBad) firstBad = name;
      });
      if (firstBad) {
        var el = reg.elements[firstBad];
        if (el) { var target = el.tagName ? el : el[0]; if (target && target.focus) target.focus(); }
        return false;
      }
      return true;
    }

    // Step display
    var steps = $$('[data-step]', reg);
    var indicators = $$('[data-step-indicator]');
    var current = parseInt(reg.getAttribute('data-start-step') || '1', 10);
    reg.classList.add('is-stepped');

    function show(n) {
      current = n;
      steps.forEach(function (el) {
        el.classList.toggle('is-current', parseInt(el.getAttribute('data-step'), 10) === n);
      });
      indicators.forEach(function (li) {
        var i = parseInt(li.getAttribute('data-step-indicator'), 10);
        li.classList.toggle('is-active', i === n);
        li.classList.toggle('is-done', i < n);
        if (i === n) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current');
      });
      var top = reg.getBoundingClientRect().top;
      if (top < 0) window.scrollTo(0, window.scrollY + top - 100);
    }

    $$('[data-next]', reg).forEach(function (btn) {
      btn.addEventListener('click', function () { if (validateStep(current)) show(current + 1); });
    });
    $$('[data-prev]', reg).forEach(function (btn) {
      btn.addEventListener('click', function () { show(current - 1); });
    });

    // Enter moves to the next step instead of submitting early
    reg.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' && event.target.tagName === 'INPUT' && event.target.type !== 'submit' && current < 3) {
        event.preventDefault();
        if (validateStep(current)) show(current + 1);
      }
    });

    // Final check before sending
    reg.addEventListener('submit', function (event) {
      for (var n = 1; n <= 3; n++) {
        if (!validateStep(n)) {
          event.preventDefault();
          show(n);
          validateStep(n);       // focus the first bad field now that the step is visible
          return;
        }
      }
    });

    // Clear a message as soon as the person fixes the field
    ['input', 'change'].forEach(function (type) {
      reg.addEventListener(type, function (event) {
        if (event.target.name) setError(reg, event.target.name, '');
      });
    });

    // Photo preview
    var photo = reg.elements['passport_photo'];
    var preview = $('#photo_preview');
    var placeholder = $('#photo_placeholder');
    var fileName = $('[data-file-name]', reg);
    photo.addEventListener('change', function () {
      var file = photo.files[0];
      if (!file) {
        preview.classList.add('hidden'); placeholder.classList.remove('hidden');
        if (fileName) fileName.textContent = '';
        return;
      }
      if (fileName) fileName.textContent = file.name;
      if (/^image\/(jpeg|png)$/.test(file.type)) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden'); placeholder.classList.add('hidden');
      }
      var msg = rules.passport_photo();
      if (msg) setError(reg, 'passport_photo', msg);
    });

    show(current);
  }

  // ---------- Login forms ----------
  $$('form[data-login-form]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      var ok = true;
      $$('input[required]', form).forEach(function (input) {
        var empty = input.value.trim() === '';
        setError(form, input.name, empty ? 'This field is required.' : '');
        if (empty) ok = false;
      });
      if (!ok) event.preventDefault();
    });
  });
});
