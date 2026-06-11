<?php
require_once 'includes/db.php';
$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: tours.php'); exit; }
$pdo = getPDO();

// Fetch tour
$tour = $pdo->prepare('SELECT * FROM tours WHERE slug=? AND status="published" AND is_active_ad=1');
$tour->execute([$slug]);
$tour = $tour->fetch();
if (!$tour) { header('Location: tours.php'); exit; }
$id = $tour['id'];

// Fetch itinerary steps with full accommodation info
$steps = $pdo->prepare('SELECT ist.*, d.name as dest_name, d.latitude, d.longitude, d.country, a.id as acc_id, a.name as acc_name, a.featured_image as acc_image, a.description as acc_desc FROM itinerary_steps ist JOIN destinations d ON d.id=ist.destination_id LEFT JOIN accommodations a ON a.id=ist.accommodation_id WHERE ist.tour_id=? ORDER BY ist.step_number ASC');
$steps->execute([$id]);
$steps = $steps->fetchAll();

// Fetch gallery images
$gallery = $pdo->prepare('SELECT * FROM tour_images WHERE tour_id=? ORDER BY id ASC');
$gallery->execute([$id]);
$gallery = $gallery->fetchAll();

$heroImg = $tour['featured_image'] ? 'uploads/'.$tour['featured_image'] : 'images/Filao/East Africa/pexels-droneafrica-13234382.jpg';
$price = $tour['price_from_usd'] ? '$'.number_format($tour['price_from_usd']) : 'Contact Us';
$nights = $tour['duration_days'] - 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $base_href = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') ? '/filao/' : '/'; ?>
  <base href="<?= $base_href ?>">
  <title><?= htmlspecialchars($tour['seo_title'] ?: $tour['title']) ?> &mdash; Filao Adventures</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="assets/favicon_io/favicon.ico">
  
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,400;0,500;0,600;0,700;1,500;1,600&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/magnific-popup.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css">
  <link rel="stylesheet" href="css/style.css"> <!-- Fallback -->
  <link rel="stylesheet" href="css/start-planning.css">
  
  <style>
    /* HIGH VIBRANCY AD LANDING STYLES */
    :root {
      --ad-dark: #121518;
      --ad-gold: #D4AF37;
      --ad-gold-glow: rgba(212, 175, 55, 0.4);
      --ad-red: #D92525;
      --ad-cream: #FDFBF7;
      --ad-green: #2E5A27;
      --font-heading: 'Cormorant Garant', serif;
      --font-body: 'Outfit', sans-serif;
    }

    body { background:var(--ad-cream); color:#333; font-family:var(--font-body); overflow-x:hidden; }
    h1, h2, h3, h4, h5, h6 { font-family:var(--font-heading); color:var(--ad-dark); }
    
    /* Navbar */
    .ad-nav { background:rgba(255, 255, 255, 0.95); backdrop-filter:blur(10px); box-shadow:0 10px 30px rgba(0,0,0,0.05); padding:10px 0; position:sticky; top:0; z-index:1030; }
    .ad-nav .logo img { height:50px; }
    .ad-nav-contacts { display:flex; gap:24px; align-items:center; font-size:14px; font-weight:600; color:var(--ad-dark); }
    .ad-nav-contacts a { color:var(--ad-dark); text-decoration:none; display:flex; align-items:center; gap:8px; transition:color 0.2s; }
    .ad-nav-contacts a:hover { color:var(--ad-gold); }
    .ad-nav-contacts i { color:var(--ad-gold); font-size:18px; }
    .btn-ad-nav { background:linear-gradient(135deg, #D92525, #B31B1B); color:#fff !important; padding:12px 32px; border-radius:50px; font-weight:800; text-transform:uppercase; letter-spacing:1px; font-size:13px; text-decoration:none; box-shadow:0 8px 20px rgba(217, 37, 37, 0.3); transition:all 0.3s; }
    .btn-ad-nav:hover { background:linear-gradient(135deg, #B31B1B, #9E1515); transform:translateY(-3px); box-shadow:0 12px 25px rgba(217, 37, 37, 0.4); }
    
    @media (max-width: 991px) { .ad-nav-contacts { display:none; } }

    /* Hero Section */
    .ad-hero { position:relative; padding: 120px 0; min-height:85vh; display:flex; align-items:center; overflow:hidden; }
    .ad-hero-bg { position:absolute; top:-5%; left:-5%; right:-5%; bottom:-5%; background-size:cover; background-position:center; animation:zoomBg 25s infinite alternate linear; }
    @keyframes zoomBg { from { transform:scale(1); } to { transform:scale(1.1); } }
    .ad-hero-overlay { position:absolute; inset:0; background:linear-gradient(120deg, rgba(18,21,24,0.95) 0%, rgba(18,21,24,0.7) 45%, rgba(18,21,24,0.2) 100%); z-index:2; }
    .ad-hero-content { position:relative; z-index:3; max-width:850px; color:#fff; }
    
    .ad-badge { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(90deg, #D92525, #FF4545); color:#fff; font-weight:900; text-transform:uppercase; letter-spacing:2px; font-size:12px; padding:8px 20px; border-radius:30px; margin-bottom:20px; box-shadow:0 4px 15px rgba(217, 37, 37, 0.4); animation:pulseBadge 2s infinite; }
    @keyframes pulseBadge { 0% { box-shadow:0 0 0 0 rgba(217, 37, 37, 0.4); } 70% { box-shadow:0 0 0 15px rgba(217, 37, 37, 0); } 100% { box-shadow:0 0 0 0 rgba(217, 37, 37, 0); } }
    
    .ad-title { color:#ffffff !important; font-size:clamp(40px, 5.5vw, 75px); font-weight:600; line-height:1.1; margin-bottom:24px; text-shadow:0 10px 30px rgba(0,0,0,0.5); }
    
    .ad-meta { display:flex; flex-wrap:wrap; gap:20px; font-size:18px; font-weight:600; color:#fff; margin-bottom:40px; }
    .ad-meta-item { display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.1); backdrop-filter:blur(10px); padding:10px 20px; border-radius:12px; border:1px solid rgba(255,255,255,0.2); }
    .ad-meta-item i { color:var(--ad-gold); font-size:24px; }
    
    .btn-hero-primary { background:linear-gradient(135deg, var(--ad-gold), #B38F24); color:var(--ad-dark) !important; padding:18px 45px; border-radius:50px; font-weight:800; text-transform:uppercase; letter-spacing:1px; font-size:15px; text-decoration:none; display:inline-block; box-shadow:0 10px 30px var(--ad-gold-glow); transition:all 0.3s; margin-right:15px; margin-bottom:15px; border:2px solid transparent; }
    .btn-hero-primary:hover { transform:translateY(-5px); box-shadow:0 15px 35px var(--ad-gold-glow); background:linear-gradient(135deg, #FFF0A8, var(--ad-gold)); }
    
    .btn-hero-secondary { background:rgba(255,255,255,0.1); backdrop-filter:blur(5px); color:#fff !important; padding:18px 45px; border-radius:50px; font-weight:800; text-transform:uppercase; letter-spacing:1px; font-size:15px; text-decoration:none; display:inline-block; border:2px solid rgba(255,255,255,0.3); transition:all 0.3s; }
    .btn-hero-secondary:hover { background:#fff; color:var(--ad-dark) !important; }

    /* Floating Overview Card */
    .ad-overview-wrapper { position:relative; margin-top:40px; z-index:10; }
    .ad-overview-card { background:#fff; padding:50px; border-radius:24px; box-shadow:0 25px 60px rgba(0,0,0,0.08); display:flex; flex-wrap:wrap; gap:40px; }
    .ad-overview-text { flex:1; min-width:300px; font-size:18px; line-height:1.8; color:#555; }
    .ad-overview-text h2 { font-size:42px; margin-bottom:20px; color:var(--ad-dark); }
    .ad-overview-highlights { flex:1; min-width:300px; background:var(--ad-cream); padding:30px; border-radius:16px; border:1px solid #EAE0D5; }
    .ad-overview-highlights h3 { font-size:24px; margin-bottom:20px; color:var(--ad-dark); display:flex; align-items:center; gap:10px; }
    .ad-overview-highlights h3 i { color:var(--ad-gold); }
    .ad-overview-highlights ul { padding:0; list-style:none; }
    .ad-overview-highlights li { position:relative; padding-left:35px; margin-bottom:15px; font-size:16px; font-weight:500; color:#444; }
    .ad-overview-highlights li::before { content:'\f00c'; font-family:'FontAwesome'; position:absolute; left:0; top:2px; color:var(--ad-green); font-size:18px; }

    /* Sticky Form Glassmorphism */
    .ad-sticky-col { position:relative; }
    .ad-form-glass { position:sticky; top:120px; background:rgba(255,255,255,0.85); backdrop-filter:blur(20px); padding:40px; border-radius:24px; box-shadow:0 30px 60px rgba(0,0,0,0.1); border:1px solid rgba(255,255,255,1); }
    .ad-form-glass::before { content:''; position:absolute; top:0; left:0; right:0; height:6px; background:linear-gradient(90deg, var(--ad-gold), #FFF0A8); border-radius:24px 24px 0 0; }
    .ad-form-title { font-size:32px; font-family:var(--font-heading); font-weight:700; margin-bottom:10px; color:var(--ad-dark); }
    .ad-form-glass p { font-size:15px; color:#666; margin-bottom:25px; }
    .ad-form-glass .form-control { background:#F8F9FA; border:1px solid #EAE0D5; padding:15px 20px; border-radius:12px; font-size:15px; font-family:var(--font-body); box-shadow:inset 0 2px 5px rgba(0,0,0,0.02); transition:all 0.3s; }
    .ad-form-glass .form-control:focus { background:#fff; border-color:var(--ad-gold); box-shadow:0 0 0 4px var(--ad-gold-glow); }
    .btn-submit-form { width:100%; background:linear-gradient(135deg, var(--ad-dark), #2A2520); color:#fff; padding:18px; border-radius:12px; font-size:16px; font-weight:800; text-transform:uppercase; letter-spacing:1px; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.15); transition:all 0.3s; margin-top:10px; }
    .btn-submit-form:hover { background:linear-gradient(135deg, #000, var(--ad-dark)); transform:translateY(-3px); box-shadow:0 15px 40px rgba(0,0,0,0.2); }

    /* ZigZag Itinerary */
    .ad-section-title { font-size:56px; font-weight:700; text-align:center; margin-bottom:60px; color:var(--ad-dark); }
    .ad-section-title span { color:var(--ad-gold); font-style:italic; }
    .ad-zig { display:flex; flex-wrap:wrap; align-items:center; gap:50px; margin-bottom:80px; }
    .ad-zig:nth-child(even) { flex-direction:row-reverse; }
    .ad-zig-img { flex:1; min-width:300px; position:relative; }
    .ad-zig-img img { width:100%; height:400px; object-fit:cover; border-radius:24px; box-shadow:0 20px 50px rgba(0,0,0,0.15); }
    .ad-zig-day { position:absolute; top:20px; left:20px; background:var(--ad-gold); color:var(--ad-dark); font-weight:900; text-transform:uppercase; letter-spacing:2px; font-size:14px; padding:10px 20px; border-radius:30px; box-shadow:0 10px 25px var(--ad-gold-glow); }
    .ad-zig-text { flex:1; min-width:300px; }
    .ad-zig-text h3 { font-size:36px; margin-bottom:15px; }
    .ad-zig-meta { display:inline-flex; gap:15px; background:#fff; padding:10px 20px; border-radius:50px; font-weight:600; font-size:14px; color:#555; box-shadow:0 5px 15px rgba(0,0,0,0.05); margin-bottom:20px; border:1px solid #EAE0D5; }
    .ad-zig-meta i { color:var(--ad-gold); }
    .ad-zig-desc { font-size:17px; line-height:1.8; color:#555; }

    /* Luxury Accommodations */
    .ad-acc-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(350px, 1fr)); gap:30px; }
    .ad-acc-card { background:#fff; border-radius:24px; overflow:hidden; box-shadow:0 15px 40px rgba(0,0,0,0.06); transition:transform 0.4s; border:1px solid #EAE0D5; }
    .ad-acc-card:hover { transform:translateY(-10px); box-shadow:0 30px 60px rgba(0,0,0,0.1); }
    .ad-acc-img { height:250px; position:relative; }
    .ad-acc-img img { width:100%; height:100%; object-fit:cover; }
    .ad-acc-badge { position:absolute; bottom:15px; right:15px; background:rgba(28,23,18,0.8); backdrop-filter:blur(5px); color:#fff; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; padding:6px 15px; border-radius:30px; }
    .ad-acc-body { padding:30px; }
    .ad-acc-title { font-size:28px; font-weight:600; margin-bottom:10px; }
    .ad-acc-desc { font-size:15px; color:#666; line-height:1.7; }

    /* Gallery Grid */
    .ad-gallery-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:16px; }
    .ad-gallery-item { display:block; border-radius:16px; overflow:hidden; aspect-ratio:1; box-shadow:0 10px 30px rgba(0,0,0,0.08); }
    .ad-gallery-item img { width:100%; height:100%; object-fit:cover; transition:transform 0.4s ease; }
    .ad-gallery-item:hover img { transform:scale(1.08); }

    /* Pricing Section */
    .ad-pricing-box { background:#fff; border-radius:24px; padding:50px; box-shadow:0 20px 50px rgba(0,0,0,0.08); border-top:6px solid var(--ad-dark); }
    .table-pricing { width:100%; font-size:18px; color:#333; }
    .table-pricing th { background:var(--ad-cream); padding:20px; font-weight:700; text-transform:uppercase; letter-spacing:1px; font-size:14px; border-bottom:2px solid #EAE0D5; }
    .table-pricing td { padding:20px; border-bottom:1px solid #EAE0D5; font-weight:500; }
    .table-pricing tr:last-child td { border-bottom:none; }
    .table-pricing td.price-val { font-size:22px; font-weight:800; color:var(--ad-dark); }

    .inc-exc-grid { display:grid; grid-template-columns:1fr 1fr; gap:40px; margin-bottom:50px; }
    .inc-box { background:rgba(46, 90, 39, 0.05); padding:30px; border-radius:16px; border:1px solid rgba(46, 90, 39, 0.1); }
    .inc-box h4 { color:var(--ad-green); font-size:24px; margin-bottom:20px; display:flex; align-items:center; gap:10px; }
    .exc-box { background:rgba(217, 37, 37, 0.05); padding:30px; border-radius:16px; border:1px solid rgba(217, 37, 37, 0.1); }
    .exc-box h4 { color:var(--ad-red); font-size:24px; margin-bottom:20px; display:flex; align-items:center; gap:10px; }
    .inc-exc-grid ul { list-style:none; padding:0; }
    .inc-exc-grid li { position:relative; padding-left:30px; margin-bottom:12px; font-size:16px; color:#444; }
    .inc-box li::before { content:'\f00c'; font-family:'FontAwesome'; position:absolute; left:0; top:2px; color:var(--ad-green); }
    .exc-box li::before { content:'\f00d'; font-family:'FontAwesome'; position:absolute; left:0; top:2px; color:var(--ad-red); }

    @media (max-width: 991px) {
      .inc-exc-grid { grid-template-columns:1fr; }
      .ad-zig { flex-direction:column !important; gap:30px; }
      .ad-title { font-size:42px; }
      .ad-overview-card { padding:30px; }
    }

    /* Mobile Sticky */
    .ad-mobile-sticky { display:none; position:fixed; bottom:0; left:0; width:100%; background:rgba(255,255,255,0.95); backdrop-filter:blur(10px); padding:15px 20px; box-shadow:0 -10px 30px rgba(0,0,0,0.1); z-index:9999; align-items:center; justify-content:space-between; border-top:1px solid #EAE0D5; }
    .ad-mobile-sticky .price { font-family:var(--font-body); font-weight:900; color:var(--ad-dark); font-size:22px; line-height:1; }
    .ad-mobile-sticky .price span { font-size:12px; color:#666; font-weight:600; display:block; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px; }
    .ad-mobile-sticky .btn { background:linear-gradient(135deg, var(--ad-gold), #B38F24); color:var(--ad-dark); font-weight:800; text-transform:uppercase; letter-spacing:1px; border-radius:50px; padding:14px 28px; border:none; box-shadow:0 8px 20px var(--ad-gold-glow); }
    @media (max-width: 991px) { .ad-mobile-sticky { display:flex; } body { padding-bottom:85px; } }

    /* Footer */
    .ad-footer { background:var(--ad-dark); color:rgba(255,255,255,0.5); padding:60px 0; text-align:center; font-size:14px; }
  </style>
</head>
<body>

<!-- Custom Navbar -->
<nav class="ad-nav">
  <div class="container px-4 px-lg-5 d-flex justify-content-between align-items-center" style="max-width:1400px;">
    <a href="/" class="logo">
      <img src="assets/logo/filao-logo.png" alt="Filao Adventures">
    </a>
    <div class="ad-nav-contacts">
      <a href="tel:+254757139239"><i class="fa fa-phone"></i> +254 757 139239</a>
      <a href="mailto:info@filaoadventures.co.ke"><i class="fa fa-envelope"></i> info@filaoadventures.co.ke</a>
    </div>
    <a href="javascript:void(0)" class="btn-ad-nav" data-open-planner="true" data-tour-id="<?= $tour['id'] ?>" data-tour-title="<?= htmlspecialchars($tour['title']) ?>">Claim Offer Now</a>
  </div>
</nav>

<!-- Hero Section -->
<section class="ad-hero">
  <div class="ad-hero-bg" style="background-image:url('<?= htmlspecialchars($heroImg) ?>');"></div>
  <div class="ad-hero-overlay"></div>
  <div class="container px-4 px-lg-5 position-relative z-3" style="max-width:1400px;">
    <div class="ad-hero-content">
      <div class="ad-badge"><i class="fa fa-bolt"></i> Exclusive Safari Deal</div>
      <h1 class="ad-title"><?= htmlspecialchars($tour['title']) ?></h1>
      <div class="ad-meta">
        <div class="ad-meta-item"><i class="fa fa-clock-o"></i> <?= $tour['duration_days'] ?> Days</div>
        <div class="ad-meta-item"><i class="fa fa-map-marker"></i> <?= htmlspecialchars($tour['country'] ?: 'East Africa') ?></div>
        <?php if($tour['price_from_usd']): ?>
        <div class="ad-meta-item"><i class="fa fa-tag"></i> From $<?= number_format($tour['price_from_usd']) ?> pp</div>
        <?php endif; ?>
      </div>
      <a href="javascript:void(0)" class="btn-hero-primary" onclick="document.getElementById('enquiryFormBox').scrollIntoView({behavior:'smooth'})">Get Free Quote</a>
      <a href="javascript:void(0)" class="btn-hero-secondary" onclick="document.getElementById('itinerary').scrollIntoView({behavior:'smooth'})">View Itinerary</a>
    </div>
  </div>
</section>

<!-- Main Content Area -->
<div class="container px-4 px-lg-5 position-relative" style="max-width:1400px;">
  
  <!-- Floating Overview Card -->
  <div class="ad-overview-wrapper">
    <div class="ad-overview-card">
      <div class="ad-overview-text">
        <h2>Experience the Extraordinary</h2>
        <?= $tour['description'] ?: '<p>'.nl2br(htmlspecialchars($tour['excerpt'])).'</p>' ?>
      </div>
      <div class="ad-overview-highlights">
        <h3><i class="fa fa-star"></i> Tour Highlights</h3>
        <?php if($tour['highlights']): ?>
          <?= $tour['highlights'] ?>
        <?php else: ?>
          <ul>
            <li>Exclusive game drives in custom 4x4 Land Cruisers</li>
            <li>Expert local guides with deep knowledge</li>
            <li>Premium luxury accommodations</li>
            <li>Spectacular photography opportunities</li>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="row mt-5 pt-5">
    <!-- Left Column (Scrolling Content) -->
    <div class="col-lg-8 pe-lg-5">
      
      <!-- Itinerary -->
      <div id="itinerary" class="mb-5 pb-5">
        <h2 class="ad-section-title">Your <span>Journey</span></h2>
        
        <div class="mt-5">
          <?php foreach($steps as $step): ?>
          <div class="ad-zig">
            <div class="ad-zig-img">
              <div class="ad-zig-day">Day <?= $step['step_number'] ?></div>
              <?php $simg = $step['step_image'] ? 'uploads/'.htmlspecialchars($step['step_image']) : 'images/Filao/East Africa/pexels-droneafrica-13234382.jpg'; ?>
              <img src="<?= $simg ?>" alt="Day <?= $step['step_number'] ?>">
            </div>
            <div class="ad-zig-text">
              <h3><?= htmlspecialchars($step['dest_name']) ?></h3>
              <div class="ad-zig-meta">
                <?php if($step['acc_name']): ?>
                <span><i class="fa fa-bed"></i> <?= htmlspecialchars($step['acc_name']) ?></span>
                <?php endif; ?>
                <?php if($step['nights_count']>1): ?>
                <span><i class="fa fa-moon-o"></i> <?= $step['nights_count'] ?> Nights</span>
                <?php endif; ?>
              </div>
              <div class="ad-zig-desc">
                <?= nl2br(htmlspecialchars($step['step_description'])) ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Route Map -->
      <div id="tour-map-section" class="mb-5 pb-5 border-top pt-5">
        <h2 class="ad-section-title">Safari <span>Route</span></h2>
        <div id="tourMap" style="height:450px; border-radius:24px; box-shadow:0 15px 40px rgba(0,0,0,0.06); margin-top:40px; display:flex; align-items:center; justify-content:center; background:#e5e5e5;">
          <span style="color:#6B6358;font-size:14px;font-family:'Inter',sans-serif;">Loading Map...</span>
        </div>
        <p class="mt-3 text-center" style="font-size:13px;color:#888;">* Route is illustrative. Actual transit paths may vary.</p>
      </div>

      <!-- Accommodations -->
      <?php 
      $hasAcc = false;
      foreach($steps as $s) { if($s['acc_id']) { $hasAcc = true; break; } }
      if ($hasAcc): 
      ?>
      <div class="mb-5 pb-5 border-top pt-5">
        <h2 class="ad-section-title">Premium <span>Stays</span></h2>
        <div class="ad-acc-grid mt-5">
          <?php 
          $displayedAcc = [];
          foreach($steps as $step): 
            if (!$step['acc_id'] || in_array($step['acc_id'], $displayedAcc)) continue;
            $displayedAcc[] = $step['acc_id'];
            $img = $step['acc_image'] ? 'uploads/' . htmlspecialchars($step['acc_image']) : 'images/Filao/East Africa/Sopa Lodges/dining-by-the-waterhole-in-samburu-sopa-lodge.jpg';
          ?>
          <div class="ad-acc-card">
            <div class="ad-acc-img">
              <span class="ad-acc-badge"><?= htmlspecialchars($step['dest_name']) ?></span>
              <img src="<?= $img ?>" alt="<?= htmlspecialchars($step['acc_name']) ?>">
            </div>
            <div class="ad-acc-body">
              <h4 class="ad-acc-title"><?= htmlspecialchars($step['acc_name']) ?></h4>
              <div class="ad-acc-desc">
                <?= html_entity_decode($step['acc_desc'] ?? 'Premium luxury accommodation offering spectacular views and world-class service.') ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Gallery Section -->
      <?php if(count($gallery) > 0 || true): ?>
      <div class="mb-5 pb-5 border-top pt-5">
        <h2 class="ad-section-title">Visual <span>Inspiration</span></h2>
        <div class="ad-gallery-grid popup-gallery mt-5">
          <?php 
          if(count($gallery) > 0):
            foreach($gallery as $img): ?>
            <a href="uploads/<?= htmlspecialchars($img['image_path'] ?? '') ?>" class="ad-gallery-item" title="<?= htmlspecialchars($img['caption'] ?? '') ?>">
              <img src="uploads/<?= htmlspecialchars($img['image_path'] ?? '') ?>" alt="Gallery Image" loading="lazy">
            </a>
            <?php endforeach;
          else: 
            $fallbackImages = [
              'images/Filao/East Africa/pexels-droneafrica-13234382.jpg',
              'images/Filao/East Africa/Maasai Mara/free-photo-of-majestic-african-elephant-in-kenyan-savanna (6).jpeg',
              'images/Filao/East Africa/Amboseli/Sarova-Shaba-Safari-breakfast-in-the-wild.jpg',
              'images/Filao/East Africa/pexels-kelly-17291020.jpg',
              'images/Filao/East Africa/pexels-balazsimon-15993990.jpg',
              'images/Filao/East Africa/Maasai Mara/free-photo-of-leopard-resting-in-tree-masai-mara-kenya (4).jpeg'
            ];
            foreach($fallbackImages as $fimg): ?>
            <a href="<?= $fimg ?>" class="ad-gallery-item">
              <img src="<?= $fimg ?>" alt="Gallery Image" loading="lazy">
            </a>
            <?php endforeach;
          endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Pricing & Inclusions -->
      <div class="mb-5 pb-5 border-top pt-5">
        <h2 class="ad-section-title">Investment <span>& Details</span></h2>
        
        <div class="ad-pricing-box mt-5">
          <div class="inc-exc-grid">
            <div class="inc-box">
              <h4><i class="fa fa-check-circle"></i> What's Included</h4>
              <?= $tour['inclusions'] ?: '<ul><li>All park entrance fees</li><li>Full board accommodation</li><li>Exclusive 4x4 Safari Land Cruiser</li><li>Professional guide</li><li>Airport transfers</li></ul>' ?>
            </div>
            <div class="exc-box">
              <h4><i class="fa fa-times-circle"></i> Not Included</h4>
              <?= $tour['exclusions'] ?: '<ul><li>International flights</li><li>Travel insurance</li><li>Tips and gratuities</li><li>Personal items</li></ul>' ?>
            </div>
          </div>

          <table class="table-pricing">
            <thead>
              <tr>
                <th>Group Size</th>
                <th style="text-align:right;">Price Per Person (USD)</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $tiers = [1 => '1 Person', 2 => '2 People', 3 => '3 People', 4 => '4 People', 5 => '5 People', 6 => '6 People'];
              $hasPricing = false;
              foreach($tiers as $num => $label) {
                  $col = $num > 1 ? "price_{$num}_people" : "price_{$num}_person";
                  $p = isset($tour[$col]) ? $tour[$col] : null;
                  if (!empty($p) && $p > 0) {
                      $hasPricing = true;
                      echo "<tr><td>{$label}</td><td class='price-val' align='right'>$" . number_format($p) . "</td></tr>";
                  }
              }
              if (!$hasPricing) {
                  echo "<tr><td colspan='2'>Please contact us for detailed pricing.</td></tr>";
              }
              ?>
            </tbody>
          </table>
          <div style="text-align:center; margin-top:30px;">
            <p style="font-size:14px; color:#888;">* Prices are subject to change based on seasonality. Contact us for exact dates.</p>
          </div>
        </div>
      </div>

    </div>
    
    <!-- Right Column (Sticky Form) -->
    <div class="col-lg-4 ad-sticky-col">
      <div class="ad-form-glass" id="enquiryFormBox">
        <h3 class="ad-form-title">Secure Your Spot</h3>
        <p>Get a customized itinerary and exact pricing within 24 hours.</p>
        
        <div id="tourEnquiryFeedback" class="alert" style="display:none;font-size:14px;padding:15px;border-radius:12px;"></div>
        
        <form id="tourEnquiryForm" action="#" method="POST">
          <input type="hidden" name="type" value="tour_enquiry">
          <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
          <input type="hidden" name="tour_title" value="<?= htmlspecialchars($tour['title']) ?>">
          
          <div class="mb-3">
            <input type="text" name="first_name" class="form-control" placeholder="Full Name" required>
          </div>
          <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="Email Address" required>
          </div>
          <div class="mb-3">
            <input type="month" name="travel_date" class="form-control" placeholder="Expected Travel Date">
          </div>
          <div class="mb-3">
            <select name="adults" class="form-control">
              <option value="2">2 Guests</option>
              <option value="1">1 Guest</option>
              <option value="3">3 Guests</option>
              <option value="4">4+ Guests</option>
            </select>
          </div>
          <div class="mb-4">
            <textarea name="message" class="form-control" rows="3" placeholder="Any specific requirements?"></textarea>
          </div>
          <button type="submit" id="tourEnquiryBtn" class="btn-submit-form">Request Quote Now</button>
          
          <div style="display:flex; align-items:center; justify-content:center; gap:8px; margin-top:20px; font-size:13px; color:#888; font-weight:600;">
            <i class="fa fa-lock" style="color:var(--ad-gold);"></i> Your details are 100% secure.
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<!-- Mobile Sticky Bottom CTA -->
<div class="ad-mobile-sticky">
  <div class="price">
    <span>Starting from</span>
    <?= $price ?>
  </div>
  <button class="btn" onclick="document.getElementById('enquiryFormBox').scrollIntoView({behavior:'smooth'})">Get Quote</button>
</div>

<!-- Minimal Footer -->
<footer class="ad-footer">
  <div class="container">
    <img src="assets/logo/filao-logo.png" alt="Filao Adventures" style="height:50px; margin-bottom:20px; filter:brightness(0) invert(1); opacity:0.3;">
    <p class="mb-2">&copy; <?= date('Y') ?> Filao Adventures. All Rights Reserved. &mdash; Nairobi, Kenya</p>
    <p class="mb-0"><a href="privacy-policy">Privacy Policy</a> &bull; <a href="contact">Contact Support</a></p>
  </div>
</footer>

<?php include 'includes/start-planning-modal.php'; ?>

<!-- Scripts -->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.magnific-popup.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet-polylinedecorator@1.6.0/dist/leaflet.polylineDecorator.min.js"></script>
<script src="js/start-planning.js"></script>
<script>
$(document).ready(function() {
  // Gallery Popup
  $('#tourEnquiryForm').on('submit', function(e) {
    e.preventDefault();
    var form = this;
    var btn = $('#tourEnquiryBtn');
    var feedback = $('#tourEnquiryFeedback');
    
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Sending...');
    feedback.hide();
    
    var data = new FormData(form);
    
    fetch('/filao/handlers/enquiry.php', { method: 'POST', body: data })
      .then(r => r.json())
      .then(res => {
        if(res.success) {
          feedback.removeClass('alert-danger').addClass('alert-success')
            .css({background:'rgba(46, 90, 39, 0.1)', border:'1px solid rgba(46, 90, 39, 0.2)', color:'#2E5A27'})
            .html('<i class="fa fa-check-circle mr-2"></i> ' + res.message).show();
          form.reset();
          btn.html('<i class="fa fa-check mr-2"></i> Sent Successfully!');
        } else {
          feedback.removeClass('alert-success').addClass('alert-danger')
            .css({background:'rgba(217, 37, 37, 0.1)', border:'1px solid rgba(217, 37, 37, 0.2)', color:'#D92525'})
            .html('<i class="fa fa-exclamation-circle mr-2"></i> ' + res.message).show();
          btn.prop('disabled', false).html('Request Quote Now');
        }
      })
      .catch(err => {
        feedback.removeClass('alert-success').addClass('alert-danger')
          .css({background:'rgba(217, 37, 37, 0.1)', border:'1px solid rgba(217, 37, 37, 0.2)', color:'#D92525'})
          .html('<i class="fa fa-exclamation-circle mr-2"></i> Network error. Please try again.').show();
        btn.prop('disabled', false).html('Request Quote Now');
      });
  });

  // Init Map
  <?php
  $waypoints = [];
  $destNames = [];
  $nairobiCoords = ['lat' => -1.2921, 'lng' => 36.8219, 'name' => 'Nairobi (Start)', 'day' => 1];

  foreach($steps as $step) {
    $destNames[] = $step['dest_name'];
    if($step['latitude'] && $step['longitude'] && ($step['latitude'] != 0 || $step['longitude'] != 0)) {
      $waypoints[] = ['lat' => (float)$step['latitude'], 'lng' => (float)$step['longitude'], 'name' => $step['dest_name'], 'day' => $step['step_number']];
    }
  }

  if (count($waypoints) > 0 && stripos($waypoints[0]['name'], 'Nairobi') === false) {
      array_unshift($waypoints, $nairobiCoords);
  }
  if (count($waypoints) > 0 && stripos($waypoints[count($waypoints)-1]['name'], 'Nairobi') === false) {
      $endNairobi = $nairobiCoords;
      $endNairobi['name'] = 'Nairobi (End)';
      $endNairobi['day'] = end($waypoints)['day'] + 1;
      $waypoints[] = $endNairobi;
  }
  $waypointsJson = json_encode($waypoints);
  ?>
  
  function initMap() {
    var waypoints = <?= $waypointsJson ?>;
    if(waypoints.length > 0 && document.getElementById('tourMap')) {
      document.getElementById('tourMap').innerHTML = '';
      var map = L.map('tourMap');
      
      L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 20
      }).addTo(map);
      
      var coords = waypoints.map(function(wp) { return [wp.lat, wp.lng]; });
      
      if (coords.length > 1) {
          var forwardCoords = coords.slice(0, coords.length - 1);
          var returnCoords = [coords[coords.length - 2], coords[coords.length - 1]];

          if (forwardCoords.length > 1) {
              var forwardPolyline = L.polyline(forwardCoords, {
                  color: '#C49018', weight: 4, opacity: 0.9, lineJoin: 'round'
              }).addTo(map);

              L.polylineDecorator(forwardPolyline, {
                  patterns: [
                      { offset: 25, repeat: 100, symbol: L.Symbol.arrowHead({pixelSize: 12, polygon: false, pathOptions: {stroke: true, color: '#C49018', weight: 3}}) }
                  ]
              }).addTo(map);
          }

          var returnPolyline = L.polyline(returnCoords, {
              color: '#8B6A14', weight: 3, opacity: 0.8, dashArray: '8, 12', lineJoin: 'round'
          }).addTo(map);

          L.polylineDecorator(returnPolyline, {
              patterns: [
                  { offset: '50%', repeat: 0, symbol: L.Symbol.arrowHead({pixelSize: 12, polygon: false, pathOptions: {stroke: true, color: '#8B6A14', weight: 3}}) }
              ]
          }).addTo(map);
          
          map.fitBounds(L.latLngBounds(coords).pad(0.2));
      }

      waypoints.forEach(function(wp, idx) {
        var iconHtml = '<div style="background:#C49018;color:#fff;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:Inter,sans-serif;font-size:12px;font-weight:700;border:2px solid #fff;box-shadow:0 3px 10px rgba(0,0,0,0.3);">' + (idx+1) + '</div>';
        var icon = L.divIcon({ className: '', html: iconHtml, iconSize: [28, 28], iconAnchor: [14, 14] });
        L.marker([wp.lat, wp.lng], {icon: icon}).addTo(map)
          .bindPopup('<strong style="color:#C49018;font-size:11px;text-transform:uppercase;letter-spacing:0.1em;">Day ' + wp.day + '</strong><br><span style="font-size:14px;font-family:\'Cormorant Garant\',serif;font-weight:600;">' + wp.name + '</span>');
      });
      
    } else if (document.getElementById('tourMap')) {
      document.getElementById('tourMap').innerHTML = '<span style="color:#6B6358;font-size:14px;font-family:\'Inter\',sans-serif;">Map data not available for this tour.</span>';
    }
  }
  
  // Wait a moment for layout to settle before initializing map
  setTimeout(initMap, 500);

});
</script>
</body>
</html>
