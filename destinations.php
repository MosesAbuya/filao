<?php
require_once 'includes/db.php';
$pdo = getPDO();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Destinations By Region &mdash; Filao Adventures</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="assets/favicon_io/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css?v=<?= time() ?>">
  <style>
    .dest-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px; }
  </style>
</head>
<body>
<?php require_once 'includes/nav.php'; ?>

<?php
$currentRegion = isset($_GET['region']) ? trim($_GET['region']) : null;
$heroTitle = $currentRegion ? "Destinations in " . htmlspecialchars($currentRegion) : "Explore By Region";
$heroImg = 'images/Filao/East Africa/pexels-droneafrica-15373902.jpg';

$stmt = $pdo->query("SELECT name, slug, country, region, latitude, longitude, featured_image FROM destinations WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND latitude != 0 AND longitude != 0");
$mapDestinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Find hero image if region is selected
if ($currentRegion && isset($navRegions[$currentRegion])) {
    foreach ($navRegions[$currentRegion] as $c) {
        if (!empty($c['region_img'])) {
            $r_img = $c['region_img'];
            $heroImg = str_starts_with($r_img, 'images/') ? $r_img : 'uploads/' . $r_img;
            break;
        }
        if (!empty($c['featured_image'])) {
            $img = $c['featured_image'];
            if (str_starts_with($img, 'images/')) {
                $heroImg = $img;
            } elseif (str_starts_with($img, 'destinations/') || str_starts_with($img, 'countries/') || str_starts_with($img, 'regions/')) {
                $heroImg = 'uploads/' . $img;
            } else {
                $heroImg = 'uploads/destinations/' . $img;
            }
            break;
        }
    }
}
?>

<!-- Page Hero -->
<section class="td-hero" style="background-image:url('<?= htmlspecialchars($heroImg) ?>');">
  <div class="overlay"></div>
  <div class="td-hero-content">
    <h1><?= htmlspecialchars($heroTitle) ?></h1>
  </div>
  <div class="hero-breadcrumb">
      <a href="index"><i class="fa fa-home"></i></a>
      <span class="sep">/</span>
      <?php if ($currentRegion): ?>
          <a href="destinations">Regions</a>
          <span class="sep">/</span>
          <span class="current"><?= htmlspecialchars($currentRegion) ?></span>
      <?php else: ?>
          <span class="current">Regions</span>
      <?php endif; ?>
  </div>
</section>

<section class="section-pad bg-cream">
  <div class="container" style="max-width:1280px;">
    
    <?php if (!$currentRegion): ?>
      <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
          <div class="fa-section-heading">
            <span class="eyebrow">Our World</span>
            <h2>Explore the Continents</h2>
            <p>From the endless savannahs of East Africa where the Great Migration unfolds, to the pristine white sands of the Indian Ocean and the ultra-modern skyline of Dubai, Filao Adventures curates extraordinary journeys across the globe.</p>
            <p>Select a region below to discover the magical destinations, carefully crafted itineraries, and unforgettable experiences that await you.</p>
          </div>
        </div>
        <div class="col-lg-6">
          <div id="destinationsMap" style="width: 100%; height: 400px; border-radius: 12px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); z-index: 1;"></div>
        </div>
      </div>
      
      <div class="dest-grid">
          <?php foreach ($navRegions as $regionName => $countriesList): 
              $img = 'images/placeholder.jpg';
              foreach ($countriesList as $c) {
                  if (!empty($c['region_img'])) {
                      $img = str_starts_with($c['region_img'], 'images/') ? $c['region_img'] : 'uploads/' . $c['region_img'];
                      break;
                  }
                  if (!empty($c['featured_image'])) {
                      $img = str_starts_with($c['featured_image'], 'images/') ? $c['featured_image'] : (str_starts_with($c['featured_image'], 'destinations/') ? 'uploads/' . $c['featured_image'] : 'uploads/destinations/' . $c['featured_image']);
                      break;
                  }
              }
          ?>
              <a href="destinations?region=<?= urlencode($regionName) ?>" class="fa-dest-card">
                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($regionName) ?>" loading="lazy">
                <div class="dc-overlay"></div>
                <div class="dc-text">
                  <div class="dc-name"><?= htmlspecialchars($regionName) ?></div>
                </div>
              </a>
          <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="dest-grid">
          <?php 
          if (isset($navRegions[$currentRegion])) {
              $uniqueCountries = [];
              foreach ($navRegions[$currentRegion] as $c) {
                  if (!isset($uniqueCountries[$c['country']])) {
                      $uniqueCountries[$c['country']] = $c['featured_image'];
                  }
              }
              
              foreach ($uniqueCountries as $cName => $cImg): 
                  $imgUrl = 'images/placeholder.jpg';
                  if (!empty($cImg)) {
                      $imgUrl = str_starts_with($cImg, 'images/') ? $cImg : (str_starts_with($cImg, 'destinations/') ? 'uploads/' . $cImg : 'uploads/destinations/' . $cImg);
                  }
              ?>
                  <a href="country?name=<?= urlencode($cName) ?>" class="fa-dest-card">
                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($cName) ?>" loading="lazy">
                    <div class="dc-overlay"></div>
                    <div class="dc-text">
                      <div class="dc-region"><?= htmlspecialchars($currentRegion) ?></div>
                      <div class="dc-name"><?= htmlspecialchars($cName) ?></div>
                    </div>
                  </a>
              <?php endforeach; 
          } else {
              echo "<p>No destinations found for this region.</p>";
          }
          ?>
      </div>
      <?php endif; ?>
      
    </div>

  </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="assets/js/filao-nav.js"></script>
<script src="js/start-planning.js"></script>

<!-- Leaflet Map Scripts -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('destinationsMap')) {
      var map = L.map('destinationsMap').setView([0, 20], 3); // Centered over Africa roughly

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 18,
          attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      var destinations = <?= json_encode($mapDestinations) ?>;
      
      var bounds = [];
      destinations.forEach(function(dest) {
        if (dest.latitude && dest.longitude) {
          var marker = L.marker([parseFloat(dest.latitude), parseFloat(dest.longitude)]).addTo(map);
          var popupContent = '<div style="text-align:center;"><b>' + dest.name + '</b><br>' + dest.country + '<br><a href="destination-detail?slug=' + dest.slug + '" style="display:inline-block; margin-top:5px; color:#C49018;">View Destination</a></div>';
          marker.bindPopup(popupContent);
          bounds.push([parseFloat(dest.latitude), parseFloat(dest.longitude)]);
        }
      });
      if (bounds.length > 0) {
        map.fitBounds(bounds, {padding: [30, 30]});
      }
    }
  });
</script>
</body>
</html>
