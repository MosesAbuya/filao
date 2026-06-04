<?php
require_once 'includes/db.php';
$pdo = getPDO();
$destinations = $pdo->query("SELECT id, name, slug, country, region_type, featured_image, latitude, longitude FROM destinations ORDER BY name ASC")->fetchAll();

$dests = [];
foreach($destinations as $dest) {
  // Determine tab based on country or region
  $tab = 'global';
  if (in_array($dest['country'], ['Kenya', 'Tanzania', 'Uganda', 'Rwanda'])) {
    if (str_contains(strtolower($dest['region_type']), 'beach') || str_contains(strtolower($dest['region_type']), 'island') || $dest['slug'] === 'zanzibar-island' || $dest['slug'] === 'diani-beach' || $dest['slug'] === 'mombasa-old-town') {
        $tab = 'ocean';
    } else {
        $tab = 'east';
    }
  } else if (in_array($dest['country'], ['Maldives', 'Seychelles', 'Mauritius'])) {
    $tab = 'ocean';
  }

  // Handle image path (some might be in uploads, some might be direct relative paths from the script)
  $img = $dest['featured_image'];
  if (!empty($img) && !str_starts_with($img, 'http') && !str_starts_with($img, 'images/')) {
      $img = 'uploads/' . $img;
  }
  if (empty($img)) {
      $img = 'images/Filao/East Africa/pexels-droneafrica-15373902.jpg'; // fallback
  }

  $dests[] = [
    'id' => $dest['id'],
    'name' => $dest['name'],
    'country' => $dest['country'],
    'img' => $img,
    'link' => 'destination-detail.php?slug='.$dest['slug'],
    'region' => $dest['region_type'] ?: 'Destination',
    'tab' => $tab,
    'lat' => (float)$dest['latitude'],
    'lng' => (float)$dest['longitude']
  ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Safari Destinations &mdash; Africa &amp; Beyond | Filao Adventures</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="assets/favicon_io/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/animate.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css">
  <style>
    .dest-tabs { display:flex;gap:32px;justify-content:center;margin-bottom:48px;border-bottom:1px solid #E5DDD0; }
    .dest-tabs .nav-link { background:none;border:none;padding:12px 0;font-size:11.5px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#6B6358;border-bottom:2px solid transparent;border-radius:0;font-family:'Inter',sans-serif; }
    .dest-tabs .nav-link:hover { color:#C49018; }
    .dest-tabs .nav-link.active { color:#9E3A25;border-bottom-color:#9E3A25; }
    
    .dest-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px; }
  </style>
</head>
<body>
<?php require_once 'includes/nav.php'; ?>

<!-- Page Hero -->
<section class="fa-page-hero" style="background-image:url('images/Filao/East Africa/pexels-droneafrica-15373902.jpg');">
  <div class="overlay"></div>
  <div class="container fa-page-hero-content" style="max-width:1280px;">
    <h1>Explore Our Destinations</h1>
    <div class="breadcrumb-fa">
      <a href="index.php">Home</a>
      <span class="bc-sep">&#8250;</span>
      <span class="bc-current">Destinations</span>
    </div>
  </div>
</section>

<!-- Content -->
<section class="section-pad bg-cream">
  <div class="container" style="max-width:1280px;">
    
    <!-- Global Destinations Map -->
    <div style="margin-bottom:64px;border-radius:6px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.08);border:1px solid #E5DDD0;">
      <div style="background:#1C1712;color:#fff;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;">
        <h3 style="font-family:'Cormorant Garant',serif;font-size:22px;margin:0;"><i class="fa fa-map-marker mr-2" style="color:#C49018;"></i> Explore African Destinations</h3>
        <span style="font-size:12px;font-family:'Inter',sans-serif;color:rgba(255,255,255,0.7);">Interactive Map</span>
      </div>
      <div id="destMap" style="height:450px;width:100%;background:#e5e5e5;display:flex;align-items:center;justify-content:center;">
        <span style="color:#6B6358;font-size:14px;font-family:'Inter',sans-serif;"><i class="fa fa-spinner fa-spin mr-2"></i> Loading map...</span>
      </div>
    </div>
    <ul class="nav dest-tabs" id="destTabs" role="tablist">
      <li class="nav-item">
        <button class="nav-link active" id="all-tab" data-toggle="tab" data-target="#tab-all" type="button" role="tab">All Destinations</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" id="east-tab" data-toggle="tab" data-target="#tab-east" type="button" role="tab">East Africa</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" id="global-tab" data-toggle="tab" data-target="#tab-global" type="button" role="tab">Global Luxury</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" id="ocean-tab" data-toggle="tab" data-target="#tab-ocean" type="button" role="tab">Indian Ocean &amp; Beaches</button>
      </li>
    </ul>

    <div class="tab-content">
      <!-- ALL -->
      <div class="tab-pane fade show active" id="tab-all" role="tabpanel">
        <div class="dest-grid">
          <?php foreach($dests as $d): ?>
          <a href="<?= $d['link'] ?>" class="fa-dest-card">
            <img src="<?= htmlspecialchars($d['img']) ?>" alt="<?= htmlspecialchars($d['name']) ?>" loading="lazy">
            <div class="dc-overlay"></div>
            <span class="dc-country-badge"><?= htmlspecialchars($d['country']) ?></span>
            <div class="dc-text">
              <div class="dc-region"><?= htmlspecialchars($d['region']) ?></div>
              <div class="dc-name"><?= htmlspecialchars($d['name']) ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      
      <!-- EAST AFRICA -->
      <div class="tab-pane fade" id="tab-east" role="tabpanel">
        <div class="dest-grid">
          <?php foreach($dests as $d): 
            if($d['tab'] !== 'east') continue;
          ?>
          <a href="<?= $d['link'] ?>" class="fa-dest-card">
            <img src="<?= htmlspecialchars($d['img']) ?>" alt="<?= htmlspecialchars($d['name']) ?>" loading="lazy">
            <div class="dc-overlay"></div>
            <span class="dc-country-badge"><?= htmlspecialchars($d['country']) ?></span>
            <div class="dc-text">
              <div class="dc-region"><?= htmlspecialchars($d['region']) ?></div>
              <div class="dc-name"><?= htmlspecialchars($d['name']) ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- GLOBAL LUXURY -->
      <div class="tab-pane fade" id="tab-global" role="tabpanel">
        <div class="dest-grid">
          <?php foreach($dests as $d): 
            if($d['tab'] !== 'global') continue;
          ?>
          <a href="<?= $d['link'] ?>" class="fa-dest-card">
            <img src="<?= htmlspecialchars($d['img']) ?>" alt="<?= htmlspecialchars($d['name']) ?>" loading="lazy">
            <div class="dc-overlay"></div>
            <span class="dc-country-badge"><?= htmlspecialchars($d['country']) ?></span>
            <div class="dc-text">
              <div class="dc-region"><?= htmlspecialchars($d['region']) ?></div>
              <div class="dc-name"><?= htmlspecialchars($d['name']) ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- INDIAN OCEAN -->
      <div class="tab-pane fade" id="tab-ocean" role="tabpanel">
        <div class="dest-grid">
          <?php foreach($dests as $d): 
            if($d['tab'] !== 'ocean') continue;
          ?>
          <a href="<?= $d['link'] ?>" class="fa-dest-card">
            <img src="<?= htmlspecialchars($d['img']) ?>" alt="<?= htmlspecialchars($d['name']) ?>" loading="lazy">
            <div class="dc-overlay"></div>
            <span class="dc-country-badge"><?= htmlspecialchars($d['country']) ?></span>
            <div class="dc-text">
              <div class="dc-region"><?= htmlspecialchars($d['region']) ?></div>
              <div class="dc-name"><?= htmlspecialchars($d['name']) ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

  </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="assets/js/filao-nav.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
$(document).ready(function() {
  var dests = <?= json_encode($dests) ?>;
  var mapContainer = document.getElementById('destMap');
  
  if(dests.length > 0) {
    mapContainer.innerHTML = ''; // clear loading text
    var map = L.map('destMap');
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
      attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
      subdomains: 'abcd',
      maxZoom: 20
    }).addTo(map);
    
    var markers = [];
    dests.forEach(function(d) {
      if(d.lat && d.lng) {
        var iconHtml = '<div style="background:#C49018;color:#fff;width:24px;height:24px;border-radius:50%;border:2px solid #fff;box-shadow:0 3px 6px rgba(0,0,0,0.3);"></div>';
        var icon = L.divIcon({ className: '', html: iconHtml, iconSize: [24, 24], iconAnchor: [12, 12] });
        var marker = L.marker([d.lat, d.lng], {icon: icon}).addTo(map)
          .bindPopup('<div style="text-align:center;"><img src="' + d.img + '" style="width:100%;height:100px;object-fit:cover;border-radius:4px;margin-bottom:8px;"><br><strong style="font-family:\'Cormorant Garant\',serif;font-size:18px;color:#1C1712;">' + d.name + '</strong><br><a href="' + d.link + '" style="display:inline-block;margin-top:8px;font-size:11px;font-family:\'Inter\',sans-serif;color:#C49018;text-transform:uppercase;letter-spacing:0.1em;font-weight:700;">Explore &rarr;</a></div>');
        markers.push(marker);
      }
    });
    
    if(markers.length > 0) {
      var group = new L.featureGroup(markers);
      map.fitBounds(group.getBounds().pad(0.1));
    } else {
      map.setView([-1.286389, 36.817223], 5); // default to Nairobi
    }
  } else {
    mapContainer.innerHTML = '<span style="color:#6B6358;font-size:14px;font-family:\'Inter\',sans-serif;">No map data available.</span>';
  }
});
</script>
</body>
</html>
