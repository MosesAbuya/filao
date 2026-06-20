<?php
/**
 * includes/start-planning-modal.php
 * Global Start Planning Stepper Modal for Filao Adventures
 * Loaded by footer.php on every page.
 * 
 * JavaScript triggers it via openPlanningModal(tourId, tourTitle, destName)
 */
$pdo_modal = getPDO();
$modalDests = $pdo_modal->query("SELECT id, name FROM destinations ORDER BY name ASC")->fetchAll();
$modalActs = $pdo_modal->query("SELECT id, name, category FROM activities ORDER BY category ASC, name ASC")->fetchAll();
$currentYear = (int) date('Y');
?>

<!-- intl-tel-input CSS for country dropdown -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.5/css/intlTelInput.css" />
<style>
  .iti { width: 100%; display: block; }
  .iti__flag { background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.5/img/flags.png"); }
  @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .iti__flag { background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.5/img/flags@2x.png"); }
  }
</style>

<!-- =====================================================
     START PLANNING MODAL OVERLAY
     ===================================================== -->
<div id="spModal" class="sp-modal-overlay" role="dialog" aria-modal="true" aria-label="Start Planning Modal"
  style="display:none;">
  <div class="sp-modal-box">

    <!-- Close -->
    <button class="sp-modal-close" id="spClose" aria-label="Close">&times;</button>

    <!-- Progress Bar -->
    <div class="sp-progress-wrap">
      <div class="sp-progress-bar" id="spProgressBar"></div>
    </div>

    <!-- Context Banner (shows pre-selected tour) -->
    <div id="spContextBanner" class="sp-context-banner" style="display:none;">
      <i class="fa fa-binoculars mr-2" style="color:#C49018;"></i>
      <span>Enquiring about: <strong id="spContextLabel"></strong></span>
    </div>

    <!-- Step Track -->
    <div id="spStepsWrap" class="sp-steps-wrap">

      <!-- ======= STEP 1: Know where to travel? (Generic only) ======= -->
      <div class="sp-step" data-step="1" data-skip-if-tour="true">
        <div class="sp-step-icon"><i class="fa fa-globe"></i></div>
        <h2 class="sp-step-title">Your dream journey starts here.</h2>
        <p class="sp-step-sub">Let's get to know what you're looking for so we can craft the perfect itinerary for you.
        </p>
        <p class="sp-step-question">Do you know where you want to travel to?</p>
        <div class="sp-choice-grid sp-choice-3col">
          <button class="sp-choice-btn" data-next="2" data-val="yes" data-field="knows_dest">Yes, I do!</button>
          <button class="sp-choice-btn" data-next="2" data-val="idea" data-field="knows_dest">I have an idea, need
            advice</button>
          <button class="sp-choice-btn" data-next="2" data-val="no" data-field="knows_dest">No help me decide!</button>
        </div>
      </div>

      <!-- ======= STEP 2: Select Destination ======= -->
      <div class="sp-step" data-step="2">
        <div class="sp-step-icon"><i class="fa fa-map-marker"></i></div>
        <h2 class="sp-step-title">Where would you like to go?</h2>
        <p class="sp-step-sub">We operate across Africa and beyond. Select a destination, or let our experts recommend
          one for you.</p>
        <div class="sp-choice-grid sp-choice-wrap">
          <?php foreach ($modalDests as $d): ?>
            <button class="sp-choice-btn" data-field="destination"
              data-val="<?= htmlspecialchars($d['name']) ?>"><?= htmlspecialchars($d['name']) ?></button>
          <?php endforeach; ?>
          <button class="sp-choice-btn sp-choice-secondary" data-field="destination" data-val="Not sure yet">Not sure
            yet advise me</button>
        </div>
        <div class="sp-nav-row">
          <button class="sp-back-btn" data-prev="1"><i class="fa fa-angle-left mr-1"></i> Back</button>
          <button class="sp-next-btn" data-next="3">Next <i class="fa fa-angle-right ml-1"></i></button>
        </div>
      </div>

      <!-- ======= STEP 3: When to travel? ======= -->
      <div class="sp-step" data-step="3">
        <div class="sp-step-icon"><i class="fa fa-calendar"></i></div>
        <h2 class="sp-step-title">When would you like to travel?</h2>
        <p class="sp-step-sub">Select a preferred month or a range if you're flexible. We'll find the best seasonal
          windows for you.</p>

        <!-- Year toggle -->
        <div class="sp-year-row">
          <button class="sp-year-btn sp-year-active" data-year="<?= $currentYear ?>"><?= $currentYear ?></button>
          <button class="sp-year-btn" data-year="<?= $currentYear + 1 ?>"><?= $currentYear + 1 ?></button>
          <button class="sp-year-btn" data-year="<?= $currentYear + 2 ?>"><?= $currentYear + 2 ?></button>
        </div>
        <div class="sp-month-grid">
          <?php $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']; ?>
          <?php foreach ($months as $m): ?>
            <button class="sp-month-btn" data-field="travel_month" data-val="<?= $m ?>"><?= $m ?></button>
          <?php endforeach; ?>
          <button class="sp-month-btn sp-choice-secondary" data-field="travel_month" data-val="Flexible">Any
            month</button>
        </div>

        <div class="sp-exact-date-row" style="margin-top:20px; text-align:center;">
          <p style="font-size:13px; color:#6B6358; margin-bottom:8px;">Or pick an exact travel date if you know it:</p>
          <input type="date" id="spExactDate" name="exact_travel_date" class="sp-text-input" style="max-width:250px; margin:0 auto; padding:10px 15px; border-radius:4px; border:1px solid #D5CFC9; outline:none; text-align:center;">
        </div>

        <!-- Duration -->
        <div class="sp-duration-row" style="margin-top:30px; text-align:center; display:flex; flex-direction:column; align-items:center;">
          <label class="sp-label" for="spDuration" style="margin-bottom:10px;">How many days would you like to travel?</label>
          <select id="spDuration" name="duration" class="sp-select" style="max-width:250px;">
            <option value="">Select duration</option>
            <?php for ($d = 3; $d <= 21; $d++): ?>
              <option value="<?= $d ?>"><?= $d ?> days</option>
            <?php endfor; ?>
            <option value="21+">More than 21 days</option>
            <option value="Flexible">I'm flexible</option>
          </select>
        </div>
        <div class="sp-nav-row">
          <button class="sp-back-btn" data-prev="2"><i class="fa fa-angle-left mr-1"></i> Back</button>
          <button class="sp-next-btn" data-next="4">Next <i class="fa fa-angle-right ml-1"></i></button>
        </div>
      </div>

      <!-- ======= STEP 4: Who's travelling? ======= -->
      <div class="sp-step" data-step="4">
        <div class="sp-step-icon"><i class="fa fa-users"></i></div>
        <h2 class="sp-step-title">Who will be travelling with you?</h2>
        <p class="sp-step-sub">Please note that children older than 12 are considered adults. This helps us find the
          best options for your group.</p>
        <div class="sp-travellers-row">
          <div class="sp-counter-box">
            <label class="sp-label">Adults</label>
            <div class="sp-counter">
              <button type="button" class="sp-counter-btn sp-dec" data-field="adults">−</button>
              <span class="sp-counter-val" id="spAdultsVal">2</span>
              <button type="button" class="sp-counter-btn sp-inc" data-field="adults">+</button>
            </div>
          </div>
          <div class="sp-counter-box">
            <label class="sp-label">Children <small style="font-weight:400;">(Under 12)</small></label>
            <div class="sp-counter">
              <button type="button" class="sp-counter-btn sp-dec" data-field="children">−</button>
              <span class="sp-counter-val" id="spChildrenVal">0</span>
              <button type="button" class="sp-counter-btn sp-inc" data-field="children">+</button>
            </div>
          </div>
        </div>
        <div class="sp-nav-row">
          <button class="sp-back-btn" data-prev="3"><i class="fa fa-angle-left mr-1"></i> Back</button>
          <button class="sp-next-btn" data-next="5">Next <i class="fa fa-angle-right ml-1"></i></button>
        </div>
      </div>

      <!-- ======= STEP 5: Budget ======= -->
      <div class="sp-step" data-step="5">
        <div class="sp-step-icon"><i class="fa fa-usd"></i></div>
        <h2 class="sp-step-title">What is your budget per person?</h2>
        <p class="sp-step-sub">This includes accommodation, activities, and local travel. International flights are
          excluded. Don't worry we work across all budgets!</p>
        <div class="sp-budget-display">
          <span class="sp-budget-currency">USD</span>
          <span class="sp-budget-amount" id="spBudgetDisplay">$4,000</span>
          <span class="sp-budget-label">per person</span>
        </div>
        <div class="sp-slider-wrap">
          <input type="range" id="spBudgetSlider" class="sp-slider" min="500" max="25000" step="250" value="4000">
          <div class="sp-slider-labels">
            <span>$500</span><span>$5k</span><span>$10k</span><span>$15k</span><span>$20k</span><span>$25k+</span>
          </div>
        </div>
        <div class="sp-nav-row">
          <button class="sp-back-btn" data-prev="4"><i class="fa fa-angle-left mr-1"></i> Back</button>
          <button class="sp-next-btn" data-next="6">Next <i class="fa fa-angle-right ml-1"></i></button>
        </div>
      </div>

      <!-- ======= STEP 6: Contact Info ======= -->
      <div class="sp-step" data-step="6">
        <div class="sp-step-icon"><i class="fa fa-envelope"></i></div>
        <h2 class="sp-step-title">Almost there! How do we reach you?</h2>
        <p class="sp-step-sub">A Filao safari specialist will personally reach out to you within 24 hours to begin
          crafting your perfect journey.</p>

        <div class="sp-form-grid">
          <div class="sp-field-wrap">
            <label class="sp-label" for="spFname">First Name *</label>
            <input type="text" id="spFname" class="sp-text-input" placeholder="Jane" required>
          </div>
          <div class="sp-field-wrap">
            <label class="sp-label" for="spLname">Last Name *</label>
            <input type="text" id="spLname" class="sp-text-input" placeholder="Smith" required>
          </div>
          <div class="sp-field-wrap">
            <label class="sp-label" for="spEmail">Email Address *</label>
            <input type="email" id="spEmail" class="sp-text-input" placeholder="jane@example.com" required>
          </div>
          <div class="sp-field-wrap">
            <label class="sp-label" for="spPhone">Phone / WhatsApp</label>
            <input type="tel" id="spPhone" class="sp-text-input" placeholder="">
          </div>
          <div class="sp-field-wrap sp-field-full">
            <label class="sp-label" for="spMessage">Anything else we should know?</label>
            <textarea id="spMessage" class="sp-text-input sp-textarea" rows="3"
              placeholder="Special requirements, preferred lodges, occasion..."></textarea>
          </div>
          <div class="sp-field-wrap sp-field-full" style="margin-top:10px;">
            <label style="display:flex; align-items:flex-start; gap:8px; font-size:13.5px; font-family:'Inter',sans-serif; color:#4A4340; cursor:pointer; line-height:1.4;">
              <input type="checkbox" id="spNewsletterOptIn" checked style="accent-color:#C49018; margin-top:3px;">
              <span>Yes, I'd like to receive safari inspiration, expert tips, and exclusive offers from Filao Adventures via email.</span>
            </label>
          </div>
        </div>

        <div id="spFormError" class="sp-form-error" style="display:none;"></div>

        <div class="sp-nav-row">
          <button class="sp-back-btn" data-prev="5"><i class="fa fa-angle-left mr-1"></i> Back</button>
          <button class="sp-submit-btn" id="spSubmitBtn">
            <i class="fa fa-paper-plane mr-2"></i> Send My Safari Plan
          </button>
        </div>
        <p class="sp-privacy-note"><i class="fa fa-lock mr-1"></i> Your information is secure. We never sell or share
          your data.</p>
      </div>

      <!-- ======= STEP 7: Thank You ======= -->
      <div class="sp-step sp-step-thankyou" data-step="7">
        <div class="sp-thankyou-icon">
          <i class="fa fa-check-circle"></i>
        </div>
        <h2 class="sp-step-title">Your safari journey is about to begin!</h2>
        <p class="sp-step-sub" id="spThankYouMsg">Thank you! A Filao safari specialist will reach out to you within 24
          hours.</p>
        <div class="sp-thankyou-info">
          <p>In the meantime, explore our tours or get inspired:</p>
          <div class="sp-thankyou-links" style="display:flex; justify-content:center; gap:15px; margin-top:20px; flex-wrap:wrap;">
            <a href="/filao/tours" class="sp-next-btn" style="text-decoration:none; display:inline-block;">Browse All Tours</a>
            <a href="/filao/destinations" class="sp-back-btn" style="text-decoration:none; display:inline-block;">View Destinations</a>
          </div>
        </div>
        <button class="sp-back-btn" id="spCloseThankYou" style="margin-top:20px;">Close</button>
      </div>

    </div><!-- /.sp-steps-wrap -->

    <!-- Hidden data store for form values -->
    <input type="hidden" id="spTourId" value="">
    <input type="hidden" id="spTourTitle" value="">
    <input type="hidden" id="spSelectedYear" value="<?= $currentYear ?>">
    <input type="hidden" id="spAdultsHidden" value="2">
    <input type="hidden" id="spChildrenHidden" value="0">
    <input type="hidden" id="spBudgetHidden" value="4000">
  </div>
</div>

<!-- intl-tel-input JS for country dropdown -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.5/js/intlTelInput.min.js"></script>