<?php
require_once 'includes/db.php';
$pdo = getPDO();
$activities = $pdo->query("SELECT id, name, slug, 'other' as category, image as featured_image FROM taxonomies WHERE type='activity' ORDER BY name ASC")->fetchAll();

$acts = [];
foreach($activities as $act) {
  $tab = 'other';
  if (str_contains(strtolower($act['category']), 'wildlife')) {
      $tab = 'wildlife';
  } elseif (str_contains(strtolower($act['category']), 'adventure')) {
      $tab = 'adventure';
  } elseif (str_contains(strtolower($act['category']), 'cultural')) {
      $tab = 'cultural';
  } elseif (str_contains(strtolower($act['category']), 'water')) {
      $tab = 'water';
  }

  $img = $act['featured_image'];
  if (!empty($img) && !str_starts_with($img, 'http') && !str_starts_with($img, 'images/')) {
      $img = 'uploads/' . $img;
  }
  if (empty($img)) {
      $img = 'images/Filao/East Africa/pexels-droneafrica-15373902.jpg'; // fallback
  }

  $acts[] = [
    'id' => $act['id'],
    'name' => $act['name'],
    'category' => $act['category'],
    'img' => $img,
    'link' => 'activities/'.$act['slug'],
    'tab' => $tab
  ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $base_href = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') ? '/filao/' : '/'; ?>
  <base href="<?= $base_href ?>">
  <title>Safari Activities &mdash; Filao Adventures</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="assets/favicon_io/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
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
<section class="fa-page-hero" style="background-image:url('images/Filao/East Africa/pexels-balazsimon-15993990.jpg');">
  <div class="overlay"></div>
  <div class="container fa-page-hero-content" style="max-width:1280px;">
    <h1>Explore Activities</h1>
    <div class="breadcrumb-fa">
      <a href="index">Home</a>
      <span class="bc-sep">&#8250;</span>
      <span class="bc-current">Activities</span>
    </div>
  </div>
</section>

<!-- Content -->
<section class="section-pad bg-cream">
  <div class="container" style="max-width:1280px;">
    
    <ul class="nav dest-tabs" id="destTabs" role="tablist">
      <li class="nav-item">
        <button class="nav-link active" id="all-tab" data-toggle="tab" data-target="#tab-all" type="button" role="tab">All Activities</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" id="wildlife-tab" data-toggle="tab" data-target="#tab-wildlife" type="button" role="tab">Wildlife</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" id="adventure-tab" data-toggle="tab" data-target="#tab-adventure" type="button" role="tab">Adventure</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" id="cultural-tab" data-toggle="tab" data-target="#tab-cultural" type="button" role="tab">Cultural</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" id="water-tab" data-toggle="tab" data-target="#tab-water" type="button" role="tab">Water & Beach</button>
      </li>
    </ul>

    <div class="tab-content">
      <!-- ALL -->
      <div class="tab-pane fade show active" id="tab-all" role="tabpanel">
        <div class="dest-grid">
          <?php foreach($acts as $a): ?>
          <a href="<?= $a['link'] ?>" class="fa-dest-card">
            <img src="<?= htmlspecialchars($a['img']) ?>" alt="<?= htmlspecialchars($a['name']) ?>" loading="lazy">
            <div class="dc-overlay"></div>
            <span class="dc-country-badge"><?= htmlspecialchars($a['category']) ?></span>
            <div class="dc-text">
              <div class="dc-name"><?= htmlspecialchars($a['name']) ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      
      <!-- WILDLIFE -->
      <div class="tab-pane fade" id="tab-wildlife" role="tabpanel">
        <div class="dest-grid">
          <?php foreach($acts as $a): if($a['tab'] !== 'wildlife') continue; ?>
          <a href="<?= $a['link'] ?>" class="fa-dest-card">
            <img src="<?= htmlspecialchars($a['img']) ?>" alt="<?= htmlspecialchars($a['name']) ?>" loading="lazy">
            <div class="dc-overlay"></div>
            <span class="dc-country-badge"><?= htmlspecialchars($a['category']) ?></span>
            <div class="dc-text">
              <div class="dc-name"><?= htmlspecialchars($a['name']) ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- ADVENTURE -->
      <div class="tab-pane fade" id="tab-adventure" role="tabpanel">
        <div class="dest-grid">
          <?php foreach($acts as $a): if($a['tab'] !== 'adventure') continue; ?>
          <a href="<?= $a['link'] ?>" class="fa-dest-card">
            <img src="<?= htmlspecialchars($a['img']) ?>" alt="<?= htmlspecialchars($a['name']) ?>" loading="lazy">
            <div class="dc-overlay"></div>
            <span class="dc-country-badge"><?= htmlspecialchars($a['category']) ?></span>
            <div class="dc-text">
              <div class="dc-name"><?= htmlspecialchars($a['name']) ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- CULTURAL -->
      <div class="tab-pane fade" id="tab-cultural" role="tabpanel">
        <div class="dest-grid">
          <?php foreach($acts as $a): if($a['tab'] !== 'cultural') continue; ?>
          <a href="<?= $a['link'] ?>" class="fa-dest-card">
            <img src="<?= htmlspecialchars($a['img']) ?>" alt="<?= htmlspecialchars($a['name']) ?>" loading="lazy">
            <div class="dc-overlay"></div>
            <span class="dc-country-badge"><?= htmlspecialchars($a['category']) ?></span>
            <div class="dc-text">
              <div class="dc-name"><?= htmlspecialchars($a['name']) ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- WATER -->
      <div class="tab-pane fade" id="tab-water" role="tabpanel">
        <div class="dest-grid">
          <?php foreach($acts as $a): if($a['tab'] !== 'water') continue; ?>
          <a href="<?= $a['link'] ?>" class="fa-dest-card">
            <img src="<?= htmlspecialchars($a['img']) ?>" alt="<?= htmlspecialchars($a['name']) ?>" loading="lazy">
            <div class="dc-overlay"></div>
            <span class="dc-country-badge"><?= htmlspecialchars($a['category']) ?></span>
            <div class="dc-text">
              <div class="dc-name"><?= htmlspecialchars($a['name']) ?></div>
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
</body>
</html>
