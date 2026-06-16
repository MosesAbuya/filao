<?php
require_once 'includes/db.php';
$pdo = getPDO();
$where = ["t.status='published'"];
$params = [];

if (isset($_GET['joining']) && $_GET['joining'] == '1') {
    $where[] = "t.is_joining_tour=1";
}

if (!empty($_GET['dur']) && is_array($_GET['dur'])) {
    $durConditions = [];
    foreach ($_GET['dur'] as $dur) {
        if ($dur === '1-3') $durConditions[] = "(t.duration_days BETWEEN 1 AND 3)";
        elseif ($dur === '4-5') $durConditions[] = "(t.duration_days BETWEEN 4 AND 5)";
        elseif ($dur === '6-7') $durConditions[] = "(t.duration_days BETWEEN 6 AND 7)";
        elseif ($dur === '8+') $durConditions[] = "(t.duration_days >= 8)";
    }
    if (!empty($durConditions)) $where[] = "(" . implode(" OR ", $durConditions) . ")";
}

// Minimal placeholder for category filtering logic - ideally needs JOIN with tour_activities/tour_categories
// For now, if we had a category field, we'd filter here. Let's skip deep category join to keep it simple, 
// unless we can use `category` mapping if it existed.
// Wait, tours don't have a simple 'category' field in the schema, they map via `tour_activities`.

if (!empty($_GET['price']) && is_array($_GET['price'])) {
    $priceConditions = [];
    foreach ($_GET['price'] as $p) {
        if ($p === '500-1500') $priceConditions[] = "(t.price_from_usd BETWEEN 500 AND 1500)";
        elseif ($p === '1500-3000') $priceConditions[] = "(t.price_from_usd BETWEEN 1500 AND 3000)";
        elseif ($p === '3000+') $priceConditions[] = "(t.price_from_usd >= 3000)";
    }
    if (!empty($priceConditions)) $where[] = "(" . implode(" OR ", $priceConditions) . ")";
}

$whereSql = implode(" AND ", $where);
$stmt = $pdo->prepare("
    SELECT t.id, t.title, t.slug, t.duration_days, t.price_from_usd, t.excerpt, t.description, t.featured_image, t.status 
    FROM tours t 
    JOIN activity_tour at ON t.id = at.tour_id
    JOIN activities a ON at.activity_id = a.id
    WHERE $whereSql AND a.slug = 'safari'
    ORDER BY t.duration_days ASC
");
$stmt->execute($params);
$tours = $stmt->fetchAll();

function getTourRouteT($pdo,$id){
  $s=$pdo->prepare("SELECT d.name FROM itinerary_steps ist JOIN destinations d ON d.id=ist.destination_id WHERE ist.tour_id=? ORDER BY ist.step_number ASC");
  $s->execute([$id]);
  $names = $s->fetchAll(PDO::FETCH_COLUMN);
  $names = array_map('htmlspecialchars', $names);
  return implode(' &rarr; ', $names);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Safari Experiences &mdash; Filao Adventures</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Browse Filao Adventures' expertly crafted safari tours, beach holidays and international packages. Find your perfect journey.">
  <link rel="icon" href="assets/favicon_io/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/animate.css">
  <link rel="stylesheet" href="css/owl.carousel.min.css">
  <link rel="stylesheet" href="css/owl.theme.default.min.css">
  <link rel="stylesheet" href="css/flaticon.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css">
  <style>
    .filter-sidebar { background:#fff; padding:28px; border:1px solid #E5DDD0; border-radius:4px; position:sticky;top:160px; }
    .filter-sidebar h5 { font-family:'Inter',sans-serif;font-size:10px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:#C49018;margin-bottom:16px; }
    .filter-sidebar .filter-group { margin-bottom:20px; padding-bottom:18px; border-bottom:1px solid #EDE8E0; }
    .filter-sidebar label { display:flex;align-items:center;gap:8px;font-size:13.5px;color:#1C1712;cursor:pointer;padding:3px 0; }
    .filter-sidebar input[type=checkbox] { accent-color:#C49018; }
    .filter-count { font-size:11px;color:#6B6358;margin-left:auto; }
    .btn-filter { width:100%;background:#C49018;color:#fff;border:none;padding:11px;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;cursor:pointer;border-radius:3px;font-family:'Inter',sans-serif;margin-bottom:8px; }
    .btn-filter-clear { display:block;text-align:center;font-size:11px;color:#6B6358;text-decoration:none; }
    .results-info { font-size:13px;color:#6B6358;margin-bottom:24px;font-family:'Inter',sans-serif; }
  </style>
</head>
<body>
<?php require_once 'includes/nav.php'; ?>

<!-- Page Hero -->
<section class="fa-page-hero" style="background-image:url('images/Filao/East Africa/pexels-droneafrica-13234382.jpg');">
  <div class="overlay"></div>
  <div class="container fa-page-hero-content" style="max-width:1280px;">
    <h1>Safari Experiences</h1>
    <div class="breadcrumb-fa">
      <a href="index">Home</a>
      <span class="bc-sep">&#8250;</span>
      <span class="bc-current">Safaris</span>
    </div>
  </div>
</section>

<!-- Content -->
<section class="section-pad bg-cream">
  <div class="container" style="max-width:1280px;">
    <div class="row">
      <!-- Filter Sidebar -->
      <div class="col-lg-3 col-md-4 mb-5 d-none d-md-block">
        <form method="GET" action="safaris.php" id="tours-filter-form">
          <div class="filter-sidebar">
            <h5>Filter Tours</h5>
            <div class="filter-group">
              <div class="mb-2" style="font-size:10.5px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;">Duration</div>
              <label><input type="checkbox" name="dur[]" value="1-3"> 1&ndash;3 Days <span class="filter-count">(2)</span></label>
              <label><input type="checkbox" name="dur[]" value="4-5"> 4&ndash;5 Days <span class="filter-count">(3)</span></label>
              <label><input type="checkbox" name="dur[]" value="6-7"> 6&ndash;7 Days <span class="filter-count">(1)</span></label>
              <label><input type="checkbox" name="dur[]" value="8+"> 8+ Days <span class="filter-count">(1)</span></label>
            </div>
            <div class="filter-group">
              <div class="mb-2" style="font-size:10.5px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;">Joining Tours</div>
              <label><input type="checkbox" name="joining" value="1" <?= isset($_GET['joining']) && $_GET['joining'] == '1' ? 'checked' : '' ?>> Joining Tours Only</label>
            </div>
            <div class="filter-group" style="border-bottom:none;">
              <div class="mb-2" style="font-size:10.5px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;">Budget</div>
              <label><input type="checkbox" name="price[]" value="500-1500"> $500 &ndash; $1,500</label>
              <label><input type="checkbox" name="price[]" value="1500-3000"> $1,500 &ndash; $3,000</label>
              <label><input type="checkbox" name="price[]" value="3000+"> $3,000+</label>
            </div>
            <button type="submit" class="btn-filter">Apply Filters</button>
            <a href="safaris" class="btn-filter-clear">Clear All</a>
          </div>
        </form>
      </div>

      <!-- Tour Cards -->
      <div class="col-lg-9 col-md-8" id="tours-container">
        <div id="tours-loading" style="display:none; text-align:center; padding:50px 0;">
          <i class="fa fa-spinner fa-spin fa-3x" style="color:#C49018;"></i>
        </div>
        <div id="tours-content">
        <p class="results-info">Showing <strong><?= count($tours) ?></strong> tours &mdash; all crafted in Kenya by local experts</p>
        <div class="row">
          <?php
          foreach($tours as $idx => $tour):
            $img = !empty($tour['featured_image']) ? 'uploads/'.$tour['featured_image'] : 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg';
            $route = getTourRouteT($pdo, $tour['id']);
            $excerpt = !empty($tour['excerpt']) ? $tour['excerpt'] : (!empty($tour['description']) ? $tour['description'] : '');
            $nights = max(1, (int)($tour['duration_days'] ?? 1)) - 1;
          ?>
          <div class="col-md-6 mb-5 d-flex">
            <div class="fa-tour-card w-100">
              <div class="tc-image-wrap">
                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($tour['title'] ?? '') ?>" class="tc-image" loading="lazy">
                <div class="tc-price-badge <?= !empty($tour['price_from_usd']) ? '' : 'contact' ?>">
                  <?= !empty($tour['price_from_usd']) ? '$'.number_format($tour['price_from_usd']).'/person' : 'Enquire' ?>
                </div>
                <div class="tc-duration-badge"><?= $nights ?> Nights</div>
              </div>
              <div class="tc-body">
                <div class="tc-title"><a href="tours/<?= $tour['slug'] ?? '' ?>"><?= htmlspecialchars($tour['title'] ?? '') ?></a></div>
                <?php if($route): ?>
                <div class="tc-route" style="margin-bottom:12px;font-size:13px;color:#6B6358;"><i class="fa fa-map-marker mr-1" style="color:#C49018;"></i><?= $route ?></div>
                <?php endif; ?>
                <div class="tc-excerpt" style="font-size:14px;color:#6B6358;"><?= htmlspecialchars(mb_substr(strip_tags($excerpt),0,130)) ?>...</div>
                <div class="tc-footer" style="margin-top:15px; border-top:1px solid #E5DDD0; padding-top:15px; display:flex; align-items:center; justify-content:space-between;">
                  <div class="tc-price" style="font-size:13px; color:#4A4340; font-weight:600;">
                    From <span class="price-val" style="font-size:16px; color:#1C1712;">$<?= number_format(!empty($tour['price_from_usd']) ? $tour['price_from_usd'] : 1200) ?></span> Per person
                  </div>
                  <a href="tours/<?= $tour['slug'] ?? '' ?>" class="tc-cta" style="padding:10px 20px; font-size:11px; white-space:nowrap; visibility:visible; opacity:1;">View Itinerary</a>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="fa-cta-banner" style="background-image:url('images/Filao/East Africa/pexels-zacchaeus-rains-262050732-20523197.jpg');">
  <div class="overlay"></div>
  <div class="container fa-cta-content" style="max-width:1280px;">
    <h2>Can't Find Your Perfect Safari?</h2>
    <p>Let our specialists craft a custom tour built entirely around your travel dates, budget, and dreams.</p>
    <a href="contact" class="btn-filao-cta" style="padding:14px 32px;">Get a Custom Quote</a>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
<script src="js/jquery.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="assets/js/filao-nav.js"></script>
<script src="js/start-planning.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tours-filter-form');
    const container = document.getElementById('tours-container');
    const content = document.getElementById('tours-content');
    const loading = document.getElementById('tours-loading');

    if(!form || !container) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchTours();
    });

    // Auto-submit on checkbox change
    const inputs = form.querySelectorAll('input[type="checkbox"]');
    inputs.forEach(input => {
        input.addEventListener('change', fetchTours);
    });

    function fetchTours() {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        const url = form.action + '?' + params.toString();

        content.style.display = 'none';
        loading.style.display = 'block';

        window.history.pushState({path: url}, '', url);

        fetch(url)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('tours-content');
                if(newContent) {
                    content.innerHTML = newContent.innerHTML;
                }
                loading.style.display = 'none';
                content.style.display = 'block';
            })
            .catch(() => {
                window.location.href = url;
            });
    }

    window.addEventListener('popstate', function() {
        window.location.reload();
    });
});
</script>
</body>
</html>
