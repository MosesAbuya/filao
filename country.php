<?php
require_once 'includes/db.php';
$pdo = getPDO();

$countryName = isset($_GET['name']) ? trim($_GET['name']) : '';
if (empty($countryName)) {
    header('Location: destinations.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, slug, country, region_type, featured_image, latitude, longitude FROM destinations WHERE country = ? ORDER BY name ASC");
$stmt->execute([$countryName]);
$destinations = $stmt->fetchAll();

$dests = [];
foreach($destinations as $dest) {
  // Handle image path
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
    'link' => 'destinations/'.$dest['slug'],
    'region' => $dest['region_type'] ?: 'Destination',
    'lat' => (float)$dest['latitude'],
    'lng' => (float)$dest['longitude']
  ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title><?= htmlspecialchars($countryName) ?> Destinations | Filao Adventures</title>
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
    .dest-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px; }
  </style>
</head>
<body>
<?php require_once 'includes/nav.php'; ?>

<!-- Page Hero -->
<section class="fa-page-hero" style="background-image:url('images/Filao/East Africa/pexels-droneafrica-15373902.jpg');">
  <div class="overlay"></div>
  <div class="container fa-page-hero-content" style="max-width:1280px;">
    <h1>Destinations in <?= htmlspecialchars($countryName) ?></h1>
    <div class="breadcrumb-fa">
      <a href="index">Home</a>
      <span class="bc-sep">&#8250;</span>
      <a href="destinations">Destinations</a>
      <span class="bc-sep">&#8250;</span>
      <span class="bc-current"><?= htmlspecialchars($countryName) ?></span>
    </div>
  </div>
</section>

<!-- Content -->
<section class="section-pad bg-cream">
  <div class="container" style="max-width:1280px;">
    
    <div class="row">
      <div class="col-lg-8 pr-lg-5">
      
        <!-- About Country -->
        <div style="background:#fff;padding:40px;border-radius:4px;box-shadow:0 2px 16px rgba(0,0,0,0.03);margin-bottom:48px;">
          <h2 style="font-family:'Cormorant Garant',serif;font-size:32px;color:#1C1712;margin-bottom:24px;">About <?= htmlspecialchars($countryName) ?></h2>
          <div style="font-size:15.5px;color:#4A4340;line-height:1.8;">
            <p><?= htmlspecialchars($countryName) ?> is a spectacular travel destination that offers unforgettable experiences. Whether you are looking for breathtaking wildlife encounters, stunning landscapes, or pure relaxation, this region has it all. At Filao Adventures, we craft tailor-made journeys that allow you to explore <?= htmlspecialchars($countryName) ?> at your own pace, staying at the finest hand-picked lodges and camps.</p>
            <p>Below you will find the specific regions and parks we visit within <?= htmlspecialchars($countryName) ?>. Speak to one of our safari experts to start designing your perfect itinerary.</p>
          </div>
        </div>

        <!-- Destinations Map -->
        <div style="margin-bottom:64px;border-radius:6px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.08);border:1px solid #E5DDD0;">
          <div style="background:#1C1712;color:#fff;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;">
            <h3 style="font-family:'Cormorant Garant',serif;font-size:22px;margin:0;"><i class="fa fa-map-marker mr-2" style="color:#C49018;"></i> Map of <?= htmlspecialchars($countryName) ?> Destinations</h3>
            <span style="font-size:12px;font-family:'Inter',sans-serif;color:rgba(255,255,255,0.7);">Interactive Map</span>
          </div>
          <div id="destMap" style="height:450px;width:100%;background:#e5e5e5;display:flex;align-items:center;justify-content:center;">
            <span style="color:#6B6358;font-size:14px;font-family:'Inter',sans-serif;"><i class="fa fa-spinner fa-spin mr-2"></i> Loading map...</span>
          </div>
        </div>
        
        <div class="fa-section-heading">
          <h2>Places to Visit in <?= htmlspecialchars($countryName) ?></h2>
        </div>
        
        <div class="dest-grid">
          <?php if(count($dests) > 0): ?>
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
          <?php else: ?>
            <div class="col-12 text-center" style="grid-column: 1 / -1;">
                <p>No destinations found for this country.</p>
            </div>
          <?php endif; ?>
        </div>

      </div>
      
      <!-- Right Sidebar -->
      <div class="col-lg-4">
        <div style="border:1.5px solid #C49018;padding:32px;background:#fff;position:sticky;top:150px;">
          <h3 style="font-family:'Cormorant Garant',serif;font-size:24px;color:#1C1712;margin-bottom:24px;border-bottom:1px solid #E5DDD0;padding-bottom:12px;">Country Quick Facts</h3>
          
          <div class="mb-4">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;display:block;margin-bottom:4px;">Country</span>
            <span style="font-size:15px;color:#1C1712;font-weight:500;"><i class="fa fa-map-marker" style="color:#C49018;width:16px;"></i> <?= htmlspecialchars($countryName) ?></span>
          </div>
          
          <div class="mb-5">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;display:block;margin-bottom:4px;">Destinations</span>
            <span style="font-size:15px;color:#1C1712;font-weight:500;"><i class="fa fa-map" style="color:#C49018;width:16px;"></i> <?= count($dests) ?> Regions/Parks</span>
          </div>

          <button data-open-planner="true" data-dest-name="<?= htmlspecialchars($countryName) ?>" class="tc-cta" style="width:100%;text-align:center;border:none;cursor:pointer;">Plan a Trip Here</button>
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
<script src="js/start-planning.js"></script>
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
      var center = group.getBounds().getCenter();
      map.setView(center, 5); // Use fixed regional zoom
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
