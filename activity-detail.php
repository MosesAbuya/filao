<?php
require_once 'includes/db.php';
$slug = $_GET['slug'] ?? '';
$id = intval($_GET['id'] ?? 0);

if (!$slug && !$id) { header('Location: /filao/activities'); exit; }
$pdo = getPDO();

if ($slug) {
    $act = $pdo->prepare('SELECT * FROM activities WHERE slug=?');
    $act->execute([$slug]);
} else {
    $act = $pdo->prepare('SELECT * FROM activities WHERE id=?');
    $act->execute([$id]);
}

$act = $act->fetch();
if (!$act) { header('Location: /filao/activities'); exit; }
$id = $act['id'];

// Related tours using pivot table activity_tour
$tours = $pdo->prepare('
    SELECT t.id, t.title, t.slug, t.duration_days, t.price_from_usd, t.excerpt, t.featured_image 
    FROM tours t 
    JOIN activity_tour at ON at.tour_id=t.id 
    WHERE at.activity_id=? AND t.status="published" 
    LIMIT 3
');
$tours->execute([$id]);
$tours = $tours->fetchAll();

$img = $act['featured_image'];
if (!empty($img) && !str_starts_with($img, 'http') && !str_starts_with($img, 'images/')) {
    $img = 'uploads/' . $img;
}
$heroImg = $img ?: 'images/Filao/East Africa/pexels-droneafrica-15373902.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <base href="/filao/">
  <title><?= htmlspecialchars($act['name']) ?> &mdash; Filao Adventures</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="assets/favicon_io/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css">
</head>
<body>
<?php require_once 'includes/nav.php'; ?>

<section class="fa-page-hero" style="background-image:url('<?= htmlspecialchars($heroImg) ?>');padding:140px 0 80px;">
  <div class="overlay"></div>
  <div class="container fa-page-hero-content" style="max-width:1280px;">
    <span style="font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:#C49018;margin-bottom:12px;display:block;font-family:'Inter',sans-serif;"><?= htmlspecialchars($act['category']) ?></span>
    <h1><?= htmlspecialchars($act['name']) ?></h1>
    <div class="breadcrumb-fa">
      <a href="index">Home</a>
      <span class="bc-sep">&#8250;</span>
      <a href="activities">Activities</a>
      <span class="bc-sep">&#8250;</span>
      <span class="bc-current"><?= htmlspecialchars($act['name']) ?></span>
    </div>
  </div>
</section>

<!-- Content -->
<section style="padding:80px 0; background:#F7F5F0;">
  <div class="container" style="max-width:1280px;">
    <div class="row g-5">
      <!-- Main Activity Info -->
      <div class="col-lg-8">
        <div style="background:#fff;border:1px solid #E5DDD0;border-radius:6px;padding:48px;margin-bottom:40px;">
          <h2 style="font-family:'Cormorant Garant',serif;font-size:36px;color:#1C1712;margin-bottom:24px;font-weight:400;">Experience <?= htmlspecialchars($act['name']) ?></h2>
          
          <div style="font-family:'Inter',sans-serif;font-size:17px;line-height:1.8;color:#4A4340;">
            <p><?= nl2br(htmlspecialchars($act['description'] ?: 'Prepare for an unforgettable experience in the heart of nature.')) ?></p>
            <p>At Filao Adventures, we ensure that your activities are carefully tailored to your pace and preferences. Whether you seek thrilling adventures or peaceful relaxation, our expert guides provide unparalleled insight and hospitality.</p>
          </div>
        </div>
      </div>
      
      <!-- Sidebar / CTA -->
      <div class="col-lg-4">
        <div style="background:#1C1712;border-radius:6px;padding:32px;text-align:center;">
          <span style="font-size:11px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#C49018;font-family:'Inter',sans-serif;">Tailor-Made Travel</span>
          <h3 style="font-family:'Cormorant Garant',serif;font-size:26px;color:#fff;margin:12px 0 16px;">Add this to your journey</h3>
          <p style="font-size:14px;color:rgba(255,255,255,0.7);font-family:'Inter',sans-serif;margin-bottom:24px;">Speak to our safari experts to incorporate <?= htmlspecialchars($act['name']) ?> into your bespoke African itinerary.</p>
          <button data-open-planner="true" style="display:block;width:100%;padding:12px;background:#C49018;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;font-size:12px;font-family:'Inter',sans-serif;">Enquire Now</button>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Related Tours (Using Pivot Table) -->
<?php if (count($tours) > 0): ?>
<section style="padding:80px 0; background:#fff; border-top:1px solid #E5DDD0;">
  <div class="container" style="max-width:1280px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:40px;">
      <div>
        <span style="font-size:12px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:#C49018;font-family:'Inter',sans-serif;">Tours &amp; Safaris</span>
        <h2 style="font-family:'Cormorant Garant',serif;font-size:42px;color:#1C1712;margin-top:8px;">Tours Featuring <?= htmlspecialchars($act['name']) ?></h2>
      </div>
      <a href="tours" class="view-all-link">View All Tours &rarr;</a>
    </div>

    <div class="row">
      <?php foreach($tours as $t): 
        $tImg = $t['featured_image'];
        if (!empty($tImg) && !str_starts_with($tImg, 'http') && !str_starts_with($tImg, 'images/')) {
            $tImg = 'uploads/' . $tImg;
        }
        $tImg = $tImg ?: 'images/Filao/East Africa/pexels-droneafrica-13234382.jpg';
        $nights = max(1, $t['duration_days'] - 1);
      ?>
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="tour-card" style="border:1px solid #E5DDD0; background:#FAF8F4; border-radius:6px; overflow:hidden; transition:box-shadow 0.3s; height:100%;">
          <div class="tc-img-wrap" style="position:relative;">
            <a href="tours/<?= $t['slug'] ?>">
              <img src="<?= htmlspecialchars($tImg) ?>" alt="<?= htmlspecialchars($t['title']) ?>" style="width:100%; height:260px; object-fit:cover;">
            </a>
          </div>
          <div class="tc-body" style="padding:24px;">
            <div style="font-size:10px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#9E3A25;margin-bottom:8px;"><?= $nights ?> Nights &bull; Safari</div>
            <div style="font-family:'Cormorant Garant',serif;font-size:24px;font-weight:500;margin-bottom:12px;line-height:1.2;">
              <a href="tours/<?= $t['slug'] ?>" style="color:#1C1712;text-decoration:none;"><?= htmlspecialchars($t['title']) ?></a>
            </div>
            <div style="font-size:14px;color:#6B6358;font-family:'Inter',sans-serif;margin-bottom:20px;line-height:1.6;">
              <?= htmlspecialchars(substr(strip_tags($t['excerpt'] ?? ''),0,120)) ?>...
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #E5DDD0; padding-top:16px;">
              <div style="font-size:14px;color:#1C1712;font-family:'Inter',sans-serif;">From <strong>$<?= number_format($t['price_from_usd'] ?: 1200) ?></strong></div>
              <a href="tours/<?= $t['slug'] ?>" style="font-size:12px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#C49018;text-decoration:none;">View Trip &rarr;</a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="assets/js/filao-nav.js"></script>`n<script src="js/start-planning.js"></script>
</body>
</html>
