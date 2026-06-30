<?php
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Safari Experiences &mdash; Filao Adventures</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="assets/favicon_io/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css">
<?php @include_once __DIR__.'/includes/head_tags.php'; ?>
</head>
<body>
<?php require_once 'includes/nav.php'; ?>

<section class="fa-page-hero" style="background-image:url('images/Filao/East Africa/pexels-balazsimon-15993990.jpg');">
  <div class="overlay"></div>
  <div class="container fa-page-hero-content" style="max-width:1280px;text-align:center;">
    <h1>Safari Experiences</h1>
    <p style="color:rgba(255,255,255,0.85);font-size:18px;max-width:600px;margin:0 auto 24px;">From sunrise game drives to Maasai village visits &mdash; discover Africa on your own terms.</p>
    <div class="breadcrumb-fa justify-content-center">
      <a href="index">Home</a>
      <span class="bc-sep">&#8250;</span>
      <span class="bc-current">Experiences</span>
    </div>
  </div>
</section>

<section class="section-pad bg-cream">
  <div class="container" style="max-width:1280px;">
    <div class="row">
      
      <div class="col-lg-4 col-md-6">
        <a href="experience-detail?slug=game-drives" class="fa-exp-tile">
          <img src="images/Filao/East Africa/Maasai Mara/pexels-photo-8150758 (7).jpg" alt="Game Drives" loading="lazy">
          <div class="exp-overlay"></div>
          <div class="exp-text">
            <h3 class="exp-title">Game Drives</h3>
            <div class="exp-desc">Encounter Africa's most iconic wildlife from open-roof 4&times;4 Land Cruisers, guided by expert naturalists.</div>
            <div class="exp-cta">Explore Tours <i class="fa fa-arrow-right ml-1"></i></div>
          </div>
        </a>
      </div>

      <div class="col-lg-4 col-md-6">
        <a href="experience-detail?slug=great-migration" class="fa-exp-tile">
          <img src="images/Filao/East Africa/Maasai Mara/free-photo-of-wildebeest-grazing-in-the-kenyan-savannah (6).jpeg" alt="Great Migration" loading="lazy">
          <div class="exp-overlay"></div>
          <div class="exp-text">
            <h3 class="exp-title">The Great Migration</h3>
            <div class="exp-desc">Witness one of nature's greatest spectacles &mdash; over 1.5 million wildebeest crossing the Mara River.</div>
            <div class="exp-cta">Explore Tours <i class="fa fa-arrow-right ml-1"></i></div>
          </div>
        </a>
      </div>

      <div class="col-lg-4 col-md-6">
        <a href="experience-detail?slug=hot-air-balloon" class="fa-exp-tile">
          <img src="images/Filao/East Africa/pexels-droneafrica-15373902.jpg" alt="Hot Air Balloon Safari" loading="lazy">
          <div class="exp-overlay"></div>
          <div class="exp-text">
            <h3 class="exp-title">Hot Air Balloon Safari</h3>
            <div class="exp-desc">Float silently above the Maasai Mara at sunrise for a breathtaking aerial perspective of the savannah.</div>
            <div class="exp-cta">Explore Tours <i class="fa fa-arrow-right ml-1"></i></div>
          </div>
        </a>
      </div>

      <div class="col-lg-4 col-md-6">
        <a href="experience-detail?slug=cultural-visits" class="fa-exp-tile">
          <img src="images/Filao/East Africa/Maasai Mara/free-photo-of-portrait-of-a-maasai-man-in-traditional-attire (6).jpeg" alt="Maasai Cultural Visits" loading="lazy">
          <div class="exp-overlay"></div>
          <div class="exp-text">
            <h3 class="exp-title">Maasai Cultural Visits</h3>
            <div class="exp-desc">Spend time with authentic Maasai communities, learn their traditions, and support their livelihoods.</div>
            <div class="exp-cta">Explore Tours <i class="fa fa-arrow-right ml-1"></i></div>
          </div>
        </a>
      </div>

      <div class="col-lg-4 col-md-6">
        <a href="experience-detail?slug=photography" class="fa-exp-tile">
          <img src="images/Filao/East Africa/Maasai Mara/free-photo-of-leopard-resting-in-tree-masai-mara-kenya (4).jpeg" alt="Big Five Photography" loading="lazy">
          <div class="exp-overlay"></div>
          <div class="exp-text">
            <h3 class="exp-title">Photographic Safari</h3>
            <div class="exp-desc">Chase the perfect frame &mdash; lions, leopards, elephants, buffalos, and rhinos across Kenya's finest parks.</div>
            <div class="exp-cta">Explore Tours <i class="fa fa-arrow-right ml-1"></i></div>
          </div>
        </a>
      </div>

      <div class="col-lg-4 col-md-6">
        <a href="tours?category=hybrid-tours" class="fa-exp-tile">
          <img src="images/Filao/Indian Ocean/pexels-asadphoto-9394269.jpg" alt="Beach & Bush" loading="lazy">
          <div class="exp-overlay"></div>
          <div class="exp-text">
            <h3 class="exp-title">Beach &amp; Bush Combos</h3>
            <div class="exp-desc">Combine a thrilling safari with pure beach relaxation on the Indian Ocean coast or Zanzibar.</div>
            <div class="exp-cta">Explore Tours <i class="fa fa-arrow-right ml-1"></i></div>
          </div>
        </a>
      </div>

    </div>
  </div>
</section>

<section class="fa-cta-banner" style="background-image:url('images/Filao/East Africa/pexels-zacchaeus-rains-262050732-20523197.jpg');">
  <div class="overlay"></div>
  <div class="container fa-cta-content" style="max-width:1280px;">
    <h2>Craft Your Own Experience</h2>
    <p>Tell us what you want to see and do, and we'll weave it into a seamless, unforgettable itinerary.</p>
    <a href="contact" class="btn-filao-cta" style="padding:14px 32px;">Contact a Specialist</a>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="assets/js/filao-nav.js"></script>
</body>
</html>
