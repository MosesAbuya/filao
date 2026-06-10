<?php
// includes/footer.php   Filao Adventures Global Footer
?>
<footer class="fa-footer">
  <div class="container" style="max-width:1280px;">
    <div class="row">
      <!-- Brand column -->
      <div class="col-lg-3 col-md-6 mb-5">
        <img src="assets/logo/filao-logo.png" alt="Filao Adventures" class="fa-footer-logo">
        <p class="fa-footer-desc">Your premier safari and luxury travel partner. Crafting unforgettable journeys across
          Africa and beyond since 2020.</p>
        <div class="social-links" style="margin-bottom:24px;">
          <a href="https://www.facebook.com/profile.php?id=100084891550126#" aria-label="Facebook" target="_blank" rel="noopener noreferrer"><i class="fa fa-facebook"></i></a>
          <a href="https://www.instagram.com/filaoadventures/" aria-label="Instagram" target="_blank" rel="noopener noreferrer"><i class="fa fa-instagram"></i></a>
          <a href="https://ke.linkedin.com/jobs/view/travel-consultant-at-filao-adventures-4398464574" aria-label="LinkedIn" target="_blank" rel="noopener noreferrer"><i class="fa fa-linkedin"></i></a>
          <a href="https://wa.me/254757139239" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer"><i class="fa fa-whatsapp"></i></a>
        </div>
        <div class="footer-affiliation">
          <span
            style="font-size:11px;font-family:'Inter',sans-serif;color:#6B6358;text-transform:uppercase;letter-spacing:0.1em;display:block;margin-bottom:8px;">Proudly
            Affiliated With</span>
          <a href="https://www.safaribookings.com/p6895" target="_blank" rel="noopener noreferrer" style="margin-right: 10px;">
            <img src="images/Filao/safaribookings.png" alt="Safari Bookings"
              style="height:35px;opacity:0.9;transition:opacity 0.2s;">
          </a>
          <a href="https://www.tripadvisor.co.za/Attraction_Review-g294207-d24109431-Reviews-FILAO_ADVENTURES-Nairobi.html" target="_blank" rel="noopener noreferrer">
            <img src="images/Filao/tripadvisor.svg" alt="TripAdvisor"
              style="height:35px;opacity:0.9;transition:opacity 0.2s;">
          </a>
        </div>
      </div>

      <!-- Destinations -->
      <div class="col-lg-2 col-md-6 mb-5">
        <span class="footer-col-heading">Destinations</span>
        <ul>
          <li><a href="destinations">Maasai Mara</a></li>
          <li><a href="destinations">Amboseli</a></li>
          <li><a href="destinations">Serengeti</a></li>
          <li><a href="destinations">Zanzibar</a></li>
          <li><a href="destinations">Diani Beach</a></li>
          <li><a href="destinations">Bali &amp; Dubai</a></li>
          <li><a href="destinations">View All &rarr;</a></li>
        </ul>
      </div>

      <!-- Safaris & Tours -->
      <div class="col-lg-2 col-md-6 mb-5">
        <span class="footer-col-heading">Safaris &amp; Tours</span>
        <ul>
          <li><a href="tours">All Tours</a></li>
          <li><a href="safaris">Safari Experiences</a></li>
          <li><a href="safaris">Wildlife Safaris</a></li>
          <li><a href="safaris">Beach Holidays</a></li>
          <li><a href="safaris">City Tours</a></li>
          <li><a href="blog">Travel Blog</a></li>
        </ul>
      </div>

      <!-- Company -->
      <div class="col-lg-2 col-md-6 mb-5">
        <span class="footer-col-heading">Company</span>
        <ul>
          <li><a href="about">About Us</a></li>
          <li><a href="team">Meet the Team</a></li>
          <li><a href="sustainable-tourism">Sustainability</a></li>
          <li><a href="accreditations">Accreditations</a></li>
          <li><a href="careers">Careers</a></li>
          <li><a href="privacy-policy">Privacy Policy</a></li>
        </ul>
      </div>

      <!-- Contact -->
      <div class="col-lg-3 col-md-6 mb-5">
        <span class="footer-col-heading">Contact Us</span>
        <ul>
          <li style="margin-bottom:12px;">
            <span style="display:flex;align-items:flex-start;gap:8px;color:rgba(255,255,255,.55);font-size:14px;">
              <i class="fa fa-map-marker mt-1" style="color:#C49018;flex-shrink:0;"></i>
              Nairobi, Kenya
            </span>
          </li>
          <li style="margin-bottom:12px;">
            <a href="tel:+254757139239" style="display:flex;align-items:center;gap:8px;">
              <i class="fa fa-phone" style="color:#C49018;flex-shrink:0;"></i>
              +254 757 139239
            </a>
          </li>
          <li style="margin-bottom:12px;">
            <a href="mailto:info@filaoadventures.co.ke" style="display:flex;align-items:center;gap:8px;">
              <i class="fa fa-envelope" style="color:#C49018;flex-shrink:0;"></i>
              info@filaoadventures.co.ke
            </a>
          </li>
          <li>
            <button data-open-planner="true" class="tc-cta" style="margin-top:8px;border:none;cursor:pointer;">Plan My
              Safari</button>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div class="fa-footer-bottom">
    <div class="container" style="max-width:1280px; text-align: center;">
      <p class="mb-0">
        &copy; <?php echo date('Y'); ?> Filao Adventures. All Rights Reserved. &mdash; Nairobi, Kenya &mdash; Crafting
        Unforgettable Journeys.<br>
        <span style="font-size:12px;opacity:0.7;">Site by <a href="https://mmtechpro.co.ke" target="_blank"
            style="color:inherit;text-decoration:underline;">Millenium Meritum Technology &amp; Procurement</a></span>
      </p>
    </div>
  </div>
</footer>

<!-- ====== START PLANNING MODAL (Global) ====== -->
<link rel="stylesheet" href="/filao/css/start-planning.css">

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/254757139239" target="_blank" rel="noopener noreferrer"
  style="position:fixed;bottom:24px;right:24px;width:60px;height:60px;background-color:#25D366;color:#FFF;border-radius:50%;text-align:center;font-size:30px;box-shadow:0 4px 10px rgba(0,0,0,0.3);z-index:9999;display:flex;align-items:center;justify-content:center;transition:transform 0.3s;"
  onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"
  aria-label="Chat on WhatsApp">
  <i class="fa fa-whatsapp"></i>
</a>

<?php require_once 'includes/start-planning-modal.php'; ?>