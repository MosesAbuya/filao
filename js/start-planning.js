/**
 * js/start-planning.js
 * Filao Adventures   Start Planning Stepper Modal
 * Handles: step navigation, multi-select, budget slider,
 *          travellers counter, AJAX form submission, context mode
 */
(function () {
  'use strict';

  // ---- State ----
  const state = {
    currentStep: 1,
    isTourContext: false,
    totalSteps: 8,      // steps rendered (1-8 + thankyou=9)
    knows_dest: null,
    destination: null,
    activities: [],
    travel_month: null,
    travel_year: new Date().getFullYear(),
    duration: null,
    adults: 2,
    children: 0,
    budget: 4000,
    travelled_before: null,
    referred: null,
    tour_id: null,
    tour_title: null,
  };

  // ---- DOM refs ----
  const overlay   = document.getElementById('spModal');
  const closeBtn  = document.getElementById('spClose');
  const closeTY   = document.getElementById('spCloseThankYou');
  const progBar   = document.getElementById('spProgressBar');
  const ctxBanner = document.getElementById('spContextBanner');
  const ctxLabel  = document.getElementById('spContextLabel');
  const tourIdEl  = document.getElementById('spTourId');
  const tourTitleEl = document.getElementById('spTourTitle');
  const yearEl    = document.getElementById('spSelectedYear');
  const budgetSlider  = document.getElementById('spBudgetSlider');
  const budgetDisplay = document.getElementById('spBudgetDisplay');
  const adultsVal    = document.getElementById('spAdultsVal');
  const childrenVal  = document.getElementById('spChildrenVal');
  const formError    = document.getElementById('spFormError');
  const submitBtn    = document.getElementById('spSubmitBtn');

  // ---- Intl Tel Input ----
  let phoneIti = null;
  const phoneInput = document.getElementById('spPhone');
  if (phoneInput && window.intlTelInput) {
    phoneIti = window.intlTelInput(phoneInput, {
      initialCountry: "auto",
      autoPlaceholder: "off",
      separateDialCode: true,
      geoIpLookup: function(callback) {
        fetch("https://ipapi.co/json")
          .then(function(res) { return res.json(); })
          .then(function(data) { callback(data.country_code); })
          .catch(function() { callback("us"); });
      },
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.5/js/utils.js",
    });
  }

  // ---- Open Modal ----
  window.openPlanningModal = function (tourId, tourTitle, destName) {
    // Reset state
    resetState();

    if (tourId && tourTitle) {
      state.isTourContext = true;
      state.tour_id   = tourId;
      state.tour_title = tourTitle;
      if (destName) state.destination = destName;

      tourIdEl.value    = tourId;
      tourTitleEl.value = tourTitle;
      ctxLabel.textContent = tourTitle;
      ctxBanner.style.display = 'flex';

      // Skip step 1 (know where?), 2 (destination), and 3 (activities) for tour context
      goToStep(4);
    } else {
      state.isTourContext = false;
      ctxBanner.style.display = 'none';
      goToStep(1);
    }

    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  };

  function closeModal () {
    overlay.style.display = 'none';
    document.body.style.overflow = '';
  }

  function resetState () {
    state.currentStep = 1;
    state.isTourContext = false;
    state.activities = [];
    state.destination = null;
    state.travel_month = null;
    state.duration = null;
    state.travelled_before = null;
    state.referred = null;
    state.tour_id = null;
    state.tour_title = null;
    
    // reset UI
    state.adults = 2; state.children = 0; state.budget = 4000;
    adultsVal.textContent = 2; childrenVal.textContent = 0;
    if (budgetSlider) { budgetSlider.value = 4000; budgetDisplay.textContent = '$4,000'; }
    document.getElementById('spFname').value = '';
    document.getElementById('spLname').value = '';
    document.getElementById('spEmail').value = '';
    document.getElementById('spPhone').value = '';
    document.getElementById('spMessage').value = '';
    document.getElementById('spCustomPurpose').value = '';
    // clear all selections
    document.querySelectorAll('.sp-choice-btn.sp-selected').forEach(el => el.classList.remove('sp-selected'));
    document.querySelectorAll('.sp-month-btn.sp-selected').forEach(el => el.classList.remove('sp-selected'));
    document.querySelectorAll('.sp-year-btn.sp-year-active').forEach(el => el.classList.remove('sp-year-active'));
    document.querySelectorAll('.sp-year-btn').forEach(el => { if (el.dataset.year == new Date().getFullYear()) el.classList.add('sp-year-active'); });
    formError.style.display = 'none';
  }

  // ---- Step Navigation ----
  function goToStep (n, goingBack) {
    const all = document.querySelectorAll('.sp-step');
    all.forEach(el => {
      el.classList.remove('sp-active', 'sp-back-anim');
    });
    const target = document.querySelector(`.sp-step[data-step="${n}"]`);
    if (!target) return;
    if (goingBack) target.classList.add('sp-back-anim');
    target.classList.add('sp-active');
    state.currentStep = n;
    updateProgress();
    // Scroll modal to top
    const box = document.querySelector('.sp-modal-box');
    if (box) box.scrollTop = 0;
  }

  function updateProgress () {
    const pct = Math.round(((state.currentStep - 1) / (state.totalSteps - 1)) * 100);
    if (progBar) progBar.style.width = pct + '%';
  }

  // ---- Choice Button clicks ----
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.sp-choice-btn');
    if (!btn) return;

    const field = btn.dataset.field;
    const val   = btn.dataset.val;
    const nextS = btn.dataset.next ? parseInt(btn.dataset.next) : null;
    const isMulti = btn.classList.contains('sp-multi');

    if (isMulti) {
      // Toggle selection
      btn.classList.toggle('sp-selected');
      if (field === 'activities') {
        if (btn.classList.contains('sp-selected')) {
          if (!state.activities.includes(val)) state.activities.push(val);
        } else {
          state.activities = state.activities.filter(a => a !== val);
        }
      }
    } else {
      // Single-select in same group
      const siblings = btn.closest('.sp-choice-grid, .sp-question-block').querySelectorAll(`.sp-choice-btn[data-field="${field}"]`);
      siblings.forEach(s => s.classList.remove('sp-selected'));
      btn.classList.add('sp-selected');
      state[field] = val;
      if (nextS) {
        setTimeout(() => goToStep(nextS), 280);
      }
    }
  });

  // ---- Month buttons ----
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.sp-month-btn');
    if (!btn) return;
    document.querySelectorAll('.sp-month-btn').forEach(b => b.classList.remove('sp-selected'));
    btn.classList.add('sp-selected');
    state.travel_month = btn.dataset.val;
  });

  // ---- Year buttons ----
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.sp-year-btn');
    if (!btn) return;
    document.querySelectorAll('.sp-year-btn').forEach(b => b.classList.remove('sp-year-active'));
    btn.classList.add('sp-year-active');
    state.travel_year = btn.dataset.year;
  });

  // ---- Next buttons ----
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.sp-next-btn');
    if (!btn) return;
    const next = parseInt(btn.dataset.next);
    goToStep(next);
  });

  // ---- Back buttons ----
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.sp-back-btn');
    if (!btn || btn.id === 'spCloseThankYou') return;
    let prev = parseInt(btn.dataset.prev);
    // In tour context, skip steps 1, 2 and 3
    if (state.isTourContext && (prev === 1 || prev === 2 || prev === 3)) {
      closeModal();
      return;
    }
    goToStep(prev, true);
  });

  // ---- Close ----
  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (closeTY)  closeTY.addEventListener('click', closeModal);
  overlay.addEventListener('click', function (e) {
    if (e.target === overlay) closeModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModal();
  });

  // ---- Budget Slider ----
  if (budgetSlider) {
    budgetSlider.addEventListener('input', function () {
      const v = parseInt(this.value);
      state.budget = v;
      budgetDisplay.textContent = v >= 25000 ? '$25,000+' : '$' + v.toLocaleString();
      // Update slider gradient
      const pct = ((v - 500) / (25000 - 500)) * 100;
      this.style.background = `linear-gradient(90deg, #C49018 ${pct}%, #E5DDD0 ${pct}%)`;
    });
  }

  // ---- Travellers Counter ----
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.sp-counter-btn');
    if (!btn) return;
    const field = btn.closest('.sp-counter-box').querySelector('.sp-counter-btn').dataset.field ||
                  btn.dataset.field;
    const isInc = btn.classList.contains('sp-inc');
    const isDec = btn.classList.contains('sp-dec');
    const counterField = btn.dataset.field;

    if (counterField === 'adults') {
      if (isInc) state.adults = Math.min(state.adults + 1, 20);
      if (isDec) state.adults = Math.max(state.adults - 1, 1);
      adultsVal.textContent = state.adults;
    } else if (counterField === 'children') {
      if (isInc) state.children = Math.min(state.children + 1, 10);
      if (isDec) state.children = Math.max(state.children - 1, 0);
      childrenVal.textContent = state.children;
    }
  });

  // ---- Select Duration ----
  const durationSel = document.getElementById('spDuration');
  if (durationSel) {
    durationSel.addEventListener('change', function () {
      state.duration = this.value;
    });
  }

  // ---- AJAX Submit ----
  if (submitBtn) {
    submitBtn.addEventListener('click', function () {
      const fname = document.getElementById('spFname').value.trim();
      const lname = document.getElementById('spLname').value.trim();
      const email = document.getElementById('spEmail').value.trim();
      
      let phone = document.getElementById('spPhone').value.trim();
      if (phoneIti && phone) {
        phone = phoneIti.getNumber(); // get full international number
      }
      
      const msg   = document.getElementById('spMessage').value.trim();
      const customPurpose = document.getElementById('spCustomPurpose').value.trim();
      const exactDate = document.getElementById('spExactDate') ? document.getElementById('spExactDate').value : '';

      formError.style.display = 'none';

      if (!fname || !email) {
        formError.textContent = 'Please enter your first name and email address.';
        formError.style.display = 'block';
        return;
      }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        formError.textContent = 'Please enter a valid email address.';
        formError.style.display = 'block';
        return;
      }

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Sending...';

      const formData = new FormData();
      formData.append('type', 'start_planning');
      formData.append('first_name', fname);
      formData.append('last_name', lname);
      formData.append('email', email);
      formData.append('phone', phone);
      formData.append('message', msg);
      formData.append('custom_purpose', customPurpose);
      formData.append('destination', state.destination || '');
      formData.append('tour_id', state.tour_id || '');
      formData.append('tour_title', state.tour_title || '');
      formData.append('travel_month', state.travel_month || '');
      formData.append('travel_year', state.travel_year || '');
      formData.append('exact_travel_date', exactDate);
      formData.append('duration', state.duration || '');
      formData.append('adults', state.adults);
      formData.append('children', state.children);
      formData.append('budget', state.budget);
      formData.append('travelled_before', state.travelled_before || '');
      formData.append('referred', state.referred || '');
      const optInBox = document.getElementById('spNewsletterOptIn');
      if (optInBox && optInBox.checked) {
        formData.append('newsletter_optin', '1');
      }
      state.activities.forEach(a => formData.append('activities[]', a));

      const basePath = window.location.hostname === 'localhost' ? '/filao' : '';
      fetch(basePath + '/handlers/enquiry.php', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          document.getElementById('spThankYouMsg').textContent = data.message;
          goToStep(9);
        } else {
          formError.textContent = data.message || 'Something went wrong. Please try again.';
          formError.style.display = 'block';
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="fa fa-paper-plane mr-2"></i> Send My Safari Plan';
        }
      })
      .catch(() => {
        formError.textContent = 'Network error. Please check your connection and try again.';
        formError.style.display = 'block';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa fa-paper-plane mr-2"></i> Send My Safari Plan';
      });
    });
  }

  // ---- Wire up "Start Planning" buttons sitewide ----
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-open-planner]');
    if (!btn) return;
    e.preventDefault();
    const tourId    = btn.dataset.tourId    || null;
    const tourTitle = btn.dataset.tourTitle || null;
    const destName  = btn.dataset.destName  || null;
    window.openPlanningModal(tourId, tourTitle, destName);
  });

  // ---- Initialize ----
  // Show step 1 on first load hidden
  document.querySelectorAll('.sp-step').forEach(el => el.classList.remove('sp-active'));

})();
