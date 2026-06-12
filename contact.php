<?php
require_once 'includes/db.php';
$pdo = getPDO();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Contact Filao Adventures &mdash; Plan Your Safari</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Get in touch with Filao Adventures. Our safari specialists are ready to craft your perfect African journey.">
  <link rel="icon" href="assets/favicon_io/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css">
  <style>
    .contact-form-box { background:#fff;padding:40px;border-radius:4px;box-shadow:0 8px 32px rgba(0,0,0,0.05);border-top:3px solid #C49018; }
    .contact-form-box .form-control { border:1px solid #E5DDD0;border-radius:3px;font-family:'Inter',sans-serif;font-size:14px;padding:12px 16px;color:#1C1712;background:#FAF8F4;margin-bottom:20px; }
    .contact-form-box .form-control:focus { border-color:#C49018;box-shadow:0 0 0 3px rgba(196,144,24,.14);outline:none;background:#fff; }
    .contact-form-box label { font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;margin-bottom:6px; }
    .contact-info-box { padding:32px 0; }
    .contact-item { display:flex;gap:16px;margin-bottom:24px; }
    .contact-item .icon { width:48px;height:48px;background:#FAF8F4;border:1px solid #E5DDD0;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .contact-item .icon i { color:#C49018;font-size:18px; }
    .contact-item .text h4 { font-family:'Cormorant Garant',serif;font-size:20px;color:#1C1712;margin-bottom:4px; }
    .contact-item .text p { font-size:14.5px;color:#6B6358;margin:0;line-height:1.6; }
    .contact-item .text a { color:#6B6358;text-decoration:none;transition:color 0.15s; }
    .contact-item .text a:hover { color:#C49018; }
    #contactFeedback { display:none; }
  </style>
</head>
<body>
<?php require_once 'includes/nav.php'; ?>

<section class="fa-page-hero" style="background-image:url('images/Filao/Company/tourist behind tour vehicle.jpeg');">
  <div class="overlay"></div>
  <div class="container fa-page-hero-content" style="max-width:1280px;text-align:center;">
    <h1>Get In Touch</h1>
    <div class="breadcrumb-fa justify-content-center">
      <a href="index">Home</a>
      <span class="bc-sep">&#8250;</span>
      <span class="bc-current">Contact Us</span>
    </div>
  </div>
</section>

<section class="section-pad bg-cream">
  <div class="container" style="max-width:1280px;text-align:center;">
    
    <div class="fa-section-heading centered" style="margin:0 auto 48px;">
      <span class="eyebrow">Plan Your Safari</span>
      <h2>Let's Craft Your Journey</h2>
      <p>Whether you're ready to book or just starting to dream, our safari specialists are here to help you design the perfect itinerary.</p>
    </div>

    <div class="contact-info-grid" style="text-align:left; background:#fff; padding:40px; border-radius:8px; border:1px solid #E5DDD0;">
      <h3 style="font-family:'Cormorant Garant',serif;font-size:28px;color:#1C1712;margin-bottom:32px;text-align:center;">Contact Information</h3>
      
      <div class="row">
        <div class="col-md-6 mb-4">
          <div class="contact-item" style="height:100%; padding:24px; border:1px solid #FAF8F4; border-radius:8px; background:#FAF8F4;">
            <div class="icon"><i class="fa fa-map-marker"></i></div>
            <div class="text">
              <h4>Our Office</h4>
              <p>Ambank House, Nairobi, Kenya<br>East Africa</p>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 mb-4">
          <div class="contact-item" style="height:100%; padding:24px; border:1px solid #FAF8F4; border-radius:8px; background:#FAF8F4;">
            <div class="icon"><i class="fa fa-phone"></i></div>
            <div class="text">
              <h4>Call Us</h4>
              <p><a href="tel:+254757139239">+254 757 139239</a></p>
              <p style="font-size:12px;margin-top:4px;">Mon - Fri: 8am to 6pm (EAT)</p>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 mb-4">
          <div class="contact-item" style="height:100%; padding:24px; border:1px solid #FAF8F4; border-radius:8px; background:#FAF8F4;">
            <div class="icon"><i class="fa fa-envelope"></i></div>
            <div class="text">
              <h4>Email Us</h4>
              <p><a href="mailto:info@filaoadventures.co.ke">info@filaoadventures.co.ke</a></p>
            </div>
          </div>
        </div>

        <div class="col-md-6 mb-4">
          <div class="contact-item" style="height:100%; padding:24px; border:1px solid #FAF8F4; border-radius:8px; background:#FAF8F4;">
            <div class="icon"><i class="fa fa-whatsapp"></i></div>
            <div class="text">
              <h4>WhatsApp</h4>
              <p><a href="https://wa.me/254757139239" target="_blank">Chat with a Specialist</a></p>
            </div>
          </div>
        </div>
      </div>

      <div style="margin-top:20px;border-top:1px solid #E5DDD0;padding-top:32px;text-align:center;">
        <h4 style="font-family:'Inter',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#C49018;margin-bottom:16px;">Connect With Us</h4>
        <div style="display:flex;gap:12px;justify-content:center;">
          <a href="https://www.facebook.com/profile.php?id=100084891550126#" style="width:40px;height:40px;background:#fff;border:1px solid #E5DDD0;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#6B6358;transition:all 0.2s;"><i class="fa fa-facebook"></i></a>
          <a href="https://www.instagram.com/filaoadventures/" style="width:40px;height:40px;background:#fff;border:1px solid #E5DDD0;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#6B6358;transition:all 0.2s;"><i class="fa fa-instagram"></i></a>
          <a href="https://www.tiktok.com/@filaoadventures" style="width:40px;height:40px;background:#fff;border:1px solid #E5DDD0;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#6B6358;transition:all 0.2s;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
              <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z"/>
            </svg>
          </a>
          <a href="https://ke.linkedin.com/jobs/view/travel-consultant-at-filao-adventures-4398464574" style="width:40px;height:40px;background:#fff;border:1px solid #E5DDD0;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#6B6358;transition:all 0.2s;"><i class="fa fa-linkedin"></i></a>
          <a href="#" style="width:40px;height:40px;background:#fff;border:1px solid #E5DDD0;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#6B6358;transition:all 0.2s;"><i class="fa fa-youtube"></i></a>
        </div>
      </div>
      
    </div>
  </div>
</section>

<!-- Map Section -->
<section style="height:450px;width:100%;background:#e5e5e5;position:relative;">
  <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.819806677223!2d36.816283899999995!3d-1.2818793!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f10d2584a97d9%3A0xabe14f09fb8a34a0!2sAmbank%20House!5e0!3m2!1sen!2ske!4v1780610115137!5m2!1sen!2ske" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
</section>

<?php require_once 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="assets/js/filao-nav.js"></script>
<script src="js/start-planning.js"></script>
<script>
</script>
</body>
</html>
