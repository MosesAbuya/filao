<?php
// includes/footer.php   Filao Adventures Global Footer
?>
<footer class="fa-footer">
  <div class="container" style="max-width:1280px;">
    <!-- Newsletter Subscription Banner -->
    <div class="row mb-5 pb-5" style="border-bottom:1px solid rgba(255,255,255,0.1);">
      <div class="col-lg-8 col-md-10 mx-auto text-center">
        <h3 style="font-family:'Cormorant Garant',serif;color:#C49018;margin-bottom:15px;font-size:32px;">Join Our Newsletter</h3>
        <p style="color:rgba(255,255,255,0.7);font-size:15px;margin-bottom:24px;">Subscribe to receive exclusive safari offers, travel inspiration, and expert tips directly to your inbox.</p>
        <form class="newsletter-form" id="footerNewsletterForm" style="display:flex; max-width:500px; margin:0 auto; position:relative;">
          <input type="email" name="email" placeholder="Your Email Address" required style="flex:1; padding:12px 20px; border:1px solid rgba(255,255,255,0.2); background:rgba(255,255,255,0.05); color:#fff; border-radius:30px 0 0 30px; outline:none;">
          <button type="submit" style="background:#C49018; color:#fff; border:none; padding:12px 30px; border-radius:0 30px 30px 0; font-family:'Inter',sans-serif; font-weight:600; cursor:pointer; text-transform:uppercase; font-size:12px; letter-spacing:0.1em; transition:background 0.3s;">Subscribe</button>
        </form>
        <div id="footerNewsletterFeedback" style="display:none; font-size:13px; margin-top:15px; border-radius:4px; padding:10px; max-width:500px; margin-left:auto; margin-right:auto;"></div>
      </div>
    </div>

    <div class="row">
      <!-- Brand column -->
      <div class="col-lg-3 col-md-6 mb-5">
        <img src="assets/logo/filao-logo.png" alt="Filao Adventures" class="fa-footer-logo">
        <p class="fa-footer-desc">Your premier safari and luxury travel partner. Crafting unforgettable journeys across
          Africa and beyond since 2020.</p>
        <div class="social-links" style="margin-bottom:24px;">
          <a href="https://x.com/FilaoAdventures" aria-label="X (Twitter)" target="_blank" rel="noopener noreferrer"><i class="fa fa-twitter"></i></a>
          <a href="https://www.facebook.com/profile.php?id=100084891550126#" aria-label="Facebook" target="_blank" rel="noopener noreferrer"><i class="fa fa-facebook"></i></a>
          <a href="https://www.instagram.com/filaoadventures/" aria-label="Instagram" target="_blank" rel="noopener noreferrer"><i class="fa fa-instagram"></i></a>
          <a href="https://www.tiktok.com/@filaoadventures" aria-label="TikTok" target="_blank" rel="noopener noreferrer">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-2px;">
              <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z"/>
            </svg>
          </a>
          <a href="https://ke.linkedin.com/jobs/view/travel-consultant-at-filao-adventures-4398464574" aria-label="LinkedIn" target="_blank" rel="noopener noreferrer"><i class="fa fa-linkedin"></i></a>
          <a href="https://wa.me/254757139239" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer"><i class="fa fa-whatsapp"></i></a>
        </div>

      </div>

      <!-- Destinations -->
      <div class="col-lg-2 col-md-6 mb-5">
        <span class="footer-col-heading">Destinations</span>
        <ul>
          <?php 
          $pdo_footer = getPDO();
          $footerDestinations = $pdo_footer->query("SELECT name FROM destinations ORDER BY name ASC LIMIT 6")->fetchAll(PDO::FETCH_COLUMN);
          foreach ($footerDestinations as $fDest): ?>
          <li><a href="country?name=<?= urlencode($fDest) ?>"><?= htmlspecialchars($fDest) ?></a></li>
          <?php endforeach; ?>
          <li><a href="destinations">View All &rarr;</a></li>
        </ul>
      </div>

      <!-- Safaris & Tours -->
      <div class="col-lg-2 col-md-6 mb-5">
        <span class="footer-col-heading">Safaris &amp; Tours</span>
        <ul>
          <li><a href="tours">All Tours</a></li>
          <?php 
          $footerActs = $pdo_footer->query("SELECT name, slug FROM activities ORDER BY name ASC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
          foreach ($footerActs as $fAct): ?>
          <li><a href="tours?cat[]=<?= urlencode($fAct['slug']) ?>"><?= htmlspecialchars($fAct['name']) ?></a></li>
          <?php endforeach; ?>
          <?php if (empty($footerActs)): ?>
          <li><a href="safaris">Safari Experiences</a></li>
          <li><a href="safaris">Wildlife Safaris</a></li>
          <li><a href="safaris">Beach Holidays</a></li>
          <li><a href="safaris">City Tours</a></li>
          <?php endif; ?>
          <li><a href="blog">Travel Blog</a></li>
        </ul>
      </div>

      <!-- Company -->
      <div class="col-lg-2 col-md-6 mb-5">
        <span class="footer-col-heading">Company</span>
        <ul>
          <li><a href="about">About Us</a></li>

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
      <div style="margin-bottom: 20px; display: flex; justify-content: center; gap: 20px; align-items: center;">
        <a href="https://www.safaribookings.com/p6895" target="_blank" rel="noopener noreferrer">
          <img src="images/Filao/safaribookings.png" alt="Safari Bookings" style="height:40px;opacity:0.9;transition:opacity 0.2s;">
        </a>
        <a href="https://www.tripadvisor.co.za/Attraction_Review-g294207-d24109431-Reviews-FILAO_ADVENTURES-Nairobi.html" target="_blank" rel="noopener noreferrer">
          <img src="images/Filao/tripadvisor.svg" alt="TripAdvisor" style="height:40px;opacity:0.9;transition:opacity 0.2s; filter: brightness(0) invert(1);">
        </a>
      </div>
      <p class="mb-0">
        &copy; <?php echo date('Y'); ?> Filao Adventures. All Rights Reserved. &mdash; Nairobi, Kenya &mdash; Crafting
        Unforgettable Journeys.<br>
        <span style="font-size:12px;opacity:0.7;">Site by <a href="https://mmtechpro.co.ke" target="_blank"
            style="color:inherit;text-decoration:underline;">Millenium Meritum Technology &amp; Procurement</a></span>
      </p>
    </div>
  </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nlForm = document.getElementById('footerNewsletterForm');
    const nlFeedback = document.getElementById('footerNewsletterFeedback');
    
    if (nlForm) {
        nlForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = nlForm.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            btn.innerText = 'Subscribing...';
            btn.disabled = true;
            nlFeedback.style.display = 'none';
            
            const formData = new FormData(nlForm);
            var basePath = window.location.hostname === 'localhost' ? '/filao' : '';
            fetch(basePath + '/handlers/newsletter.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                btn.innerText = originalText;
                btn.disabled = false;
                nlFeedback.style.display = 'block';
                if(res.success) {
                    nlFeedback.style.backgroundColor = 'rgba(98,140,82,0.1)';
                    nlFeedback.style.border = '1px solid #628C52';
                    nlFeedback.style.color = '#fff';
                    nlFeedback.innerHTML = '<i class="fa fa-check-circle mr-2"></i> ' + res.message;
                    nlForm.reset();
                } else {
                    nlFeedback.style.backgroundColor = 'rgba(180,30,30,0.1)';
                    nlFeedback.style.border = '1px solid #b41e1e';
                    nlFeedback.style.color = '#ffcccc';
                    nlFeedback.innerHTML = '<i class="fa fa-exclamation-circle mr-2"></i> ' + res.message;
                }
            })
            .catch(err => {
                btn.innerText = originalText;
                btn.disabled = false;
                nlFeedback.style.display = 'block';
                nlFeedback.style.backgroundColor = 'rgba(180,30,30,0.1)';
                nlFeedback.style.border = '1px solid #b41e1e';
                nlFeedback.style.color = '#ffcccc';
                nlFeedback.innerHTML = '<i class="fa fa-exclamation-circle mr-2"></i> Network error. Please try again.';
            });
        });
    }
});
</script>

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
<?php require_once 'includes/hot-offer-popup.php'; ?>