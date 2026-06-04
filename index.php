<?php
require_once 'includes/db.php';
$pdo = getPDO();

// Fetch published tours
$tours = $pdo->query("SELECT id, title, slug, duration_days, price_from_usd, excerpt, featured_image, status FROM tours WHERE status='published' ORDER BY id ASC LIMIT 6")->fetchAll();

// Fetch destinations with images + tour count
$destinations = $pdo->query("
    SELECT d.id, d.name, d.slug, d.country, d.region_type, d.featured_image,
           COUNT(DISTINCT ist.tour_id) as tour_count
    FROM destinations d
    LEFT JOIN itinerary_steps ist ON ist.destination_id = d.id
    WHERE d.featured_image IS NOT NULL AND d.featured_image != ''
    GROUP BY d.id
    ORDER BY tour_count DESC
    LIMIT 8
")->fetchAll();

// Helper: build route string from itinerary
function getTourRoute($pdo, $tourId) {
    $stmt = $pdo->prepare("SELECT d.name FROM itinerary_steps ist JOIN destinations d ON d.id=ist.destination_id WHERE ist.tour_id=? ORDER BY ist.step_number ASC");
    $stmt->execute([$tourId]);
    $names = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return implode(' → ', $names);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Filao Adventures   Safari Tours, Beach Holidays &amp; City Tours | Kenya</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Filao Adventures is Kenya's premier safari and luxury travel company. Book tailored safaris to Maasai Mara, Amboseli, Serengeti and luxury holidays to Bali, Dubai, Maldives and more.">
  <link rel="icon" type="image/x-icon" href="assets/favicon_io/favicon.ico">
  <link rel="apple-touch-icon" href="assets/favicon_io/apple-touch-icon.png">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/animate.css">
  <link rel="stylesheet" href="css/owl.carousel.min.css">
  <link rel="stylesheet" href="css/owl.theme.default.min.css">
  <link rel="stylesheet" href="css/magnific-popup.css">
  <link rel="stylesheet" href="css/flaticon.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css">
</head>
<body>

<?php require_once 'includes/nav.php'; ?>

<!-- ====== VIDEO HERO ====== -->
<section class="fa-video-hero" id="heroSection">
  <video autoplay muted loop playsinline poster="images/Filao/East Africa/pexels-kelly-17291020.jpg">
    <source src="assets/videos/hero.webm" type="video/webm">
  </video>
  <div class="video-overlay"></div>
  <div class="container fa-video-hero-content text-center" style="max-width:1280px;padding:0 24px;">
    <div class="row justify-content-center">
      <div class="col-lg-10 col-xl-8">
        <div class="fa-hero-accent justify-content-center">
          <div class="fa-hero-accent-line"></div>
          <span class="fa-hero-eyebrow">KENYA'S PREMIER SAFARI PARTNER</span>
          <div class="fa-hero-accent-line"></div>
        </div>
        <h1>Africa's Most<br><strong>Unforgettable Safaris</strong></h1>
        <p class="hero-sub mx-auto">Expertly crafted journeys through Kenya, Tanzania and beyond. Luxury travel tailored entirely for you   from the Maasai Mara to the shores of Zanzibar.</p>
        <form action="tours" method="GET" class="fa-hero-search">
          <div class="fa-search-field">
            <label for="hero-dest">Destination</label>
            <input type="text" id="hero-dest" placeholder="Where do you want to go?">
          </div>
          <div class="fa-search-field">
            <label for="hero-month">Travel Month</label>
            <select id="hero-month">
              <option value="">Any Month</option>
              <option>January</option><option>February</option><option>March</option>
              <option>April</option><option>May</option><option>June</option>
              <option>July</option><option>August</option><option>September</option>
              <option>October</option><option>November</option><option>December</option>
            </select>
          </div>
          <div class="fa-search-field">
            <label for="hero-guests">Guests</label>
            <select id="hero-guests">
              <option>1 Adult</option><option>2 Adults</option><option>3 Adults</option>
              <option>4 Adults</option><option>5+ Adults</option>
            </select>
          </div>
          <div class="d-flex align-items-center">
            <button type="submit" class="btn-filao-cta w-100" style="padding:16px 24px;border-radius:4px;font-size:12px;">
              <i class="fa fa-search mr-2"></i> Search Tours
            </button>
          </div>
        </form>
        
        <div class="fa-hero-perks d-none d-md-flex justify-content-center" style="margin-top:40px;gap:24px;font-size:12px;color:rgba(255,255,255,.9);">
          <span><i class="fa fa-check-circle"></i> Expert Local Guides</span>
          <span><i class="fa fa-check-circle"></i> Eco-Friendly Travel</span>
          <span><i class="fa fa-check-circle"></i> Best Price Guarantee</span>
        </div>
      </div>
    </div>
  </div>
  <div class="hero-scroll-hint" onclick="document.getElementById('heroSection').nextElementSibling.scrollIntoView({behavior:'smooth'})">
    <span>Scroll</span>
    <i class="fa fa-chevron-down"></i>
  </div>
</section>

<!-- ====== WHAT WE OFFER ====== -->
<section class="section-pad bg-white">
  <div class="container" style="max-width:1280px;">
    <div class="row align-items-center">
      <div class="col-lg-4 mb-5 mb-lg-0">
        <div class="fa-section-heading">
          <span class="eyebrow">Our Services</span>
          <h2>Experiences Crafted<br>For You</h2>
          <p>We don't sell tours   we craft journeys. Every Filao adventure begins with understanding your pace, your passions, and your dream.</p>
        </div>
        <a href="tours" class="view-all-link">Explore All Tours <i class="fa fa-arrow-right"></i></a>
      </div>
      <div class="col-lg-8">
        <div class="row">
          <div class="col-md-4 mb-4">
            <div class="services services-1 color-1 d-block img fa-service-card" style="background-image:url('images/Filao/East Africa/pexels-balazsimon-15993990.jpg');">
              <div class="icon d-flex align-items-center justify-content-center"><span class="flaticon-paragliding"></span></div>
              <div class="media-body" style="padding:20px;background:linear-gradient(to top,rgba(0,0,0,.75),transparent);">
                <h3 class="heading mb-2" style="color:#fff;font-family:'Cormorant Garant',serif;font-size:22px;">Safari Tours</h3>
                <p style="color:rgba(255,255,255,.8);font-size:13px;line-height:1.6;">Immersive wildlife safaris to Kenya and Tanzania's most iconic national parks and reserves.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="services services-1 color-2 d-block img fa-service-card" style="background-image:url('images/Filao/Indian Ocean/pexels-asadphoto-9394268.jpg');">
              <div class="icon d-flex align-items-center justify-content-center"><span class="flaticon-route"></span></div>
              <div class="media-body" style="padding:20px;background:linear-gradient(to top,rgba(0,0,0,.75),transparent);">
                <h3 class="heading mb-2" style="color:#fff;font-family:'Cormorant Garant',serif;font-size:22px;">Beach Holidays</h3>
                <p style="color:rgba(255,255,255,.8);font-size:13px;line-height:1.6;">Exclusive retreats to Diani Beach, Zanzibar, Maldives, and Indian Ocean destinations.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="services services-1 color-3 d-block img fa-service-card" style="background-image:url('images/Filao/Dubai/pexels-axp-photography-500641970-16412106.jpg');">
              <div class="icon d-flex align-items-center justify-content-center"><span class="flaticon-tour-guide"></span></div>
              <div class="media-body" style="padding:20px;background:linear-gradient(to top,rgba(0,0,0,.75),transparent);">
                <h3 class="heading mb-2" style="color:#fff;font-family:'Cormorant Garant',serif;font-size:22px;">International Luxury</h3>
                <p style="color:rgba(255,255,255,.8);font-size:13px;line-height:1.6;">Curated escapes to Bali, Dubai, Santorini, Paris and beyond   tailored to your style.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ====== FEATURED TOURS ====== -->
<section class="section-pad bg-cream">
  <div class="container" style="max-width:1280px;">
    <div class="row">
      <div class="col-12">
        <div class="fa-section-heading centered">
          <span class="eyebrow">Our Tours</span>
          <h2>Signature Safari Experiences</h2>
          <p>Each journey is personally crafted   from the first game drive to the final sundowner.</p>
        </div>
      </div>
    </div>
    <div class="row">
      <?php
      $tourExcerpts = [
        "Traverse southern Kenya's most iconic wildlife corridors   encountering elephants beneath Kilimanjaro and big cats in the red dust of Tsavo.",
        "Experience the legendary Amboseli with its vast elephant herds against the backdrop of Africa's highest peak, Mt. Kilimanjaro.",
        "Join a small-group game drive through the grasslands of the Masai Mara   Africa's premier big cat territory and wildebeest home.",
        "Combine two of Kenya's most rewarding parks: the flamingo-lined shores of Lake Nakuru and the big cat paradise of the Masai Mara.",
        "The ultimate Kenya safari   seven days through the Masai Mara, Lake Nakuru, and Amboseli with Kilimanjaro as your backdrop.",
        "A comprehensive East African adventure through Kenya's most celebrated wildlife destinations.",
      ];
      foreach($tours as $idx => $tour):
        $img = $tour['featured_image'] ? 'uploads/'.$tour['featured_image'] : 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg';
        $route = getTourRoute($pdo, $tour['id']);
        $excerpt = $tour['excerpt'] ?: ($tourExcerpts[$idx] ?? 'An expertly guided safari through Kenya\'s most spectacular landscapes and wildlife destinations.');
        $nights = $tour['duration_days'] - 1;
        $price = $tour['price_from_usd'] ? '$'.number_format($tour['price_from_usd']) : 'Contact Us';
      ?>
      <div class="col-lg-4 col-md-6 mb-5 d-flex">
        <div class="fa-tour-card w-100">
          <div class="tc-image-wrap">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($tour['title']) ?>" class="tc-image" loading="lazy">
            <div class="tc-price-badge <?= $tour['price_from_usd'] ? '' : 'contact' ?>">
              <?= $tour['price_from_usd'] ? '$'.number_format($tour['price_from_usd']).'/person' : 'Enquire' ?>
            </div>
            <div class="tc-duration-badge"><?= $nights ?> Nights</div>
          </div>
          <div class="tc-body">
            <div class="tc-country">Kenya &bull; Safari</div>
            <div class="tc-title"><a href="tours/<?= $tour['slug'] ?>"><?= htmlspecialchars($tour['title']) ?></a></div>
            <?php if($route): ?>
            <div class="tc-route"><i class="fa fa-map-marker"></i><?= htmlspecialchars($route) ?></div>
            <?php endif; ?>
            <div class="tc-excerpt"><?= htmlspecialchars(substr(strip_tags($excerpt),0,130)) ?>...</div>
            <div class="tc-footer">
              <div class="tc-price-text">From <strong><?= $price ?></strong></div>
              <a href="tours/<?= $tour['slug'] ?>" class="tc-cta" style="padding:10px 24px; font-size:11px;">View Itinerary</a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-3">
      <a href="tours" class="view-all-link" style="font-size:12px;">View All Tours &rarr;</a>
    </div>
  </div>
</section>

<!-- ====== DESTINATIONS CAROUSEL ====== -->
<section class="section-pad bg-earth">
  <div class="container" style="max-width:1280px;">
    <div class="row">
      <div class="col-12">
        <div class="fa-section-heading white centered">
          <span class="eyebrow">Where We Go</span>
          <h2>Iconic African &amp; Global Destinations</h2>
        </div>
      </div>
    </div>
    <div class="carousel-destination owl-carousel">
      <?php foreach($destinations as $dest):
        if(!$dest['featured_image']) continue;
        $img = $dest['featured_image'];
        if (!str_starts_with($img, 'http') && !str_starts_with($img, 'images/')) {
            $img = 'uploads/' . $img;
        }
      ?>
      <div class="item">
        <a href="destinations/<?= htmlspecialchars($dest['slug']) ?>" class="fa-dest-card">
          <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($dest['name']) ?>" loading="lazy">
          <div class="dc-overlay"></div>
          <span class="dc-country-badge"><?= htmlspecialchars($dest['country']) ?></span>
          <div class="dc-text">
            <div class="dc-region"><?= htmlspecialchars($dest['region_type'] ?? 'Destination') ?></div>
            <div class="dc-name"><?= htmlspecialchars($dest['name']) ?></div>
          </div>
          <?php if ($dest['tour_count'] > 0): ?>
          <div class="dc-tour-count"><?= $dest['tour_count'] ?> Tour<?= $dest['tour_count'] > 1 ? 's' : '' ?></div>
          <?php endif; ?>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ====== STATS BAR ====== -->
<section class="fa-stats-bar" style="border-top:1px solid rgba(255,255,255,.07);">
  <div class="container" style="max-width:1280px;">
    <div class="row text-center">
      <div class="col-md-3 col-6">
        <div class="fa-stat-item">
          <span class="stat-number">500+</span>
          <span class="stat-label">Safaris Delivered</span>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="fa-stat-item">
          <span class="stat-number">15+</span>
          <span class="stat-label">Destinations Covered</span>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="fa-stat-item">
          <span class="stat-number">98%</span>
          <span class="stat-label">Client Satisfaction</span>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="fa-stat-item">
          <span class="stat-number">10+</span>
          <span class="stat-label">Years of Excellence</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ====== WHY CHOOSE FILAO ====== -->
<section class="section-pad bg-white">
  <div class="container" style="max-width:1280px;">
    <div class="row align-items-center">
      <div class="col-lg-5 mb-5 mb-lg-0">
        <div style="overflow:hidden;border-radius:4px;height:520px;">
          <img src="images/Filao/East Africa/pexels-muganineza-arsene-2152761221-34784514.jpg" alt="Filao Adventures Story" style="width:100%;height:100%;object-fit:cover;display:block;">
        </div>
      </div>
      <div class="col-lg-6 offset-lg-1">
        <div class="fa-section-heading">
          <span class="eyebrow">Why Filao</span>
          <h2>Your Safari, Your Way</h2>
        </div>
        <p style="font-size:16px;line-height:1.8;color:#4A4340;margin-bottom:28px;">We are not a booking agent   we are your personal safari curator. Every Filao Adventures journey begins with understanding you: your pace, your passions, your dream. Then we craft something extraordinary around it.</p>
        <div class="mb-4">
          <div class="d-flex align-items-start mb-3">
            <i class="fa fa-leaf mr-3 mt-1" style="color:#628C52;font-size:18px;flex-shrink:0;"></i>
            <div>
              <div style="font-weight:600;font-size:15px;margin-bottom:3px;">Truly Tailor-Made</div>
              <div style="font-size:14px;color:#6B6358;line-height:1.6;">Every detail   the lodge, the guide, the pace   shaped entirely around your wishes.</div>
            </div>
          </div>
          <div class="d-flex align-items-start mb-3">
            <i class="fa fa-star mr-3 mt-1" style="color:#C49018;font-size:18px;flex-shrink:0;"></i>
            <div>
              <div style="font-weight:600;font-size:15px;margin-bottom:3px;">Deep Kenya Expertise</div>
              <div style="font-size:14px;color:#6B6358;line-height:1.6;">Born from the African bush, our team's knowledge comes from years on the ground in Kenya's parks.</div>
            </div>
          </div>
          <div class="d-flex align-items-start mb-3">
            <i class="fa fa-globe mr-3 mt-1" style="color:#9E3A25;font-size:18px;flex-shrink:0;"></i>
            <div>
              <div style="font-weight:600;font-size:15px;margin-bottom:3px;">Sustainable &amp; Responsible</div>
              <div style="font-size:14px;color:#6B6358;line-height:1.6;">We partner only with eco-certified lodges and contribute to community conservation on every safari.</div>
            </div>
          </div>
        </div>
        <a href="#" class="btn-filao-cta">Plan Your Safari</a>
        <a href="about" class="view-all-link ml-4">Our Story &rarr;</a>
      </div>
    </div>
  </div>
</section>

<!-- ====== TESTIMONIALS ====== -->
<section class="section-pad ftco-section testimony-section bg-bottom" style="background-image:url('images/Filao/East Africa/pexels-zacchaeus-rains-262050732-20523197.jpg');background-size:cover;background-position:center;">
  <div class="overlay"></div>
  <div class="container" style="max-width:1280px;position:relative;z-index:2;">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-6 text-center">
        <div class="fa-section-heading white centered">
          <span class="eyebrow">What Travelers Say</span>
          <h2>Stories From The Bush</h2>
        </div>
      </div>
    </div>
    <div class="row carousel-testimony owl-carousel">
      <div class="item">
        <div class="fa-testimonial">
          <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
          <blockquote>"Standing in the Maasai Mara as a herd of over two hundred elephants moved silently through the golden grass   I will never forget that moment. Filao arranged everything perfectly; every lodge, every guide, every transfer. It felt effortless."</blockquote>
          <div class="reviewer-name">Sarah M. 🇬🇧</div>
          <div class="reviewer-origin">London, United Kingdom &bull; Maasai Mara Migration Safari</div>
        </div>
      </div>
      <div class="item">
        <div class="fa-testimonial">
          <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
          <blockquote>"Our honeymoon was everything we dreamed of. Filao combined four nights at a stunning Amboseli lodge with Kilimanjaro views and then three blissful days on Diani Beach. The attention to detail   the champagne sunset, the private dinner   was beyond what we imagined."</blockquote>
          <div class="reviewer-name">James &amp; Linda K. 🇺🇸</div>
          <div class="reviewer-origin">New York, USA &bull; Amboseli &amp; Diani Honeymoon</div>
        </div>
      </div>
      <div class="item">
        <div class="fa-testimonial">
          <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
          <blockquote>"I had heard about Kenya's wildlife all my life but never imagined it would look like this. Filao's team picked me up from Nairobi airport, and within 24 hours I was watching lions hunt at sunset in the Mara. Absolutely world-class service and hospitality."</blockquote>
          <div class="reviewer-name">Ahmed Al-Rashid 🇦🇪</div>
          <div class="reviewer-origin">Dubai, UAE &bull; 5-Day Kenya Safari</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ====== BLOG TEASERS ====== -->
<section class="section-pad bg-cream">
  <div class="container" style="max-width:1280px;">
    <div class="row">
      <div class="col-12">
        <div class="fa-section-heading centered">
          <span class="eyebrow">From the Field</span>
          <h2>Safari Stories &amp; Travel Guides</h2>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-4 col-md-6 mb-5 d-flex">
        <div class="fa-blog-card w-100">
          <div class="bc-image-wrap"><img src="images/Filao/East Africa/Maasai Mara/free-photo-of-wildebeest-grazing-in-the-kenyan-savannah (6).jpeg" alt="Maasai Mara Migration" class="bc-image" loading="lazy"></div>
          <div class="bc-meta">
            <span class="bc-date">March 2025</span>
            <span class="bc-cat">Safari Guide</span>
          </div>
          <div class="bc-title"><a href="blog.php">Best Time to Visit the Maasai Mara: A Month-by-Month Guide</a></div>
          <div class="bc-excerpt">The Maasai Mara is spectacular year-round, but timing your visit around the Great Migration   when 1.5 million wildebeest cross the Mara River   creates once-in-a-lifetime moments.</div>
          <a href="blog.php" class="bc-link">Read More <i class="fa fa-arrow-right"></i></a>
        </div>
      </div>
      <div class="col-lg-4 col-md-6 mb-5 d-flex">
        <div class="fa-blog-card w-100">
          <div class="bc-image-wrap"><img src="images/Filao/East Africa/Amboseli/Sarova-Shaba-Safari-breakfast-in-the-wild.jpg" alt="Amboseli Safari" class="bc-image" loading="lazy"></div>
          <div class="bc-meta">
            <span class="bc-date">January 2025</span>
            <span class="bc-cat">Comparison</span>
          </div>
          <div class="bc-title"><a href="blog.php">Amboseli vs Maasai Mara: Which Safari Park is Right for You?</a></div>
          <div class="bc-excerpt">Both parks offer extraordinary wildlife experiences, but they each have a distinctly different character. Here is how to decide which one belongs on your Kenya itinerary.</div>
          <a href="blog.php" class="bc-link">Read More <i class="fa fa-arrow-right"></i></a>
        </div>
      </div>
      <div class="col-lg-4 col-md-6 mb-5 d-flex">
        <div class="fa-blog-card w-100">
          <div class="bc-image-wrap"><img src="images/Filao/East Africa/pexels-droneafrica-13234382.jpg" alt="Tailor Made Safari" class="bc-image" loading="lazy"></div>
          <div class="bc-meta">
            <span class="bc-date">February 2025</span>
            <span class="bc-cat">Travel Tips</span>
          </div>
          <div class="bc-title"><a href="blog.php">5 Reasons to Choose a Tailor-Made Safari Over a Group Tour</a></div>
          <div class="bc-excerpt">When you choose a tailor-made safari with Filao Adventures, every detail   from the lodge to the guide, the pace, and the route   is shaped entirely around you.</div>
          <a href="blog.php" class="bc-link">Read More <i class="fa fa-arrow-right"></i></a>
        </div>
      </div>
    </div>
    <div class="text-center"><a href="blog.php" class="view-all-link">View All Articles &rarr;</a></div>
  </div>
</section>

<!-- ====== CTA BANNER ====== -->
<section class="fa-cta-banner" style="background-image:url('images/Filao/East Africa/pexels-mwauraken-29093739.jpg');">
  <div class="overlay"></div>
  <div class="container fa-cta-content" style="max-width:1280px;">
    <h2>Ready to Start Your Safari?</h2>
    <p>Speak with a Filao Adventures specialist and let us craft your perfect journey   from the first sunrise game drive to the final sundowner.</p>
    <a href="#" class="btn-filao-cta" data-open-planner="true" style="font-size:12px;padding:14px 32px;">Start Planning &rarr;</a>
    <a href="contact.php" class="view-all-link" style="color:rgba(255,255,255,.8);border-color:rgba(255,255,255,.5);margin-left:20px;">Contact Us &rarr;</a>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<!-- loader -->
<div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#C49018"/></svg></div>

<script src="js/jquery.min.js"></script>
<script src="js/jquery-migrate-3.0.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.easing.1.3.js"></script>
<script src="js/jquery.waypoints.min.js"></script>
<script src="js/jquery.stellar.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/jquery.magnific-popup.min.js"></script>
<script src="js/jquery.animateNumber.min.js"></script>
<script src="js/scrollax.min.js"></script>
<script src="assets/js/filao-nav.js"></script>
<script src="js/main.js"></script>
<script>
$(document).ready(function(){
  // Destinations carousel
  $('.carousel-destination').owlCarousel({
    loop:true, autoplay:true, autoplayTimeout:3800, autoplayHoverPause:true,
    margin:16, nav:true, dots:false,
    navText:['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
    responsive:{0:{items:1},600:{items:2},900:{items:3},1200:{items:4}}
  });
  // Testimonials
  $('.carousel-testimony').owlCarousel({
    loop:true, margin:24, nav:false, dots:true, autoplay:true, autoplayTimeout:5000,
    responsive:{0:{items:1},768:{items:2}}
  });
});
</script>
<script src="js/start-planning.js"></script>
</body>
</html>

