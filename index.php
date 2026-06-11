<?php
require_once 'includes/db.php';
$pdo = getPDO();

// Fetch published tours
$tours = $pdo->query("SELECT id, title, slug, duration_days, price_from_usd, excerpt, featured_image, status FROM tours WHERE status='published' ORDER BY id ASC LIMIT 6")->fetchAll();

// Fetch hot offers
$hotOffers = [];
try {
    $hotOffers = $pdo->query("SELECT id, title, slug, duration_days, price_from_usd, excerpt, featured_image FROM tours WHERE status='published' AND is_hot_offer = 1 ORDER BY id DESC LIMIT 4")->fetchAll();
} catch (Exception $e) {}

// Fetch Countries with images + tour count
$countries = $pdo->query("
    SELECT d.country, MIN(d.featured_image) as featured_image, COUNT(DISTINCT ist.tour_id) as tour_count
    FROM destinations d
    LEFT JOIN itinerary_steps ist ON ist.destination_id = d.id
    WHERE d.featured_image IS NOT NULL AND d.featured_image != ''
    GROUP BY d.country
    ORDER BY tour_count DESC
    LIMIT 8
")->fetchAll();

// Helper: build route string from itinerary
function getTourRoute($pdo, $tourId)
{
  $stmt = $pdo->prepare("SELECT d.name FROM itinerary_steps ist JOIN destinations d ON d.id=ist.destination_id WHERE ist.tour_id=? ORDER BY ist.step_number ASC");
  $stmt->execute([$tourId]);
  $names = $stmt->fetchAll(PDO::FETCH_COLUMN);
  return implode(' → ', $names);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Filao Adventures Safari Tours, Beach Holidays &amp; City Tours | Kenya</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description"
    content="Filao Adventures is Kenya's premier safari and luxury travel company. Book tailored safaris to Maasai Mara, Amboseli, Serengeti and luxury holidays to Bali, Dubai, Maldives and more.">
  <link rel="icon" type="image/x-icon" href="assets/favicon_io/favicon.ico">
  <link rel="apple-touch-icon" href="assets/favicon_io/apple-touch-icon.png">
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/animate.css">
  <link rel="stylesheet" href="css/owl.carousel.min.css">
  <link rel="stylesheet" href="css/owl.theme.default.min.css">
  <link rel="stylesheet" href="css/magnific-popup.css">
  <link rel="stylesheet" href="css/flaticon.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css?v=<?= time() ?>">
</head>

<body>

  <?php require_once 'includes/nav.php'; ?>

  <!-- ====== VIDEO/SLIDER HERO =========== -->
  <style>
    .hero-slide-text {
      position: absolute;
      bottom: 120px;
      left: 50%;
      transform: translateX(-50%);
      text-align: center;
      width: 100%;
      z-index: 2;
    }
    .fa-video-hero .carousel-indicators {
      position: absolute;
      bottom: 80px;
      top: auto;
      margin-bottom: 0;
      z-index: 10;
    }
  </style>
  <section class="fa-video-hero" id="heroSection" style="position: relative; z-index: 10; overflow: visible;">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-ride="carousel" data-interval="6000" style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:0; overflow:hidden;">
      
      <div class="carousel-inner" style="height:100%;">
        <div class="carousel-item active" style="height:100%;">
          <img src="images/Filao/Hero/rhino.jpg" class="hero-zoom-anim" style="object-fit:cover;width:100%;height:100%;" alt="Rhino">
          <div class="hero-slide-text">
            <div class="fa-hero-accent justify-content-center">
              <span class="fa-hero-eyebrow" style="color:#E0E0E0; font-weight:700;">KENYA'S PREMIER SAFARI</span>
            </div>
            <h1 style="margin-bottom: 15px; font-size: 3.5rem; font-weight: 900; color: #F0F0F0 !important;">Witness the Giants</h1>
            <p style="font-size: 16px; color: #E0E0E0; font-weight:600; max-width: 600px; margin: 0 auto;">Encounter the majestic rhino in its untouched natural habitat.</p>
          </div>
        </div>
        <div class="carousel-item" style="height:100%;">
          <img src="images/Filao/Hero/cheetah.jpg" class="hero-zoom-out-anim" style="object-fit:cover;width:100%;height:100%;" alt="Cheetah">
          <div class="hero-slide-text">
            <div class="fa-hero-accent justify-content-center">
              <span class="fa-hero-eyebrow" style="color:#E0E0E0; font-weight:700;">UNRIVALED ELEGANCE</span>
            </div>
            <h1 style="margin-bottom: 15px; font-size: 3.5rem; font-weight: 900; color: #F0F0F0 !important;">The Thrill of the Chase</h1>
            <p style="font-size: 16px; color: #E0E0E0; font-weight:600; max-width: 600px; margin: 0 auto;">Experience the breathtaking speed of the African cheetah.</p>
          </div>
        </div>
        <div class="carousel-item" style="height:100%;">
          <img src="images/Filao/Hero/lion.jpg" class="hero-zoom-anim" style="object-fit:cover;width:100%;height:100%;" alt="Lion">
          <div class="hero-slide-text">
            <div class="fa-hero-accent justify-content-center">
              <span class="fa-hero-eyebrow" style="color:#E0E0E0; font-weight:700;">HEART OF THE SAVANNAH</span>
            </div>
            <h1 style="margin-bottom: 15px; font-size: 3.5rem; font-weight: 900; color: #F0F0F0 !important;">Realm of the Kings</h1>
            <p style="font-size: 16px; color: #E0E0E0; font-weight:600; max-width: 600px; margin: 0 auto;">Come face to face with the legendary lions of the Mara.</p>
          </div>
        </div>
        <div class="carousel-item" style="height:100%;">
          <img src="images/Filao/Hero/elephant.jpg" class="hero-zoom-out-anim" style="object-fit:cover;width:100%;height:100%;" alt="Elephant">
          <div class="hero-slide-text">
            <div class="fa-hero-accent justify-content-center">
              <span class="fa-hero-eyebrow" style="color:#E0E0E0; font-weight:700;">GENTLE GIANTS</span>
            </div>
            <h1 style="margin-bottom: 15px; font-size: 3.5rem; font-weight: 900; color: #F0F0F0 !important;">Timeless Journeys</h1>
            <p style="font-size: 16px; color: #E0E0E0; font-weight:600; max-width: 600px; margin: 0 auto;">Walk alongside the colossal elephant herds of Amboseli.</p>
          </div>
        </div>
      </div>

      <ol class="carousel-indicators">
        <li data-target="#heroCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#heroCarousel" data-slide-to="1"></li>
        <li data-target="#heroCarousel" data-slide-to="2"></li>
        <li data-target="#heroCarousel" data-slide-to="3"></li>
      </ol>

      <a class="carousel-control-prev" href="#heroCarousel" role="button" data-slide="prev" style="z-index:10; width: 5%; left: 5%; background: none; border: none; opacity: 0.8; font-size: 1.8rem; color: #fff; text-decoration: none; display: flex; align-items: center; justify-content: center;">
        <i class="fa fa-arrow-left" aria-hidden="true" style="text-shadow: 0 2px 5px rgba(0,0,0,0.5);"></i>
        <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" href="#heroCarousel" role="button" data-slide="next" style="z-index:10; width: 5%; right: 5%; background: none; border: none; opacity: 0.8; font-size: 1.8rem; color: #fff; text-decoration: none; display: flex; align-items: center; justify-content: center;">
        <i class="fa fa-arrow-right" aria-hidden="true" style="text-shadow: 0 2px 5px rgba(0,0,0,0.5);"></i>
        <span class="sr-only">Next</span>
      </a>
    </div>
    <div class="video-overlay" style="z-index:1;background:rgba(0,0,0,0.1); pointer-events: none;"></div>

    <!-- Search Bar aligned lower center -->
    <div class="container fa-video-hero-content" style="max-width:1280px;padding:0 24px; position:absolute; bottom: -40px; left: 50%; transform: translateX(-50%); z-index:20; width: 100%;">
      <div class="row justify-content-center w-100 mx-0">
        <div class="col-lg-10 col-xl-9 text-center mx-auto">
          
          <form action="tours" method="GET" class="fa-hero-search" style="opacity: 1; margin: 0 auto; max-width: 900px; text-align: left; background: #ffffff; border-radius: 8px; padding: 6px; position: relative; z-index: 10; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
            <div style="display:flex; align-items:center; flex-wrap:nowrap; width: 100%;">
              
              <div class="fa-search-field" style="flex: 2; position: relative; padding: 10px 20px; border-right: 1px solid rgba(0,0,0,0.08);">
                <label for="hero-live-search" style="color: #6B6358; font-size:10px; font-weight:700; letter-spacing: 0.1em; text-transform:uppercase; margin-bottom:2px; display:block;">DESTINATION</label>
                <input type="text" name="dest" id="hero-live-search" placeholder="Where do you want to go?" style="width: 100%; padding: 4px 0; border: none; outline: none; background:transparent; font-size:15px; color:#1C1712;">
                <div id="hero-search-results" class="ajax-search-results-dropdown d-none" style="position: absolute; top: 100%; left: 0; width: 100%; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-radius: 4px; z-index: 100; max-height: 300px; overflow-y: auto; text-align: left;"></div>
              </div>
              
              <div class="fa-search-field" style="flex: 1.5; padding: 10px 20px; border-right: 1px solid rgba(0,0,0,0.08);">
                <label style="color: #6B6358; font-size:10px; font-weight:700; letter-spacing: 0.1em; text-transform:uppercase; margin-bottom:2px; display:block;">TRAVEL MONTH</label>
                <select name="month" style="width: 100%; padding: 4px 0; border: none; outline: none; background:transparent; font-size:15px; color:#1C1712; cursor:pointer; -webkit-appearance:none; -moz-appearance:none; appearance:none; background-image:url('data:image/svg+xml;utf8,<svg fill=%22%231C1712%22 height=%2224%22 viewBox=%220 0 24 24%22 width=%2224%22 xmlns=%22http://www.w3.org/2000/svg%22><path d=%22M7 10l5 5 5-5z%22/><path d=%22M0 0h24v24H0z%22 fill=%22none%22/></svg>'); background-repeat:no-repeat; background-position-x:100%; background-position-y:5px;">
                  <option value="">Any Month</option>
                  <option value="january">January</option>
                  <option value="february">February</option>
                  <option value="march">March</option>
                  <option value="april">April</option>
                  <option value="may">May</option>
                  <option value="june">June</option>
                  <option value="july">July</option>
                  <option value="august">August</option>
                  <option value="september">September</option>
                  <option value="october">October</option>
                  <option value="november">November</option>
                  <option value="december">December</option>
                </select>
              </div>

              <div class="fa-search-field" style="flex: 1.5; padding: 10px 20px;">
                <label style="color: #6B6358; font-size:10px; font-weight:700; letter-spacing: 0.1em; text-transform:uppercase; margin-bottom:2px; display:block;">GUESTS</label>
                <select name="guests" style="width: 100%; padding: 4px 0; border: none; outline: none; background:transparent; font-size:15px; color:#1C1712; cursor:pointer; -webkit-appearance:none; -moz-appearance:none; appearance:none; background-image:url('data:image/svg+xml;utf8,<svg fill=%22%231C1712%22 height=%2224%22 viewBox=%220 0 24 24%22 width=%2224%22 xmlns=%22http://www.w3.org/2000/svg%22><path d=%22M7 10l5 5 5-5z%22/><path d=%22M0 0h24v24H0z%22 fill=%22none%22/></svg>'); background-repeat:no-repeat; background-position-x:100%; background-position-y:5px;">
                  <option value="1">1 Adult</option>
                  <option value="2">2 Adults</option>
                  <option value="3">3 Adults</option>
                  <option value="4">4 Adults</option>
                  <option value="5">5+ Adults</option>
                </select>
              </div>

              <div style="flex: 0 0 auto; padding: 0 4px;">
                <button type="submit" class="btn" style="background:#628C52; color:#fff; padding:15px 24px; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; border-radius:4px; border:none; display:flex; align-items:center; cursor:pointer;"><i class="fa fa-search" style="margin-right:8px; font-size:14px;"></i> SEARCH TOURS</button>
              </div>
              
            </div>
          </form>

        </div>
      </div>
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
            <p>We don't sell tours we craft journeys. Every Filao adventure begins with understanding your pace, your
              passions, and your dream.</p>
          </div>
          <a href="tours" class="view-all-link">Explore All Tours <i class="fa fa-arrow-right"></i></a>
        </div>
        <div class="col-lg-8">
          <div class="row">
            <div class="col-md-4 mb-4">
              <div class="services services-1 color-1 d-block img fa-service-card"
                style="background-image:url('images/Filao/East Africa/pexels-balazsimon-15993990.jpg');">
                <div class="icon d-flex align-items-center justify-content-center" style="background-color: #C49018;"><span
                    class="flaticon-paragliding" style="color: #fff;"></span></div>
                <div class="media-body"
                  style="padding:20px;background:linear-gradient(to top,rgba(0,0,0,.75),transparent);">
                  <h3 class="heading mb-2" style="color:#fff;font-family:'Cormorant Garant',serif;font-size:22px;">
                    Safari Tours</h3>
                  <p style="color:rgba(255,255,255,.8);font-size:13px;line-height:1.6;">Immersive wildlife safaris to
                    Kenya and Tanzania's most iconic national parks and reserves.</p>
                </div>
              </div>
            </div>
            <div class="col-md-4 mb-4">
              <div class="services services-1 color-2 d-block img fa-service-card"
                style="background-image:url('images/Filao/Indian Ocean/pexels-asadphoto-9394268.jpg');">
                <div class="icon d-flex align-items-center justify-content-center" style="background-color: #C49018;"><span class="flaticon-route" style="color: #fff;"></span>
                </div>
                <div class="media-body"
                  style="padding:20px;background:linear-gradient(to top,rgba(0,0,0,.75),transparent);">
                  <h3 class="heading mb-2" style="color:#fff;font-family:'Cormorant Garant',serif;font-size:22px;">Beach
                    Holidays</h3>
                  <p style="color:rgba(255,255,255,.8);font-size:13px;line-height:1.6;">Exclusive retreats to Diani
                    Beach, Zanzibar, Maldives, and Indian Ocean destinations.</p>
                </div>
              </div>
            </div>
            <div class="col-md-4 mb-4">
              <div class="services services-1 color-3 d-block img fa-service-card"
                style="background-image:url('images/Filao/Dubai/pexels-axp-photography-500641970-16412106.jpg');">
                <div class="icon d-flex align-items-center justify-content-center" style="background-color: #C49018;"><span
                    class="flaticon-tour-guide" style="color: #fff;"></span></div>
                <div class="media-body"
                  style="padding:20px;background:linear-gradient(to top,rgba(0,0,0,.75),transparent);">
                  <h3 class="heading mb-2" style="color:#fff;font-family:'Cormorant Garant',serif;font-size:22px;">
                    International Luxury</h3>
                  <p style="color:rgba(255,255,255,.8);font-size:13px;line-height:1.6;">Curated escapes to Bali, Dubai,
                    Santorini, Paris and beyond tailored to your style.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ====== HOT OFFERS ====== -->
  <?php if (!empty($hotOffers)): ?>
  <section class="hot-sale-section" id="hotSaleSection" style="position: relative; overflow: hidden; background-color: #1C1712; color: #fff; padding: 100px 0; margin-bottom: 0;">
    <!-- Background Images Container -->
    <div id="hsBackgrounds" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0;">
      <?php foreach ($hotOffers as $index => $offer): 
        $img = $offer['featured_image'] ? 'uploads/' . $offer['featured_image'] : 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg';
      ?>
        <div class="hs-bg hs-bg-<?= $index ?>" style="position: absolute; top:0; left:0; width: 100%; height: 100%; background-image: url('<?= htmlspecialchars($img) ?>'); background-size: cover; background-position: center; opacity: <?= $index === 0 ? 1 : 0 ?>; transition: opacity 0.8s ease;"></div>
      <?php endforeach; ?>
      <!-- Gradient Overlay -->
      <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to right, rgba(28,23,18,0.5) 0%, rgba(28,23,18,0.85) 45%, #1C1712 65%, #1C1712 100%); z-index: 1;"></div>
    </div>

    <div class="container" style="max-width:1280px; position: relative; z-index: 2;">
      <div class="row align-items-center">
        <!-- LHS Content -->
        <div class="col-lg-5 mb-5 mb-lg-0 pr-lg-5" id="hsContentWrapper">
          <span style="display:inline-block; color: #E21B1B; font-family:'Inter', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 10px;"><i class="fa fa-fire"></i> Hot Sale Deals</span>
          
          <div id="hsTextContainer" style="margin-top: 10px; position: relative; min-height: 380px;">
            <?php foreach ($hotOffers as $index => $offer): ?>
              <div class="hs-text hs-text-<?= $index ?>" style="position: absolute; top:0; left:0; width: 100%; opacity: <?= $index === 0 ? 1 : 0 ?>; visibility: <?= $index === 0 ? 'visible' : 'hidden' ?>; transition: all 0.5s ease; transform: translateY(<?= $index === 0 ? '0' : '20px' ?>);">
                <h2 style="font-family:'Cormorant Garant',serif; font-size:48px; font-weight:700; color:#fff; line-height:1.1; margin-bottom: 20px; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?= htmlspecialchars($offer['title']) ?></h2>
                <p style="font-size:16px; color:rgba(255,255,255,0.85); line-height:1.7; margin-bottom: 30px;">
                  Experience the magic of this destination. Book now to enjoy exclusive discounts on this unforgettable journey and create memories that will last a lifetime.
                </p>
                <div class="d-flex align-items-center">
                  <div style="margin-right: 40px;">
                    <span style="font-size:11px; color:rgba(255,255,255,0.6); text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:4px; font-weight:600;">Duration</span>
                    <span style="font-size:20px; font-weight:700; color:#fff;"><i class="fa fa-clock-o" style="color:#C49018; margin-right:5px;"></i> <?= htmlspecialchars($offer['duration_days']) ?> Days</span>
                  </div>
                  <div>
                    <span style="font-size:11px; color:rgba(255,255,255,0.6); text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:4px; font-weight:600;">Starting From</span>
                    <span style="font-size:26px; font-weight:700; color:#C49018;">$<?= number_format($offer['price_from_usd']) ?></span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          
          <div class="hs-controls d-flex align-items-center" style="position: relative; z-index: 10; margin-top: 20px;">
            <button id="hsPrev" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.3); color:#fff; width:48px; height:48px; border-radius:50%; margin-right:15px; cursor:pointer; transition:all 0.3s; z-index:10;"><i class="fa fa-arrow-left"></i></button>
            <button id="hsNext" style="background:#E21B1B; border:1px solid #E21B1B; color:#fff; width:48px; height:48px; border-radius:50%; cursor:pointer; transition:all 0.3s; z-index:10;"><i class="fa fa-arrow-right"></i></button>
          </div>
        </div>
        
        <!-- RHS Cards -->
        <div class="col-lg-7">
          <div style="position: relative; width: 100%; height: 500px; overflow: hidden; perspective: 1000px;">
            <?php foreach ($hotOffers as $index => $offer): 
              $img = $offer['featured_image'] ? 'uploads/' . $offer['featured_image'] : 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg';
            ?>
              <div class="hs-card hs-card-<?= $index ?>" data-index="<?= $index ?>" style="position: absolute; top: 50%; left: 0; width: 340px; height: 420px; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.4); transition: all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1); cursor:pointer;">
                <img src="<?= htmlspecialchars($img) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="">
                <div style="position: absolute; bottom:0; left:0; width:100%; padding:25px; background: linear-gradient(to top, rgba(0,0,0,0.95), transparent);">
                  <span style="background: #E21B1B; color: #fff; font-size: 11px; font-weight:700; padding: 4px 10px; border-radius: 4px; text-transform:uppercase; margin-bottom: 12px; display:inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">Hot Deal</span>
                  <h4 style="color:#fff; font-family:'Inter',sans-serif; font-weight:700; font-size:22px; line-height:1.2; margin-bottom:15px; text-shadow: 0 2px 4px rgba(0,0,0,0.6);"><?= htmlspecialchars($offer['title']) ?></h4>
                  <a href="tours/<?= $offer['slug'] ?>" class="btn btn-sm" style="background:#C49018; color:#fff; border-radius:30px; font-weight:600; padding:8px 24px; text-transform:uppercase; font-size:13px; letter-spacing:1px;">View Deal <i class="fa fa-arrow-right ml-1"></i></a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const totalItems = <?= count($hotOffers) ?>;
    if (totalItems === 0) return;
    
    let currentIndex = 0;
    let interval;

    function updateSlider(index) {
      // Backgrounds
      document.querySelectorAll('.hs-bg').forEach((bg, i) => {
        bg.style.opacity = (i === index) ? '1' : '0';
      });
      
      // Texts
      document.querySelectorAll('.hs-text').forEach((txt, i) => {
        if (i === index) {
          txt.style.opacity = '1';
          txt.style.visibility = 'visible';
          txt.style.transform = 'translateY(0)';
        } else {
          txt.style.opacity = '0';
          txt.style.visibility = 'hidden';
          txt.style.transform = 'translateY(20px)';
        }
      });

      // Cards (Active is on left, Next is on right but scaled down, others hidden)
      document.querySelectorAll('.hs-card').forEach((card, i) => {
        if (i === index) {
          // Active card
          card.style.opacity = '1';
          card.style.transform = 'translateY(-50%) translateX(20px) scale(1)';
          card.style.zIndex = '3';
          card.style.pointerEvents = 'auto';
        } else if (i === (index + 1) % totalItems) {
          // Next card preview
          card.style.opacity = '0.5';
          card.style.transform = 'translateY(-50%) translateX(380px) scale(0.85)';
          card.style.zIndex = '2';
          card.style.pointerEvents = 'none';
        } else {
          // Hidden cards
          card.style.opacity = '0';
          card.style.transform = 'translateY(-50%) translateX(450px) scale(0.7)';
          card.style.zIndex = '1';
          card.style.pointerEvents = 'none';
        }
      });
    }

    function nextSlide() {
      currentIndex = (currentIndex + 1) % totalItems;
      updateSlider(currentIndex);
    }

    function prevSlide() {
      currentIndex = (currentIndex - 1 + totalItems) % totalItems;
      updateSlider(currentIndex);
    }

    const nextBtn = document.getElementById('hsNext');
    const prevBtn = document.getElementById('hsPrev');

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        nextSlide();
        resetInterval();
      });
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        prevSlide();
        resetInterval();
      });
    }

    function resetInterval() {
      clearInterval(interval);
      interval = setInterval(nextSlide, 6000);
    }

    // Initialize
    updateSlider(0);
    resetInterval();
  });
  </script>
  <style>
    #hsPrev:hover { background: rgba(255,255,255,0.2) !important; }
    #hsNext:hover { background: #d31a1a !important; }
  </style>
  <?php endif; ?>

  <!-- ====== FEATURED TOURS ====== -->
  <section class="section-pad bg-cream">
    <div class="container" style="max-width:1280px;">
      <div class="row">
        <div class="col-12">
          <div class="fa-section-heading centered">
            <span class="eyebrow">Our Tours</span>
            <h2>Signature Safari Experiences</h2>
            <p>Each journey is personally crafted from the first game drive to the final sundowner.</p>
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
        foreach ($tours as $idx => $tour):
          $img = $tour['featured_image'] ? 'uploads/' . $tour['featured_image'] : 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg';
          $route = getTourRoute($pdo, $tour['id']);
          $excerpt = $tour['excerpt'] ?: ($tourExcerpts[$idx] ?? 'An expertly guided safari through Kenya\'s most spectacular landscapes and wildlife destinations.');
          $nights = $tour['duration_days'] - 1;
          $price = $tour['price_from_usd'] ? '$' . number_format($tour['price_from_usd']) : 'Contact Us';
          ?>
          <div class="col-lg-4 col-md-6 mb-5 d-flex">
            <div class="fa-tour-card w-100">
              <div class="tc-image-wrap">
                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($tour['title']) ?>" class="tc-image"
                  loading="lazy">
                <div class="tc-price-badge <?= $tour['price_from_usd'] ? '' : 'contact' ?>">
                  <?= $tour['price_from_usd'] ? '$' . number_format($tour['price_from_usd']) . '/person' : 'Enquire' ?>
                </div>
                <div class="tc-duration-badge"><?= $nights ?> Nights</div>
              </div>
              <div class="tc-body">
                <div class="tc-country">Kenya &bull; Safari</div>
                <div class="tc-title"><a href="tours/<?= $tour['slug'] ?>"><?= htmlspecialchars($tour['title']) ?></a>
                </div>
                <?php if ($route): ?>
                  <div class="tc-route"><i class="fa fa-map-marker"></i><?= htmlspecialchars($route) ?></div>
                <?php endif; ?>
                <div class="tc-excerpt"><?= htmlspecialchars(substr(strip_tags($excerpt), 0, 130)) ?>...</div>
                <div class="tc-footer">
                  <div class="tc-price-text">From <strong><?= $price ?></strong></div>
                  <a href="tours/<?= $tour['slug'] ?>" class="tc-cta" style="padding:10px 24px; font-size:11px;">View
                    Itinerary</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
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
        <?php foreach ($countries as $dest):
          if (!$dest['featured_image'])
            continue;
          $img = $dest['featured_image'];
          if (!str_starts_with($img, 'http') && !str_starts_with($img, 'images/')) {
            $img = 'uploads/' . $img;
          }
          $countrySlug = strtolower(str_replace(' ', '-', $dest['country']));
          ?>
          <?php
          $countryName = $dest['country'];
          $region = 'Africa';
          $cLower = strtolower(trim($countryName));
          if (in_array($cLower, ['maldives', 'sri lanka', 'indonesia', 'bali'])) {
              $region = 'Asia';
          } elseif (in_array($cLower, ['uae', 'united arab emirates', 'dubai', 'oman', 'qatar'])) {
              $region = 'Middle East';
          } elseif (in_array($cLower, ['france', 'italy', 'greece', 'spain', 'uk'])) {
              $region = 'Europe';
          } elseif (in_array($cLower, ['seychelles', 'mauritius', 'madagascar'])) {
              $region = 'Indian Ocean';
          }
          ?>
          <div class="item">
            <a href="country.php?name=<?= urlencode($countryName) ?>" class="fa-dest-card">
              <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($countryName) ?>" loading="lazy">
              <div class="dc-overlay"></div>
              <div class="dc-text">
                <div class="dc-region"><?= htmlspecialchars($region) ?></div>
                <div class="dc-name"><?= htmlspecialchars($countryName) ?></div>
              </div>
              <?php if ($dest['tour_count'] > 0): ?>
                <div class="dc-tour-count"><?= $dest['tour_count'] ?> Tour<?= $dest['tour_count'] > 1 ? 's' : '' ?></div>
              <?php endif; ?>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="row mt-5">
        <div class="col-12 text-center mt-5">
          <a href="destinations" class="btn-filao-cta">View All Regions</a>
        </div>
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
            <img src="images/Filao/Company/tourists behind safari car.jpeg"
              alt="Filao Adventures Story" style="width:100%;height:100%;object-fit:cover;display:block;">
          </div>
        </div>
        <div class="col-lg-6 offset-lg-1">
          <div class="fa-section-heading">
            <span class="eyebrow">Why Filao</span>
            <h2>Your Safari, Your Way</h2>
          </div>
          <p style="font-size:16px;line-height:1.8;color:#4A4340;margin-bottom:28px;">We are not a booking agent we are
            your personal safari curator. Every Filao Adventures journey begins with understanding you: your pace, your
            passions, your dream. Then we craft something extraordinary around it.</p>
          <div class="mb-4">
            <div class="d-flex align-items-start mb-3">
              <i class="fa fa-leaf mr-3 mt-1" style="color:#628C52;font-size:18px;flex-shrink:0;"></i>
              <div>
                <div style="font-weight:600;font-size:15px;margin-bottom:3px;">Truly Tailor-Made</div>
                <div style="font-size:14px;color:#6B6358;line-height:1.6;">Every detail the lodge, the guide, the pace
                  shaped entirely around your wishes.</div>
              </div>
            </div>
            <div class="d-flex align-items-start mb-3">
              <i class="fa fa-star mr-3 mt-1" style="color:#C49018;font-size:18px;flex-shrink:0;"></i>
              <div>
                <div style="font-weight:600;font-size:15px;margin-bottom:3px;">Deep Kenya Expertise</div>
                <div style="font-size:14px;color:#6B6358;line-height:1.6;">Born from the African bush, our team's
                  knowledge comes from years on the ground in Kenya's parks.</div>
              </div>
            </div>
            <div class="d-flex align-items-start mb-3">
              <i class="fa fa-globe mr-3 mt-1" style="color:#9E3A25;font-size:18px;flex-shrink:0;"></i>
              <div>
                <div style="font-weight:600;font-size:15px;margin-bottom:3px;">Sustainable &amp; Responsible</div>
                <div style="font-size:14px;color:#6B6358;line-height:1.6;">We partner only with eco-certified lodges and
                  contribute to community conservation on every safari.</div>
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
  <section class="section-pad ftco-section testimony-section bg-bottom"
    style="background-image:url('images/Filao/East Africa/pexels-zacchaeus-rains-262050732-20523197.jpg');background-size:cover;background-position:center;">
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
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div style="background: rgba(255,255,255,0.05); padding: 40px; border-radius: 12px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1);">
            <div id="featurable-ca357a07-9cb6-4644-8d3e-18eac19a55c6" data-featurable-async></div><script src="https://featurable.com/assets/bundle.js" defer charset="UTF-8"></script> 
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
            <div class="bc-image-wrap"><img
                src="images/Filao/East Africa/Maasai Mara/free-photo-of-wildebeest-grazing-in-the-kenyan-savannah (6).jpeg"
                alt="Maasai Mara Migration" class="bc-image" loading="lazy"></div>
            <div class="bc-meta">
              <span class="bc-date">March 2025</span>
              <span class="bc-cat">Safari Guide</span>
            </div>
            <div class="bc-title"><a href="blog">Best Time to Visit the Maasai Mara: A Month-by-Month Guide</a>
            </div>
            <div class="bc-excerpt">The Maasai Mara is spectacular year-round, but timing your visit around the Great
              Migration when 1.5 million wildebeest cross the Mara River creates once-in-a-lifetime moments.</div>
            <a href="blog" class="bc-link">Read More <i class="fa fa-arrow-right"></i></a>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-5 d-flex">
          <div class="fa-blog-card w-100">
            <div class="bc-image-wrap"><img
                src="images/Filao/East Africa/Amboseli/Sarova-Shaba-Safari-breakfast-in-the-wild.jpg"
                alt="Amboseli Safari" class="bc-image" loading="lazy"></div>
            <div class="bc-meta">
              <span class="bc-date">January 2025</span>
              <span class="bc-cat">Comparison</span>
            </div>
            <div class="bc-title"><a href="blog">Amboseli vs Maasai Mara: Which Safari Park is Right for You?</a>
            </div>
            <div class="bc-excerpt">Both parks offer extraordinary wildlife experiences, but they each have a distinctly
              different character. Here is how to decide which one belongs on your Kenya itinerary.</div>
            <a href="blog" class="bc-link">Read More <i class="fa fa-arrow-right"></i></a>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-5 d-flex">
          <div class="fa-blog-card w-100">
            <div class="bc-image-wrap"><img src="images/Filao/Company/guy picnic in a park with safaricar.jpeg"
                alt="Tailor Made Safari" class="bc-image" loading="lazy"></div>
            <div class="bc-meta">
              <span class="bc-date">February 2025</span>
              <span class="bc-cat">Travel Tips</span>
            </div>
            <div class="bc-title"><a href="blog">5 Reasons to Choose a Tailor-Made Safari Over a Group Tour</a>
            </div>
            <div class="bc-excerpt">When you choose a tailor-made safari with Filao Adventures, every detail from the
              lodge to the guide, the pace, and the route is shaped entirely around you.</div>
            <a href="blog" class="bc-link">Read More <i class="fa fa-arrow-right"></i></a>
          </div>
        </div>
      </div>
      <div class="text-center"><a href="blog" class="view-all-link">View All Articles &rarr;</a></div>
    </div>
  </section>

  <!-- ====== CTA BANNER ====== -->
  <section class="fa-cta-banner"
    style="background-image:url('images/Filao/East Africa/pexels-mwauraken-29093739.jpg');">
    <div class="overlay"></div>
    <div class="container fa-cta-content" style="max-width:1280px;">
      <h2>Ready to Start Your Safari?</h2>
      <p>Speak with a Filao Adventures specialist and let us craft your perfect journey from the first sunrise game
        drive to the final sundowner.</p>
      <a href="#" class="btn-filao-cta" data-open-planner="true" style="font-size:12px;padding:14px 32px;">Start
        Planning &rarr;</a>
      <a href="contact" class="view-all-link"
        style="color:rgba(255,255,255,.8);border-color:rgba(255,255,255,.5);margin-left:20px;">Contact Us &rarr;</a>
    </div>
  </section>

  <?php require_once 'includes/footer.php'; ?>

  <!-- loader -->
  <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px">
      <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
      <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10"
        stroke="#C49018" />
    </svg></div>

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
    $(document).ready(function () {
      // Destinations carousel
      $('.carousel-destination').owlCarousel({
        loop: true, autoplay: true, autoplayTimeout: 3800, autoplayHoverPause: true,
        margin: 16, nav: true, dots: false,
        navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
        responsive: { 0: { items: 1 }, 600: { items: 2 }, 900: { items: 3 }, 1200: { items: 4 } }
      });
      // Testimonials
      $('.carousel-testimony').owlCarousel({
        loop: true, margin: 24, nav: false, dots: true, autoplay: true, autoplayTimeout: 5000,
        responsive: { 0: { items: 1 }, 768: { items: 2 } }
      });
      
      // Hero AJAX Search
      var heroSearchInput = document.getElementById('hero-live-search');
      var heroSearchResults = document.getElementById('hero-search-results');
      if (heroSearchInput && heroSearchResults) {
        var heroSearchTimeout;
        heroSearchInput.addEventListener('input', function() {
          var q = this.value.trim();
          clearTimeout(heroSearchTimeout);
          
          if (q.length < 2) {
            heroSearchResults.classList.add('d-none');
            return;
          }
          
          heroSearchTimeout = setTimeout(function() {
            fetch('ajax-search.php?q=' + encodeURIComponent(q))
              .then(function(res) { return res.json(); })
              .then(function(data) {
                if (data.length === 0) {
                  heroSearchResults.innerHTML = '<div style="padding:16px;color:#6B6358;font-size:14px;text-align:center;">No results found</div>';
                } else {
                  var html = '<ul style="list-style:none;margin:0;padding:0;">';
                  data.forEach(function(item) {
                    html += `
                      <li style="border-bottom:1px solid #E5DDD0;">
                        <a href="${item.url}" style="display:flex;align-items:center;padding:12px 16px;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='#FAF8F4'" onmouseout="this.style.background='transparent'">
                          <img src="${item.image}" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:4px;margin-right:16px;">
                          <div>
                            <div style="font-family:'Cormorant Garant',serif;font-size:18px;color:#1C1712;line-height:1.2;margin-bottom:4px;">${item.title}</div>
                            <div style="font-family:'Inter',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#C49018;">${item.type}</div>
                          </div>
                        </a>
                      </li>
                    `;
                  });
                  html += '</ul>';
                  heroSearchResults.innerHTML = html;
                }
                heroSearchResults.classList.remove('d-none');
              })
              .catch(function(err) {
                console.error('Search error', err);
              });
          }, 300);
        });
        
        document.addEventListener('click', function(e) {
          if (!e.target.closest('.fa-search-field')) {
            heroSearchResults.classList.add('d-none');
          }
        });
      }
      
      // Explicitly initialize and start carousel
      if (typeof jQuery !== 'undefined') {
        $('#heroCarousel').carousel({
          interval: 6000,
          pause: false,
          ride: 'carousel'
        });
      }
    });
  </script>
  <script src="js/start-planning.js"></script>
</body>

</html>