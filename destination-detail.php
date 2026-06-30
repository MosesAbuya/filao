<?php
require_once 'includes/db.php';
$slug = $_GET['slug'] ?? '';
$id = intval($_GET['id'] ?? 0);

if (!$slug && !$id) { header('Location: destinations.php'); exit; }
$pdo = getPDO();

if ($slug) {
    $dest = $pdo->prepare('SELECT * FROM destinations WHERE slug=?');
    $dest->execute([$slug]);
} else {
    $dest = $pdo->prepare('SELECT * FROM destinations WHERE id=?');
    $dest->execute([$id]);
}

$dest = $dest->fetch();
if (!$dest) { header('Location: destinations.php'); exit; }
$id = $dest['id']; // normalize

// Related tours
$tours = $pdo->prepare('SELECT DISTINCT t.id, t.title, t.slug, t.duration_days, t.price_from_usd, t.excerpt, t.featured_image FROM tours t JOIN itinerary_steps ist ON ist.tour_id=t.id WHERE ist.destination_id=? AND t.status="published" LIMIT 3');
$tours->execute([$id]);
$tours = $tours->fetchAll();

$img = $dest['featured_image'];
if (!empty($img) && !str_starts_with($img, 'http') && !str_starts_with($img, 'images/')) {
    $img = 'uploads/' . $img;
}
$heroImg = $img ?: 'images/Filao/East Africa/pexels-droneafrica-15373902.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $base_href = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') ? '/filao/' : '/'; ?>
  <base href="<?= $base_href ?>">
  <title><?= htmlspecialchars($dest['name']) ?> &mdash; Filao Adventures</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="assets/favicon_io/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css?v=<?= time() ?>">
<?php @include_once __DIR__.'/includes/head_tags.php'; ?>
</head>
<body>
<?php require_once 'includes/nav.php'; ?>

<section class="td-hero" style="background-image:url('<?= htmlspecialchars($heroImg) ?>');">
  <div class="overlay"></div>
  <div class="td-hero-content">
    <span style="font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:#C49018;margin-bottom:12px;display:block;font-family:'Inter',sans-serif;"><?= htmlspecialchars($dest['country']) ?></span>
    <h1><?= htmlspecialchars($dest['name']) ?></h1>
  </div>
  <div class="hero-breadcrumb">
      <a href="index"><i class="fa fa-home"></i></a>
      <span class="sep">/</span>
      <a href="destinations">Destinations</a>
      <span class="sep">/</span>
      <a href="country?name=<?= urlencode($dest['country']) ?>"><?= htmlspecialchars($dest['country']) ?></a>
      <span class="sep">/</span>
      <span class="current"><?= htmlspecialchars($dest['name']) ?></span>
  </div>
</section>

<section class="section-pad bg-cream">
  <div class="container" style="max-width:1280px;">
    <div class="row">
      <div class="col-lg-8 pr-lg-5">
        <div style="background:#fff;padding:40px;border-radius:4px;box-shadow:0 2px 16px rgba(0,0,0,0.03);margin-bottom:48px;">
          <h2 style="font-family:'Cormorant Garant',serif;font-size:32px;color:#1C1712;margin-bottom:24px;">About <?= htmlspecialchars($dest['name']) ?></h2>
          <div style="font-size:15.5px;color:#4A4340;line-height:1.8;">
            <?php if($dest['description']): ?>
              <?= $dest['description'] ?>
            <?php else: ?>
              <p><?= htmlspecialchars($dest['name']) ?> is one of <?= htmlspecialchars($dest['country']) ?>'s premier travel destinations. Whether you are looking for spectacular wildlife encounters, stunning landscapes, or pure relaxation, this region offers an unforgettable experience. At Filao Adventures, we craft tailor-made journeys that allow you to explore <?= htmlspecialchars($dest['name']) ?> at your own pace, staying at the finest hand-picked lodges and camps.</p>
              <p>Speak to one of our safari experts to start designing your perfect itinerary.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="fa-section-heading">
          <h2>Tours Visiting <?= htmlspecialchars($dest['name']) ?></h2>
        </div>
        
        <?php if(count($tours) > 0): ?>
          <div class="row">
            <?php foreach($tours as $tour): 
              $img = $tour['featured_image'] ? 'uploads/'.$tour['featured_image'] : 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg';
              $nights = $tour['duration_days'] - 1;
            ?>
            <div class="col-md-6 mb-4 d-flex">
              <div class="fa-tour-card w-100" style="background:#fff;border:1px solid #E5DDD0;padding:0 0 24px 0;margin:0;">
                <div class="tc-image-wrap" style="margin-bottom:16px;">
                  <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($tour['title']) ?>" class="tc-image" style="height:200px;">
                </div>
                <div style="padding:0 24px;">
                  <div class="tc-duration"><?= $nights ?> Nights</div>
                  <div class="tc-title" style="font-size:20px;"><a href="tours/<?= $tour['slug'] ?>"><?= htmlspecialchars($tour['title']) ?></a></div>
                  <div class="tc-price">From <span class="price-val">$<?= number_format($tour['price_from_usd'] ?: 1200) ?></span></div>
                  <a href="tours/<?= $tour['slug'] ?>" class="tc-cta" style="font-size:11px;">See The Trip &rarr;</a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div style="background:#fff;padding:32px;border-left:3px solid #C49018;">
            <p style="font-size:15px;color:#4A4340;margin-bottom:16px;">We currently do not have any published set-departure tours that visit this destination. However, as tailor-made specialists, we can easily incorporate <?= htmlspecialchars($dest['name']) ?> into a custom itinerary for you.</p>
            <a href="#" class="tc-cta" data-open-planner="true" data-dest-name="<?= htmlspecialchars($dest['name']) ?>">Plan a Custom Safari</a>
          </div>
        <?php endif; ?>

      </div>
      
      <div class="col-lg-4">
        <div style="border:1.5px solid #C49018;padding:32px;background:#fff;position:sticky;top:150px;">
          <h3 style="font-family:'Cormorant Garant',serif;font-size:24px;color:#1C1712;margin-bottom:24px;border-bottom:1px solid #E5DDD0;padding-bottom:12px;">Destination Quick Facts</h3>
          
          <div class="mb-4">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;display:block;margin-bottom:4px;">Country</span>
            <span style="font-size:15px;color:#1C1712;font-weight:500;"><i class="fa fa-map-marker" style="color:#C49018;width:16px;"></i> <?= htmlspecialchars($dest['country']) ?></span>
          </div>
          
          <div class="mb-4">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;display:block;margin-bottom:4px;">Region Type</span>
            <span style="font-size:15px;color:#1C1712;font-weight:500;"><i class="fa fa-tag" style="color:#C49018;width:16px;"></i> <?= htmlspecialchars($dest['region_type'] ?: 'National Park') ?></span>
          </div>
          
          <div class="mb-5">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;display:block;margin-bottom:4px;">Best Time To Visit</span>
            <span style="font-size:15px;color:#1C1712;font-weight:500;"><i class="fa fa-calendar" style="color:#C49018;width:16px;"></i> June to October</span>
          </div>

          <button data-open-planner="true" data-dest-name="<?= htmlspecialchars($dest['name']) ?>" class="tc-cta" style="width:100%;text-align:center;border:none;cursor:pointer;">Plan a Trip Here</button>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="assets/js/filao-nav.js"></script>`n<script src="js/start-planning.js?v=1781967414"></script>
</body>
</html>
