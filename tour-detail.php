<?php
require_once 'includes/db.php';
$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: tours.php'); exit; }
$pdo = getPDO();

// Fetch tour
$tour = $pdo->prepare('SELECT * FROM tours WHERE slug=? AND status="published"');
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

// (Accommodations are now processed from $steps)

// Build map waypoints JSON for Leaflet
$waypoints = [];
$destNames = [];
$nairobiCoords = ['lat' => -1.2921, 'lng' => 36.8219, 'name' => 'Nairobi (Start)', 'day' => 1];

foreach($steps as $step) {
  $destNames[] = $step['dest_name'];
  if($step['latitude'] && $step['longitude'] && ($step['latitude'] != 0 || $step['longitude'] != 0)) {
    $waypoints[] = ['lat' => (float)$step['latitude'], 'lng' => (float)$step['longitude'], 'name' => $step['dest_name'], 'day' => $step['step_number']];
  }
}

// Ensure Nairobi is the first waypoint
if (count($waypoints) > 0 && stripos($waypoints[0]['name'], 'Nairobi') === false) {
    array_unshift($waypoints, $nairobiCoords);
}
// Ensure Nairobi is the last waypoint
if (count($waypoints) > 0 && stripos($waypoints[count($waypoints)-1]['name'], 'Nairobi') === false) {
    $endNairobi = $nairobiCoords;
    $endNairobi['name'] = 'Nairobi (End)';
    $endNairobi['day'] = end($waypoints)['day'] + 1;
    $waypoints[] = $endNairobi;
}

$waypointsJson = json_encode($waypoints);
$routeStr = implode(' &rarr; ', array_unique($destNames));
$heroImg = !empty($tour['featured_image']) ? 'uploads/'.$tour['featured_image'] : 'images/Filao/East Africa/pexels-droneafrica-13234382.jpg';
$price = !empty($tour['price_from_usd']) ? '$'.number_format($tour['price_from_usd']) : 'Contact Us';
$nights = max(1, (int)($tour['duration_days'] ?? 1)) - 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $base_href = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') ? '/filao/' : '/'; ?>
  <base href="<?= $base_href ?>">
  <title><?= htmlspecialchars($tour['seo_title'] ?: $tour['title']) ?> &mdash; Filao Adventures</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="<?= htmlspecialchars(strip_tags($tour['meta_description'] ?: $tour['excerpt'] ?: 'An expertly crafted safari by Filao Adventures.')) ?>">
  <link rel="icon" href="assets/favicon_io/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  <!-- Leaflet PolylineDecorator (loaded after leaflet.js in scripts) -->
  <link rel="stylesheet" href="css/animate.css">
  <link rel="stylesheet" href="css/owl.carousel.min.css">
  <link rel="stylesheet" href="css/magnific-popup.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css">
  <style>
    @keyframes slideInRight { from { transform:translateX(100%); opacity:0; } to { transform:translateX(0); opacity:1; } }
    .td-hero { position:relative;height:600px;background-size:cover;background-position:center;border-bottom:3px solid #C49018; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; padding:0 20px; }
    .td-hero .overlay { position:absolute;inset:0;background:linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.5) 100%); z-index:1; }
    .td-hero-content { position:relative; z-index:2; max-width:1000px; }
    .td-hero h1 { font-family:'Inter',sans-serif;font-size:clamp(36px,5vw,64px);font-weight:400;color:#fff;margin-bottom:12px;line-height:1.2; }
    .td-hero p.duration { font-size:20px;font-family:'Inter',sans-serif;color:#fff;font-weight:600;margin-bottom:24px; }
    .td-hero .btn-hero { background:#628C52;color:#fff;padding:12px 32px;border-radius:30px;font-family:'Inter',sans-serif;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;text-decoration:none;font-size:14px;transition:background 0.3s; display:inline-block; }
    .td-hero .btn-hero:hover { background:#4f7342; }

    .hero-breadcrumb { position:absolute; bottom:20px; left:30px; z-index:2; font-family:'Inter',sans-serif; font-size:13px; color:rgba(255,255,255,0.8); display:flex; gap:8px; align-items:center; }
    .hero-breadcrumb a { color:rgba(255,255,255,0.8); text-decoration:none; transition:color 0.2s; }
    .hero-breadcrumb a:hover { color:#C49018; }
    .hero-breadcrumb .sep { color:rgba(255,255,255,0.5); font-size:12px; }
    .hero-breadcrumb .current { color:#fff; font-weight:500; }
    
    .td-badge { display:none; } /* Hidden as it's now in the hero */
    .td-title { display:none; }
    .td-route { display:none; }
    
    /* Tabs */
    .fa-tabs { border-bottom:1px solid #E5DDD0;margin-bottom:32px;display:flex;gap:32px;overflow-x:auto; }
    .fa-tabs .nav-link { background:none;border:none;padding:12px 0;font-size:12px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#6B6358;border-bottom:2px solid transparent;border-radius:0;white-space:nowrap;font-family:'Inter',sans-serif; }
    .fa-tabs .nav-link:hover { color:#C49018; }
    .fa-tabs .nav-link.active { color:#9E3A25;border-bottom-color:#9E3A25; }
    
    .td-content-box { background:#fff;padding:40px;border-radius:4px;box-shadow:0 2px 16px rgba(0,0,0,0.03);margin-bottom:32px; }
    .td-content-box h3 { font-family:'Cormorant Garant',serif;font-size:26px;font-weight:500;margin-bottom:20px;color:#1C1712;border-bottom:1px solid #E5DDD0;padding-bottom:12px; }
    .td-content-box p { font-size:15px;color:#4A4340;line-height:1.75;margin-bottom:16px; }
    .td-content-box ul { padding-left:20px;margin-bottom:20px; }
    .td-content-box ul li { font-size:14.5px;color:#4A4340;margin-bottom:8px;line-height:1.6; }
    
    .gallery-img-link { display:block;border-radius:3px;overflow:hidden;margin-bottom:20px;aspect-ratio:3/2; }
    .gallery-img-link img { width:100%;height:100%;object-fit:cover;transition:transform 0.4s ease; }
    .gallery-img-link:hover img { transform:scale(1.05); }

    /* Map Tooltip Override */
    .leaflet-popup-content-wrapper { border-radius:4px; font-family:'Inter',sans-serif; }
    .leaflet-popup-content { margin:13px 19px; font-size:13px; line-height:1.5; color:#1C1712; }
  </style>
</head>
<body>
<?php require_once 'includes/nav.php'; ?>

<section class="td-hero" style="background-image:url('<?= htmlspecialchars($heroImg ?? '') ?>');">
  <div class="overlay"></div>
  <div class="td-hero-content">
    <h1><?= htmlspecialchars($tour['title'] ?? '') ?></h1>
    <p class="duration"><?= htmlspecialchars($tour['duration_days'] ?? '1') ?> days in <?= htmlspecialchars($tour['country'] ?? 'East Africa') ?></p>
    <a href="#" class="btn-hero" data-open-planner="true" data-tour-id="<?= $tour['id'] ?? '' ?>" data-tour-title="<?= htmlspecialchars($tour['title'] ?? '') ?>">Start Planning Now</a>
  </div>
  <div class="hero-breadcrumb">
    <a href="index"><i class="fa fa-home"></i></a>
    <span class="sep">/</span>
    <a href="tours">Tours</a>
    <span class="sep">/</span>
    <span class="current"><?= htmlspecialchars($tour['title'] ?? '') ?></span>
  </div>
</section>

<div class="container mt-5" style="max-width:1280px;">
  <div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">

      <ul class="nav fa-tabs" id="tourTabs" role="tablist">
        <li class="nav-item">
          <button class="nav-link active" id="overview-tab" data-toggle="tab" data-target="#overview" type="button" role="tab">Overview</button>
        </li>
        <li class="nav-item">
          <button class="nav-link" id="inclusions-tab" data-toggle="tab" data-target="#inclusions" type="button" role="tab">Inclusions</button>
        </li>
        <li class="nav-item">
          <button class="nav-link" id="itinerary-tab" data-toggle="tab" data-target="#itinerary" type="button" role="tab">Itinerary (<?= count($steps) ?> Days)</button>
        </li>
        <li class="nav-item">
          <button class="nav-link" id="pricing-tab" data-toggle="tab" data-target="#pricing" type="button" role="tab">Pricing</button>
        </li>
        <li class="nav-item">
          <button class="nav-link" id="accommodations-tab" data-toggle="tab" data-target="#accommodations" type="button" role="tab">Accommodations</button>
        </li>
        <li class="nav-item">
          <button class="nav-link" id="map-tab" data-toggle="tab" data-target="#map" type="button" role="tab">Maps</button>
        </li>
        <li class="nav-item">
          <button class="nav-link" id="gallery-tab" data-toggle="tab" data-target="#gallery" type="button" role="tab">Gallery</button>
        </li>
      </ul>

      <div class="tab-content" id="tourTabsContent">
        <!-- OVERVIEW -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
          <div class="td-content-box">
            <h3>About This Safari</h3>
            <div style="font-size:15px;line-height:1.75;color:#4A4340;">
              <?= !empty($tour['description']) ? $tour['description'] : '<p>'.nl2br(htmlspecialchars($tour['excerpt'] ?? '')).'</p>' ?>
            </div>
            
            <?php if(!empty($tour['highlights'])): ?>
            <h3 class="mt-5">Journey Highlights</h3>
            <div style="font-size:15px;line-height:1.75;color:#4A4340;">
              <?= $tour['highlights'] ?>
            </div>
            <?php else: ?>
            <h3 class="mt-5">Journey Highlights</h3>
            <ul>
              <li>Exclusive game drives in custom 4x4 Land Cruisers</li>
              <li>Expert local guides with deep knowledge of the terrain</li>
              <li>Premium lodge and luxury tented camp accommodations</li>
              <li>Spectacular scenic views and photography opportunities</li>
            </ul>
            <?php endif; ?>
          </div>
        </div>

        <!-- ITINERARY -->
        <div class="tab-pane fade" id="itinerary" role="tabpanel">
          <div class="td-content-box">
            <h3>Day-by-Day Itinerary</h3>
            <div class="accordion fa-itinerary-accordion" id="itineraryAccordion">
              <?php foreach($steps as $idx => $step): ?>
              <div class="card">
                <div class="card-header" id="heading<?= $step['id'] ?>">
                  <button type="button" aria-expanded="true" style="cursor:default; pointer-events:none;">
                    <span class="day-badge">DAY <?= $step['step_number'] ?></span>
                    <?= htmlspecialchars($step['dest_name']) ?>
                    <?php if($step['nights_count']>1): ?><span style="font-size:11px;color:#6B6358;font-weight:400;letter-spacing:0;text-transform:none;">(<?= $step['nights_count'] ?> Nights)</span><?php endif; ?>
                  </button>
                </div>
                <div id="collapse<?= $step['id'] ?>" class="show">
                  <div class="card-body">
                    <div class="step-meta">
                      <?php if($step['acc_name']): ?>
                      <div class="step-meta-item"><i class="fa fa-bed"></i> <?= htmlspecialchars($step['acc_name']) ?></div>
                      <?php endif; ?>
                      <?php if($step['transit_mode']): ?>
                      <div class="step-meta-item"><i class="fa fa-car"></i> <?= htmlspecialchars($step['transit_mode']) ?></div>
                      <?php endif; ?>
                    </div>
                    <div class="step-desc">
                    <?= $step['step_description'] ?>
                  </div>
                    <?php if($step['step_image']): ?>
                    <img src="uploads/<?= htmlspecialchars($step['step_image']) ?>" alt="Day <?= $step['step_number'] ?>" class="step-img w-100 mt-2">
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- MAP -->
        <div class="tab-pane fade" id="map" role="tabpanel">
          <div class="td-content-box" style="padding:20px;">
            <div id="tourMap" style="height:500px; border-radius:4px; background:#e5e5e5; display:flex; align-items:center; justify-content:center;">
              <span style="color:#6B6358;font-size:14px;font-family:'Inter',sans-serif;">Loading Map...</span>
            </div>
            <p class="mt-3 mb-0 text-center" style="font-size:11.5px;color:#6B6358;">* Route is illustrative. Actual transit paths may vary.</p>
          </div>
        </div>

        <!-- ACCOMMODATIONS -->
        <div class="tab-pane fade" id="accommodations" role="tabpanel">
          <div class="td-content-box">
            <h3>Accommodations</h3>
            <?php 
              $hasAcc = false;
              foreach($steps as $s) { if($s['acc_id']) { $hasAcc = true; break; } }
            ?>
            <?php if ($hasAcc): ?>
              <div class="accordion fa-itinerary-accordion" id="accAccordion">
                <?php 
                $dayStart = 1;
                foreach($steps as $idx => $step): 
                  $nights = max(1, $step['nights_count']);
                  $dayEnd = $dayStart + $nights - 1;
                  $dayLabel = ($dayStart == $dayEnd) ? "Day $dayStart" : "Day $dayStart - $dayEnd";
                  $dayStart += $nights;
                  
                  if (!$step['acc_id']) continue;
                  
                  $img = $step['acc_image'] ? 'uploads/' . htmlspecialchars($step['acc_image']) : 'images/Filao/East Africa/Sopa Lodges/dining-by-the-waterhole-in-samburu-sopa-lodge.jpg';
                ?>
                <div class="card">
                  <div class="card-header" id="accHeading<?= $idx ?>">
                    <h5 class="mb-0">
                      <button class="btn btn-link" type="button" aria-expanded="true" style="cursor:default; pointer-events:none;">
                        <span class="day-label"><?= $dayLabel ?>:</span> <?= htmlspecialchars($step['acc_name'] ?? 'Accommodation TBD') ?>
                      </button>
                    </h5>
                  </div>
                  <div id="accCollapse<?= $idx ?>" class="show">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-5 mb-3 mb-md-0">
                          <img src="<?= $img ?>" alt="<?= htmlspecialchars($step['acc_name'] ?? 'Accommodation TBD') ?>" class="img-fluid rounded" style="width:100%;height:220px;object-fit:cover;">
                        </div>
                        <div class="col-md-7">
                          <h4 style="font-family:'Cormorant Garant',serif;font-size:22px;font-weight:600;color:#1C1712;"><?= htmlspecialchars($step['acc_name'] ?? 'Accommodation TBD') ?></h4>
                          <p style="font-size:14px;color:#6B6358;"><i class="fa fa-map-marker mr-1" style="color:#C49018;"></i> <?= htmlspecialchars($step['dest_name'] ?? 'Various') ?> &mdash; <?= $nights ?> Night(s)</p>
                            <?= html_entity_decode($step['acc_desc'] ?? 'Details about the accommodation will be provided upon booking.') ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p>Standard premium lodges and camps are selected based on availability.</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- GALLERY -->
        <div class="tab-pane fade" id="gallery" role="tabpanel">
          <div class="td-content-box">
            <h3>Tour Gallery</h3>
            <div class="row popup-gallery">
              <?php 
              if(count($gallery) > 0):
                foreach($gallery as $img): ?>
                <div class="col-md-4 col-6">
                  <a href="uploads/<?= htmlspecialchars($img['image_path'] ?? '') ?>" class="gallery-img-link" title="<?= htmlspecialchars($img['caption'] ?? '') ?>">
                    <img src="uploads/<?= htmlspecialchars($img['image_path'] ?? '') ?>" alt="Gallery Image" loading="lazy">
                  </a>
                </div>
                <?php endforeach;
              else: 
                // Fallback gallery
                $fallbackImages = [
                  'images/Filao/East Africa/pexels-droneafrica-13234382.jpg',
                  'images/Filao/East Africa/Maasai Mara/free-photo-of-majestic-african-elephant-in-kenyan-savanna (6).jpeg',
                  'images/Filao/East Africa/Amboseli/Sarova-Shaba-Safari-breakfast-in-the-wild.jpg',
                  'images/Filao/East Africa/pexels-kelly-17291020.jpg',
                  'images/Filao/East Africa/pexels-balazsimon-15993990.jpg',
                  'images/Filao/East Africa/Maasai Mara/free-photo-of-leopard-resting-in-tree-masai-mara-kenya (4).jpeg'
                ];
                foreach($fallbackImages as $fimg): ?>
                <div class="col-md-4 col-6">
                  <a href="<?= $fimg ?>" class="gallery-img-link">
                    <img src="<?= $fimg ?>" alt="Gallery Image" loading="lazy">
                  </a>
                </div>
                <?php endforeach;
              endif; ?>
            </div>
          </div>
        </div>

        <!-- INCLUSIONS -->
        <div class="tab-pane fade" id="inclusions" role="tabpanel">
          <div class="td-content-box">
            <div class="row">
              <div class="col-md-6 mb-4 mb-md-0">
                <h3 class="mb-4" style="color:#4A6B53;"><i class="fa fa-check-circle mr-2"></i> What's Included</h3>
                <div class="inc-list">
                  <?= !empty($tour['inclusions']) ? $tour['inclusions'] : '<ul><li>All park entrance fees and taxes</li><li>Full board accommodation as per itinerary</li><li>Exclusive use of 4x4 Safari Land Cruiser</li><li>Professional English-speaking driver/guide</li><li>Airport transfers</li><li>Drinking water during game drives</li><li>Flying Doctors emergency evacuation cover</li></ul>' ?>
                </div>
              </div>
              <div class="col-md-6">
                <h3 class="mb-4" style="color:#A14332;"><i class="fa fa-times-circle mr-2"></i> What's Excluded</h3>
                <div class="inc-list">
                  <?= !empty($tour['exclusions']) ? $tour['exclusions'] : '<ul><li>International flights and visa fees</li><li>Travel insurance (highly recommended)</li><li>Tips and gratuities for guides/staff</li><li>Items of a personal nature (laundry, drinks, phone calls)</li><li>Optional activities (e.g., hot air balloon safari)</li></ul>' ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- PRICING -->
        <div class="tab-pane fade" id="pricing" role="tabpanel">
          <div class="td-content-box">
            <h3>Tour Pricing</h3>
            <div class="table-responsive mt-4">
              <table class="table table-bordered" style="font-family:'Inter',sans-serif; color:#4A4340;">
                <thead style="background:#FAF8F4;">
                  <tr>
                    <th>Group Size</th>
                    <th>Price Per Adult (USD)</th>
                    <th>Price Per Child (USD)</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  $tiers = [
                      1 => '1 Person',
                      2 => '2 People',
                      3 => '3 People',
                      4 => '4 People',
                      5 => '5 People',
                      6 => '6 People'
                  ];
                  $hasPricing = false;
                  foreach($tiers as $num => $label) {
                      $adultCol = "price_{$num}_pax";
                      $childCol = "price_child_{$num}_pax";
                      
                      $adultP = $tour[$adultCol] ?? null;
                      $childP = $tour[$childCol] ?? null;
                      
                      if (((float)$adultP > 0) || ((float)$childP > 0)) {
                          $hasPricing = true;
                          $aDisp = ((float)$adultP > 0) ? "$" . number_format((float)$adultP) : "-";
                          $cDisp = ((float)$childP > 0) ? "$" . number_format((float)$childP) : "-";
                          echo "<tr><td><strong>{$label}</strong></td><td>{$aDisp}</td><td>{$cDisp}</td></tr>";
                      }
                  }
                  if (!$hasPricing) {
                      echo "<tr><td colspan='3'>Please contact us for detailed pricing.</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
              <p class="mt-3 text-muted" style="font-size:13px;">* Prices are subject to change based on seasonality and availability. Please request a quote for exact pricing for your travel dates. Children are considered under 12 years of age.</p>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
      <div class="fa-enquiry-box">
        <div class="price-label">FROM</div>
        <div class="price-main"><?= $price ?></div>
        <div class="price-per">per person sharing &bull; <?= $tour['duration_days'] ?? 1 ?> Days</div>
        
        <div id="tourEnquiryFeedback" class="alert" style="display:none;font-size:13px;padding:12px;"></div>
        <form id="tourEnquiryForm" action="#" method="POST" class="mt-4">
          <input type="hidden" name="type" value="tour_enquiry">
          <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
          <input type="hidden" name="tour_title" value="<?= htmlspecialchars($tour['title']) ?>">
          <div class="form-group mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="first_name" class="form-control" placeholder="Jane Doe" required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="jane@example.com" required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label">Phone / WhatsApp</label>
            <input type="tel" id="tourPhone" class="form-control" placeholder="" style="width:100%;">
          </div>
          <div class="form-group mb-3">
            <label class="form-label">Exact Travel Date</label>
            <input type="date" name="travel_date" class="form-control" required>
          </div>
          <div class="row mb-3">
            <div class="col-6 form-group">
              <label class="form-label">Adults</label>
              <input type="number" name="adults" class="form-control" min="1" value="2" required>
            </div>
            <div class="col-6 form-group">
              <label class="form-label">Children</label>
              <input type="number" name="children" class="form-control" min="0" value="0">
            </div>
            <div class="col-12 mt-1">
              <small style="font-size:11px; color:#9E3A25;">* Children are below 12 years.</small>
            </div>
          </div>
          <div class="form-group mb-4">
            <label class="form-label">Additional Details</label>
            <textarea name="message" class="form-control" rows="3" placeholder="Tell us about your dream safari..."></textarea>
          </div>
          <button type="submit" id="tourEnquiryBtn" class="btn-enquiry">Send Enquiry</button>
          <div class="text-center mt-3" style="font-size:11px;color:#6B6358;font-family:'Inter',sans-serif;">Your enquiry goes directly to our safari specialists. No payment required now.</div>
        </form>

        <div class="fa-trust-badges">
          <div class="fa-trust-badge"><i class="fa fa-shield"></i> 100% Financial Protection</div>
          <div class="fa-trust-badge"><i class="fa fa-refresh"></i> Flexible Booking Terms</div>
          <div class="fa-trust-badge"><i class="fa fa-comments"></i> 24/7 On-Safari Support</div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.magnific-popup.min.js"></script>
<script src="assets/js/filao-nav.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet-polylinedecorator@1.6.0/dist/leaflet.polylineDecorator.min.js"></script>
<script src="js/start-planning.js"></script>
<script>
$(document).ready(function() {
  // Magnific Popup for Gallery
  $('.popup-gallery').magnificPopup({
    delegate: 'a',
    type: 'image',
    tLoading: 'Loading image #%curr%...',
    mainClass: 'mfp-img-mobile',
    gallery: { enabled: true, navigateByImgClick: true, preload: [0,1] },
    image: { tError: '<a href="%url%">The image #%curr%</a> could not be loaded.', titleSrc: function(item) { return item.el.attr('title') || ''; } }
  });

  // Map Initialization (only when tab is shown to fix Leaflet rendering issue in hidden div)
  var mapInitialized = false;
  $('button[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    if (e.target.id === 'map-tab' && !mapInitialized) {
      initMap();
      mapInitialized = true;
    }
  });

  function initMap() {
    var waypoints = <?= $waypointsJson ?>;
    if(waypoints.length > 0) {
      document.getElementById('tourMap').innerHTML = ''; // Clear loading text
      var map = L.map('tourMap');
      
      // CartoDB Voyager tiles (clean, professional look)
      L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
      }).addTo(map);
      
      var coords = waypoints.map(function(wp) { return [wp.lat, wp.lng]; });
      
      if (coords.length > 1) {
          var forwardCoords = coords.slice(0, coords.length - 1);
          var returnCoords = [coords[coords.length - 2], coords[coords.length - 1]];

          // Forward Journey
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

          // Return Journey (Dashed, different color)
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
      
    } else {
      document.getElementById('tourMap').innerHTML = '<span style="color:#6B6358;font-size:14px;font-family:\'Inter\',sans-serif;">Map data not available for this tour.</span>';
    }
  }

  // Tour Enquiry AJAX Form
  var tourPhoneInput = document.getElementById('tourPhone');
  var tourPhoneIti = null;
  if (tourPhoneInput && window.intlTelInput) {
    tourPhoneIti = window.intlTelInput(tourPhoneInput, {
      initialCountry: "auto",
      autoPlaceholder: "off",
      separateDialCode: true,
      geoIpLookup: function(callback) {
        fetch("https://ipapi.co/json").then(r => r.json()).then(d => callback(d.country_code)).catch(() => callback("us"));
      },
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.5/js/utils.js"
    });
  }

  $('#tourEnquiryForm').on('submit', function(e) {
    e.preventDefault();
    var form = this;
    var btn = $('#tourEnquiryBtn');
    var feedback = $('#tourEnquiryFeedback');
    
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Sending...');
    feedback.hide();
    
    var data = new FormData(form);
    
    // Add full international phone number
    if (tourPhoneIti && tourPhoneInput.value.trim() !== '') {
      data.append('phone', tourPhoneIti.getNumber());
    }

    var basePath = window.location.hostname === 'localhost' ? '/filao' : '';
    fetch(basePath + '/handlers/enquiry.php', { method: 'POST', body: data })
      .then(r => {
        if (!r.ok) {
          return r.text().then(t => { throw new Error('HTTP ' + r.status + ': ' + t.substring(0, 300)); });
        }
        return r.text();
      })
      .then(txt => {
        try { var res = JSON.parse(txt); } catch(e) { throw new Error('Invalid JSON: ' + txt.substring(0, 300)); }
        if(res.success) {
          feedback.hide();
          form.reset();
          btn.html('Send Enquiry').prop('disabled', false);
          // Success toast popup
          var toast = $('<div class="fa-toast-success"><i class="fa fa-check-circle"></i> ' + res.message + '</div>');
          toast.css({
            position:'fixed', top:'30px', right:'30px', zIndex:99999,
            background:'linear-gradient(135deg,#628C52,#4e7040)', color:'#fff',
            padding:'18px 28px', borderRadius:'12px', fontFamily:"'Inter',sans-serif",
            fontSize:'14px', boxShadow:'0 8px 32px rgba(98,140,82,0.35)',
            display:'flex', alignItems:'center', gap:'10px', maxWidth:'420px',
            animation:'slideInRight 0.4s ease'
          });
          $('body').append(toast);
          setTimeout(function(){ toast.fadeOut(400, function(){ toast.remove(); }); }, 5000);
        } else {
          feedback.removeClass('alert-success').addClass('alert-danger')
            .css({background:'rgba(180,30,30,0.07)', borderColor:'rgba(180,30,30,0.3)', color:'#b41e1e'})
            .html('<i class="fa fa-exclamation-circle mr-2"></i> ' + res.message).show();
          btn.prop('disabled', false).html('Send Enquiry');
        }
      })
      .catch(err => {
        feedback.removeClass('alert-success').addClass('alert-danger')
          .css({background:'rgba(180,30,30,0.07)', borderColor:'rgba(180,30,30,0.3)', color:'#b41e1e'})
          .html('<i class="fa fa-exclamation-circle mr-2"></i> Error: ' + err.message).show();
        btn.prop('disabled', false).html('Send Enquiry');
      });
  });
});
</script>
</body>
</html>
