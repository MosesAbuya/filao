<?php
require_once 'includes/db.php';
$pdo = getPDO();
$where = ["t.status='published'", "t.is_hot_offer=1"];
$params = [];

if (isset($_GET['joining']) && $_GET['joining'] == '1') {
    $where[] = "t.is_joining_tour=1";
}

if (!empty($_GET['q'])) {
    $q = "%" . $_GET['q'] . "%";
    $where[] = "(t.title LIKE ? OR t.excerpt LIKE ?)";
    $params[] = $q;
    $params[] = $q;
}

if (!empty($_GET['dest'])) {
    $d = "%" . $_GET['dest'] . "%";
    $where[] = "(t.title LIKE ? OR EXISTS (SELECT 1 FROM itinerary_steps ist JOIN destinations d ON d.id=ist.destination_id WHERE ist.tour_id=t.id AND d.name LIKE ?))";
    $params[] = $d;
    $params[] = $d;
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

$join = "";
if (!empty($_GET['cat']) && is_array($_GET['cat'])) {
    $join = "JOIN activity_tour at ON t.id = at.tour_id JOIN activities a ON at.activity_id = a.id";
    $catConditions = [];
    foreach ($_GET['cat'] as $c) {
        $catConditions[] = "a.slug = ?";
        $params[] = $c;
    }
    if (!empty($catConditions)) $where[] = "(" . implode(" OR ", $catConditions) . ")";
}

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
$stmt = $pdo->prepare("SELECT DISTINCT t.id, t.title, t.slug, t.duration_days, t.price_from_usd, t.excerpt, t.description, t.featured_image, t.status FROM tours t $join WHERE $whereSql ORDER BY t.duration_days ASC");
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
  <title>Hot Deals &amp; Special Offers &mdash; Filao Adventures</title>
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
    body { background-color: #1C1712; color: #fff; }
    .filter-sidebar { background:#1C1712; padding:28px; border:1px solid rgba(255,255,255,0.1); border-radius:4px; position:sticky;top:160px; }
    .filter-sidebar h5 { font-family:'Inter',sans-serif;font-size:10px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:#C49018;margin-bottom:16px; }
    .filter-sidebar .filter-group { margin-bottom:20px; padding-bottom:18px; border-bottom:1px solid rgba(255,255,255,0.1); }
    .filter-sidebar label { display:flex;align-items:center;gap:8px;font-size:13.5px;color:rgba(255,255,255,0.85);cursor:pointer;padding:3px 0; }
    .filter-sidebar input[type=checkbox] { accent-color:#C49018; }
    .filter-count { font-size:11px;color:rgba(255,255,255,0.5);margin-left:auto; }
    .btn-filter { width:100%;background:#C49018;color:#fff;border:none;padding:11px;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;cursor:pointer;border-radius:3px;font-family:'Inter',sans-serif;margin-bottom:8px; }
    .btn-filter-clear { display:block;text-align:center;font-size:11px;color:rgba(255,255,255,0.6);text-decoration:none; }
    .results-info { font-size:13px;color:rgba(255,255,255,0.7);margin-bottom:24px;font-family:'Inter',sans-serif; }
    .fa-tour-card { background:#251e18; border:1px solid rgba(255,255,255,0.05); }
    .fa-tour-card .tc-title a { color:#fff; }
    .fa-tour-card .tc-excerpt { color:rgba(255,255,255,0.7); }
    .fa-tour-card .tc-footer { border-top:1px solid rgba(255,255,255,0.1) !important; }
    .fa-tour-card .tc-price { color:rgba(255,255,255,0.8) !important; }
    .fa-tour-card .tc-price .price-val { color:#fff !important; }
    .fa-tour-card .tc-route { color:rgba(255,255,255,0.6) !important; }
  </style>
</head>
<body>
<?php require_once 'includes/nav.php'; ?>

<!-- ====== HOT OFFERS CAROUSEL HERO ====== -->
<?php if (!empty($tours)): ?>
<section class="hot-sale-section" id="hotSaleSection" style="position: relative; overflow: hidden; background-color: #1C1712; color: #fff; padding: 100px 0; margin-bottom: 0;">
  <!-- Background Images Container -->
  <div id="hsBackgrounds" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0;">
    <?php foreach ($tours as $index => $offer): 
      if ($index >= 6) break; // Limit carousel to top 6 offers
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
          <?php foreach ($tours as $index => $offer): 
            if ($index >= 6) break;
          ?>
            <div class="hs-text hs-text-<?= $index ?>" style="position: absolute; top:0; left:0; width: 100%; opacity: <?= $index === 0 ? 1 : 0 ?>; visibility: <?= $index === 0 ? 'visible' : 'hidden' ?>; transition: all 0.5s ease; transform: translateY(<?= $index === 0 ? '0' : '20px' ?>);">
              <h1 style="font-family:'Cormorant Garant',serif; font-size:48px; font-weight:700; color:#fff; line-height:1.1; margin-bottom: 20px; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?= htmlspecialchars($offer['title']) ?></h1>
              <p style="font-size:16px; color:rgba(255,255,255,0.85); line-height:1.7; margin-bottom: 30px;">
                <?= htmlspecialchars(substr(strip_tags($offer['excerpt'] ?: "Experience the magic of this destination. Book now to enjoy exclusive discounts on this unforgettable journey and create memories that will last a lifetime."), 0, 150)) ?>...
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
          <?php foreach ($tours as $index => $offer): 
            if ($index >= 6) break;
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
    const totalItems = <?= min(6, count($tours)) ?>;
    if (totalItems === 0) return;
    
    let currentIndex = 0;
    let interval;

    function updateSlider(index) {
      document.querySelectorAll('.hs-bg').forEach((bg, i) => {
        bg.style.opacity = (i === index) ? '1' : '0';
      });
      
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

      document.querySelectorAll('.hs-card').forEach((card, i) => {
        if (i === index) {
          card.style.opacity = '1';
          card.style.transform = 'translateY(-50%) translateX(20px) scale(1)';
          card.style.zIndex = '3';
          card.style.pointerEvents = 'auto';
        } else if (i === (index + 1) % totalItems) {
          card.style.opacity = '0.5';
          card.style.transform = 'translateY(-50%) translateX(380px) scale(0.85)';
          card.style.zIndex = '2';
          card.style.pointerEvents = 'none';
        } else {
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

    updateSlider(0);
    resetInterval();
  });
</script>
<style>
  #hsPrev:hover { background: rgba(255,255,255,0.2) !important; }
  #hsNext:hover { background: #d31a1a !important; }
</style>
<?php endif; ?>

<!-- Content -->
<section class="section-pad" style="background-color: #1C1712;">
  <div class="container" style="max-width:1280px;">
    <div class="row">
      <!-- Filter Sidebar -->
      <div class="col-lg-3 col-md-4 mb-5 d-none d-md-block">
        <form method="GET" action="hot-deals.php" id="tours-filter-form">
          <div class="filter-sidebar">
            <h5>Filter Deals</h5>
            <div class="filter-group">
              <div class="mb-2" style="font-size:10.5px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;">Category</div>
              <label><input type="checkbox" name="cat[]" value="safari"> Safari Tours <span class="filter-count">(5)</span></label>
              <label><input type="checkbox" name="cat[]" value="beach"> Beach Holidays <span class="filter-count">(2)</span></label>
              <label><input type="checkbox" name="cat[]" value="city"> City Tours <span class="filter-count">(1)</span></label>
              <label><input type="checkbox" name="cat[]" value="international"> International <span class="filter-count">(3)</span></label>
            </div>
            <div class="filter-group">
              <div class="mb-2" style="font-size:10.5px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;">Joining Tours</div>
              <label><input type="checkbox" name="joining" value="1" <?= isset($_GET['joining']) && $_GET['joining'] == '1' ? 'checked' : '' ?>> Joining Tours Only</label>
            </div>
            <div class="filter-group">
              <div class="mb-2" style="font-size:10.5px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;">Duration</div>
              <label><input type="checkbox" name="dur[]" value="1-3"> 1&ndash;3 Days <span class="filter-count">(2)</span></label>
              <label><input type="checkbox" name="dur[]" value="4-5"> 4&ndash;5 Days <span class="filter-count">(3)</span></label>
              <label><input type="checkbox" name="dur[]" value="6-7"> 6&ndash;7 Days <span class="filter-count">(1)</span></label>
              <label><input type="checkbox" name="dur[]" value="8+"> 8+ Days <span class="filter-count">(1)</span></label>
            </div>
            <div class="filter-group" style="border-bottom:none;">
              <div class="mb-2" style="font-size:10.5px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;">Budget</div>
              <label><input type="checkbox" name="price[]" value="500-1500"> $500 &ndash; $1,500</label>
              <label><input type="checkbox" name="price[]" value="1500-3000"> $1,500 &ndash; $3,000</label>
              <label><input type="checkbox" name="price[]" value="3000+"> $3,000+</label>
            </div>
            <button type="submit" class="btn-filter">Apply Filters</button>
            <a href="hot-deals" class="btn-filter-clear">Clear All</a>
          </div>
        </form>
      </div>

      <!-- Tour Cards -->
      <div class="col-lg-9 col-md-8" id="tours-container">
        <div id="tours-loading" style="display:none; text-align:center; padding:50px 0;">
          <i class="fa fa-spinner fa-spin fa-3x" style="color:#C49018;"></i>
        </div>
        <div id="tours-content">
        <p class="results-info">Showing <strong><?= count($tours) ?></strong> hot deals &mdash; all crafted in Kenya by local experts</p>
        <div class="row">
          <?php
          foreach($tours as $idx => $tour):
            $img = $tour['featured_image'] ? 'uploads/'.$tour['featured_image'] : 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg';
            $route = getTourRouteT($pdo, $tour['id']);
            $excerpt = !empty($tour['excerpt']) ? $tour['excerpt'] : (!empty($tour['description']) ? $tour['description'] : '');
            $nights = $tour['duration_days'] - 1;
          ?>
          <div class="col-md-6 mb-5 d-flex">
            <div class="fa-tour-card w-100">
              <div class="tc-image-wrap">
                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($tour['title']) ?>" class="tc-image" loading="lazy">
                <div class="tc-price-badge <?= $tour['price_from_usd'] ? '' : 'contact' ?>">
                  <?= $tour['price_from_usd'] ? '$'.number_format($tour['price_from_usd']).'/person' : 'Enquire' ?>
                </div>
                <div class="tc-duration-badge"><?= $nights ?> Nights</div>
              </div>
              <div class="tc-body">
                <div class="tc-country"><?= getTourCountries($pdo, $tour['id']) ?></div>
                <div class="tc-title"><a href="tours/<?= $tour['slug'] ?>"><?= htmlspecialchars($tour['title']) ?></a></div>
                <?php if($route): ?>
                <div class="tc-route" style="margin-bottom:12px;font-size:13px;color:#6B6358;"><i class="fa fa-map-marker mr-1" style="color:#C49018;"></i><?= $route ?></div>
                <?php endif; ?>
                <div class="tc-excerpt" style="font-size:14px;color:#6B6358;"><?= htmlspecialchars(mb_substr(strip_tags($excerpt),0,130)) ?>...</div>
                <div class="tc-footer" style="margin-top:15px; border-top:1px solid #E5DDD0; padding-top:15px; display:flex; align-items:center; justify-content:space-between;">
                  <div class="tc-price" style="font-size:13px; color:#4A4340; font-weight:600;">
                    From <span class="price-val" style="font-size:16px; color:#1C1712;">$<?= number_format($tour['price_from_usd'] ?: 1200) ?></span> Per person
                  </div>
                  <a href="tours/<?= $tour['slug'] ?>" class="tc-cta" style="padding:10px 20px; font-size:11px; white-space:nowrap; visibility:visible; opacity:1;">View Itinerary</a>
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
