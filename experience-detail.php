<?php
require_once 'includes/db.php';
$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: experiences.php'); exit; }
$pdo = getPDO();

$exp = $pdo->prepare("SELECT * FROM taxonomies WHERE slug=? AND type='activity'");
$exp->execute([$slug]);
$exp = $exp->fetch();
if (!$exp) { header('Location: experiences.php'); exit; }
$id = $exp['id'];

// Related tours
$tours = $pdo->prepare('SELECT DISTINCT t.id, t.title, t.slug, t.duration_days, t.price_from_usd, t.excerpt, t.featured_image FROM tours t JOIN tour_taxonomy_pivot ttp ON ttp.tour_id=t.id WHERE ttp.taxonomy_id=? AND t.status="published" LIMIT 6');
$tours->execute([$id]);
$tours = $tours->fetchAll();

$img = $exp['featured_image'];
if (!empty($img) && !str_starts_with($img, 'http') && !str_starts_with($img, 'images/')) {
    $img = 'uploads/' . $img;
}
$heroImg = $img ?: 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $base_href = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') ? '/filao/' : '/'; ?>
  <base href="<?= $base_href ?>">
  <title><?= htmlspecialchars($exp['name']) ?> &mdash; Filao Adventures</title>
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
    <span style="font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:#C49018;margin-bottom:12px;display:block;font-family:'Inter',sans-serif;">Safari Experience</span>
    <h1><?= htmlspecialchars($exp['name']) ?></h1>
    <div class="breadcrumb-fa">
      <a href="index">Home</a>
      <span class="bc-sep">&#8250;</span>
      <a href="experiences">Experiences</a>
      <span class="bc-sep">&#8250;</span>
      <span class="bc-current"><?= htmlspecialchars($exp['name']) ?></span>
    </div>
  </div>
</section>

<section class="section-pad bg-cream">
  <div class="container" style="max-width:1280px;">
    <div class="row">
      <div class="col-lg-8 pr-lg-5">
        <div style="background:#fff;padding:40px;border-radius:4px;box-shadow:0 2px 16px rgba(0,0,0,0.03);margin-bottom:48px;">
          <h2 style="font-family:'Cormorant Garant',serif;font-size:32px;color:#1C1712;margin-bottom:24px;">About <?= htmlspecialchars($exp['name']) ?></h2>
          <div style="font-size:15.5px;color:#4A4340;line-height:1.8;">
            <?php if(!empty($exp['description'])): ?>
              <?= nl2br(htmlspecialchars($exp['description'])) ?>
            <?php else: ?>
              <p>Experience the thrill of <?= htmlspecialchars($exp['name']) ?> with Filao Adventures. We carefully design our itineraries to ensure you get the most out of this incredible activity. From expert local guides to premium equipment, every detail is handled so you can focus on the adventure.</p>
              <p>Contact our safari specialists today to find out how you can include <?= htmlspecialchars($exp['name']) ?> in your bespoke African journey.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="fa-section-heading">
          <h2>Tours Featuring <?= htmlspecialchars($exp['name']) ?></h2>
        </div>
        
        <?php if(count($tours) > 0): ?>
          <div class="row">
            <?php foreach($tours as $tour): 
              $tourImg = $tour['featured_image'] ? 'uploads/'.$tour['featured_image'] : 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg';
              $nights = $tour['duration_days'] - 1;
            ?>
            <div class="col-md-6 mb-4 d-flex">
              <div class="fa-tour-card w-100" style="background:#fff;border:1px solid #E5DDD0;padding:0 0 24px 0;margin:0;">
                <div class="tc-image-wrap" style="margin-bottom:16px;">
                  <img src="<?= htmlspecialchars($tourImg) ?>" alt="<?= htmlspecialchars($tour['title']) ?>" class="tc-image" style="height:200px;">
                </div>
                <div style="padding:0 24px;">
                  <div class="tc-duration"><?= $nights ?> Nights</div>
                  <div class="tc-title" style="font-size:20px;"><a href="tour-detail?id=<?= $tour['id'] ?>"><?= htmlspecialchars($tour['title']) ?></a></div>
                  <div class="tc-price">From <span class="price-val">$<?= number_format($tour['price_from_usd'] ?: 1200) ?></span></div>
                  <a href="tour-detail?id=<?= $tour['id'] ?>" class="tc-cta">See The Trip &rarr;</a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div style="background:#fff;padding:32px;border-left:3px solid #C49018;">
            <p style="font-size:15px;color:#4A4340;margin-bottom:16px;">We currently do not have any published set-departure tours featuring this experience. However, as tailor-made specialists, we can easily incorporate <?= htmlspecialchars($exp['name']) ?> into a custom itinerary for you.</p>
            <a href="contact" class="btn-filao-cta">Plan a Custom Safari</a>
          </div>
        <?php endif; ?>

      </div>
      
      <div class="col-lg-4">
        <div style="border:1.5px solid #C49018;padding:32px;background:#fff;position:sticky;top:150px;">
          <h3 style="font-family:'Cormorant Garant',serif;font-size:24px;color:#1C1712;margin-bottom:24px;border-bottom:1px solid #E5DDD0;padding-bottom:12px;">Experience Highlights</h3>
          
          <div class="mb-4">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;display:block;margin-bottom:4px;">Activity Type</span>
            <span style="font-size:15px;color:#1C1712;font-weight:500;"><i class="fa fa-compass" style="color:#C49018;width:16px;"></i> Safari Experience</span>
          </div>
          
          <div class="mb-5">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;display:block;margin-bottom:4px;">Customizability</span>
            <span style="font-size:15px;color:#1C1712;font-weight:500;"><i class="fa fa-sliders" style="color:#C49018;width:16px;"></i> 100% Tailor-Made</span>
          </div>

          <a href="contact" class="btn-filao-cta" style="width:100%;text-align:center;">Add to Itinerary</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="assets/js/filao-nav.js"></script>
</body>
</html>
