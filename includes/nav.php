<?php
// includes/nav.php   Filao Adventures Global Navigation
require_once __DIR__ . '/db.php';
$navPdo = getPDO();

// Tours grouped by destination country from DB
$regionsStmt = $navPdo->query("
    SELECT r.name as region_name, r.slug as region_slug, r.featured_image as region_img,
           c.name as country_name, c.slug as country_slug, c.featured_image as country_img
    FROM regions r
    JOIN countries c ON r.id = c.region_id
    ORDER BY r.name, c.name
")->fetchAll(PDO::FETCH_ASSOC);

$navRegions = [];
$navCountries = []; // Just the names for the tour dropdown

foreach ($regionsStmt as $row) {
    $cName = trim($row['country_name']);
    $navCountries[] = $cName;
    $region = trim($row['region_name']);
    
    if (!isset($navRegions[$region])) {
        $navRegions[$region] = [];
    }
    
    // Maintain the old structure for compatibility
    $navRegions[$region][] = [
        'country' => $cName,
        'featured_image' => $row['country_img'],
        'region_img' => $row['region_img']
    ];
}
$navCountries = array_unique($navCountries);

$navToursByCountry = [];
foreach ($navCountries as $country) {
  $rows = $navPdo->prepare("
        SELECT DISTINCT t.id, t.title, t.slug, t.featured_image
        FROM tours t
        JOIN itinerary_steps ist ON t.id = ist.tour_id
        JOIN destinations d ON d.id = ist.destination_id
        WHERE d.country = ? AND t.status='published'
        ORDER BY t.title ASC LIMIT 6
    ");
  $rows->execute([$country]);
  $toursList = $rows->fetchAll();
  if (count($toursList) > 0) {
      $navToursByCountry[$country] = $toursList;
  }
}

// Fallback: if no country-based grouping, just load all tours
if (empty($navToursByCountry)) {
  $allTours = $navPdo->query("SELECT id, title, slug FROM tours WHERE status='published' ORDER BY id ASC LIMIT 12")->fetchAll();
  $navToursByCountry['All Tours'] = $allTours;
}

// Fetch Destiantions with their associated tours for the mobile menu
$navDestData = $navPdo->query("
    SELECT DISTINCT d.id, d.name, d.slug 
    FROM destinations d
    JOIN itinerary_steps ist ON d.id = ist.destination_id
    JOIN tours t ON t.id = ist.tour_id
    WHERE t.status='published'
    ORDER BY d.name ASC LIMIT 10
")->fetchAll();

$navDestinations = [];
foreach($navDestData as $navDestLoop) {
    $tstmt = $navPdo->prepare("
        SELECT DISTINCT t.id, t.title, t.slug 
        FROM tours t
        JOIN itinerary_steps ist ON t.id = ist.tour_id
        WHERE ist.destination_id = ? AND t.status='published'
        ORDER BY t.title ASC LIMIT 5
    ");
    $tstmt->execute([$navDestLoop['id']]);
    $navDestLoop['tours'] = $tstmt->fetchAll();
    $navDestinations[] = $navDestLoop;
}


// Recommended tours grouped by activity from DB
$navRecommended = $navPdo->query("
    SELECT id, title, slug, featured_image, price_from_usd, duration_days, recommended_activity
    FROM tours
    WHERE is_recommended=1 AND status='published'
    ORDER BY recommended_activity ASC, title ASC
")->fetchAll();

// Group recommended by activity
$navRecByActivity = [];
foreach ($navRecommended as $rt) {
  $recAct = $rt['recommended_activity'] ?: 'Featured';
  $navRecByActivity[$recAct][] = $rt;
}

// Activities dynamically grouped by category
$navActivities = $navPdo->query("SELECT id, name, slug, category, featured_image FROM activities ORDER BY category ASC, name ASC")->fetchAll();
$navActByCategory = [];
foreach ($navActivities as $navActItem) {
  $navActByCategory[$navActItem['category']][] = $navActItem;
}

// Safaris dynamically grouped by Theme
$navSafarisThemes = $navPdo->query("
    SELECT tx.name as theme_name, tx.slug as theme_slug, t.id, t.title, t.slug as tour_slug, t.featured_image
    FROM taxonomies tx
    JOIN tour_taxonomy_pivot ttp ON tx.id = ttp.taxonomy_id
    JOIN tours t ON ttp.tour_id = t.id
    JOIN activity_tour at ON t.id = at.tour_id
    JOIN activities a ON at.activity_id = a.id
    WHERE t.status='published' AND a.slug = 'safari' AND tx.name IN ('Family Friendly', 'Honeymoon', 'Solo Traveler')
    ORDER BY tx.name ASC, t.duration_days ASC
")->fetchAll();

$navSafarisByTheme = [];
foreach ($navSafarisThemes as $st) {
  $navSafarisByTheme[$st['theme_name']][] = $st;
}

// Joining Tours
$navJoiningTours = $navPdo->query("
    SELECT id, title, slug, featured_image
    FROM tours
    WHERE is_joining_tour=1 AND status='published'
    ORDER BY title ASC
")->fetchAll();
?>
<!-- ====== MAIN HEADER ====== -->
<header class="fa-site-header" id="faNavbar">

  <!-- ── ROW 1: Centered Logo + Contacts (hero only, hides on scroll) ── -->
  <div class="fa-logo-row" id="faLogoRow">
    <div class="container d-flex justify-content-between align-items-center w-100" style="max-width: 1280px;">
      <!-- Left: Contact Info -->
      <div class="fa-logo-side fa-logo-left text-white d-none d-lg-flex"
        style="font-size: 11px; gap: 20px; letter-spacing: 0.05em; font-weight: 500;">
        <a href="tel:+254757139239" class="text-white text-decoration-none"
          style="opacity: 0.85; transition: opacity 0.2s;"><i class="fa fa-phone mr-1"></i>+254 757 139239</a>
        <a href="mailto:info@filaoadventures.co.ke" class="text-white text-decoration-none"
          style="opacity: 0.85; transition: opacity 0.2s;"><i
            class="fa fa-envelope mr-1"></i>info@filaoadventures.co.ke</a>
      </div>

      <!-- Center: Logo -->
      <a href="/" class="fa-logo-centered-link mx-auto">
        <img src="assets/logo/filao-logo.png" alt="Filao Adventures" class="fa-logo-img-large">
      </a>

      <!-- Right: Socials -->
      <div class="fa-logo-side fa-logo-right text-white d-none d-lg-flex justify-content-end"
        style="font-size: 14px; gap: 20px;">
        <a href="https://wa.me/254757139239" target="_blank"
          class="text-white text-decoration-none d-flex align-items-center"
          style="font-size: 11px; opacity: 0.85; gap: 6px;"><i class="fa fa-whatsapp" style="font-size: 15px;"></i>
          WhatsApp</a>
        <a href="https://www.instagram.com/filaoadventures/" target="_blank" class="text-white" style="opacity: 0.85;"><i class="fa fa-instagram"></i></a>
        <a href="https://www.tiktok.com/@filaoadventures" target="_blank" class="text-white" style="opacity: 0.85;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-1px;">
              <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z"/>
            </svg>
        </a>
        <a href="https://x.com/FilaoAdventures" target="_blank" class="text-white mr-3" style="opacity: 0.85;"><i class="fa fa-twitter"></i></a>
        <a href="https://www.facebook.com/profile.php?id=100084891550126#" target="_blank" class="text-white" style="opacity: 0.85;"><i class="fa fa-facebook"></i></a>
        <a href="https://ke.linkedin.com/jobs/view/travel-consultant-at-filao-adventures-4398464574" target="_blank" class="text-white" style="opacity: 0.85;"><i class="fa fa-linkedin"></i></a>
      </div>
    </div>
  </div>

  <!-- ── ROW 2: Navigation bar ── -->
  <div class="fa-nav-row" id="faNavRow">
    <div class="fa-nav-row-inner">

      <!-- Sticky Logo (hidden at top, appears when scrolled) -->
      <div class="fa-sticky-logo" id="faStickyLogo">
        <a href="/">
          <img src="assets/logo/filao-logo.png" alt="Filao Adventures">
        </a>
      </div>

      <!-- Nav Links (centered) -->
      <nav class="fa-mainnav">
        <div class="d-lg-none" style="text-align:right; margin-bottom: 10px; padding: 20px 20px 0 0;">
          <button id="fa-mainnav-close" style="background:none;border:none;color:#fff;font-size:32px;line-height:1;cursor:pointer;">&times;</button>
        </div>
        <ul class="fa-subnav-inner">

          <!-- DESTINATIONS -->
          <li>
            <a href="destinations" class="nav-top-link">Destinations</a>
            <div class="fa-megamenu">
              <button class="mm-close-btn">&times; Close</button>
              <div class="fa-megamenu-content">
                <div class="fa-megamenu-inner">
                  <div class="fa-mm-tabs">
                    <span class="mm-heading">Destinations</span>
                    <ul>
                      <?php
                      $firstReg = true;
                      $firstImg = '';
                      $firstCat = '';
                      foreach ($navRegions as $regionName => $countriesList):
                        // Get an image for the region
                        $rawImg = $countriesList[0]['featured_image'];
                        $img = 'images/Filao/East Africa/pexels-kelly-17291020.jpg';
                        if (!empty($rawImg)) {
                            $img = str_starts_with($rawImg, 'destinations/') ? 'uploads/' . $rawImg : 'uploads/destinations/' . $rawImg;
                        }
                        if ($firstReg) {
                          $firstImg = $img;
                          $firstCat = $regionName;
                        }
                      ?>
                      <li class="<?= $firstReg ? 'mm-active' : '' ?>">
                        <a href="#" class="mm-tab-trigger" data-panel="dest-<?= md5($regionName) ?>"
                          data-img="<?= htmlspecialchars($img) ?>"
                          data-caption="Explore <?= htmlspecialchars($regionName) ?>">
                          <?= htmlspecialchars($regionName) ?>
                        </a>
                      </li>
                      <?php 
                        $firstReg = false;
                      endforeach; 
                      ?>
                      <li><a href="destinations" style="margin-top:12px;border-top:1px solid #E5DDD0;padding-top:12px;">View All Regions</a></li>
                    </ul>
                  </div>
                  <div class="fa-mm-links">
                    <?php
                    $firstReg = true;
                    foreach ($navRegions as $regionName => $countriesList):
                    ?>
                    <div class="mm-panel" data-id="dest-<?= md5($regionName) ?>" style="display: <?= $firstReg ? 'block' : 'none' ?>;">
                      <ul>
                        <?php
                        // Filter unique countries for this region
                        $uniqueCountries = [];
                        foreach ($countriesList as $c) {
                          if (!isset($uniqueCountries[$c['country']])) {
                            $uniqueCountries[$c['country']] = $c['featured_image'];
                          }
                        }
                        foreach ($uniqueCountries as $cName => $cImg):
                          $imgUrl = 'images/Filao/East Africa/pexels-kelly-17291020.jpg';
                          if (!empty($cImg)) {
                              $imgUrl = str_starts_with($cImg, 'destinations/') ? 'uploads/' . $cImg : 'uploads/destinations/' . $cImg;
                          }
                        ?>
                        <li><a href="country?name=<?= urlencode($cName) ?>" data-img="<?= htmlspecialchars($imgUrl) ?>" data-caption="<?= htmlspecialchars($cName) ?>"><?= htmlspecialchars($cName) ?></a></li>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                    <?php
                      $firstReg = false;
                    endforeach;
                    ?>
                  </div>
                  <div class="fa-mm-image">
                    <img id="mm-dest-img" src="<?= htmlspecialchars($firstImg) ?>" alt="Destinations">
                    <div class="mm-caption" id="mm-dest-caption">Explore <?= htmlspecialchars($firstCat) ?></div>
                  </div>
                </div>
              </div>
            </div>
          </li>

          <!-- ACTIVITIES -->
          <li>
            <a href="activities" class="nav-top-link">Activities</a>
            <div class="fa-megamenu">
              <button class="mm-close-btn">&times; Close</button>
              <div class="fa-megamenu-content">
                <div class="fa-megamenu-inner">
                  <div class="fa-mm-tabs">
                    <span class="mm-heading">Safari Activities</span>
                    <ul>
                      <?php $firstAct = true;
                      $firstImg = '';
                      $firstCat = '';
                      foreach ($navActByCategory as $category => $cActs):
                        $img = $cActs[0]['featured_image'];
                        if (!empty($img) && !str_starts_with($img, 'http') && !str_starts_with($img, 'images/')) {
                          $img = 'uploads/' . $img;
                        }
                        $img = $img ?: 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg';
                        if ($firstAct) {
                          $firstImg = $img;
                          $firstCat = $category;
                        }
                        $panelId = 'act-' . strtolower(preg_replace('/[^a-z0-9]/i', '-', $category));
                        ?>
                        <li class="<?= $firstAct ? 'mm-active' : '' ?>">
                          <a href="#" class="mm-tab-trigger" data-panel="<?= $panelId ?>"
                            data-img="<?= htmlspecialchars($img) ?>" data-caption="<?= htmlspecialchars($category) ?>">
                            <?= htmlspecialchars($category) ?>
                          </a>
                        </li>
                        <?php $firstAct = false; endforeach; ?>
                      <li><a href="activities"
                          style="margin-top:12px;border-top:1px solid #E5DDD0;padding-top:12px;">View All Activities</a>
                      </li>
                    </ul>
                  </div>
                  <div class="fa-mm-links">
                    <?php $firstAct = true;
                    foreach ($navActByCategory as $category => $cActs):
                      $panelId = 'act-' . strtolower(preg_replace('/[^a-z0-9]/i', '-', $category));
                      ?>
                      <div class="mm-panel" data-id="<?= $panelId ?>"
                        style="display:<?= $firstAct ? 'block' : 'none' ?>;">
                        <ul>
                          <?php foreach ($cActs as $navActLoop): 
                            $cImg = $navActLoop['featured_image'];
                            if (!empty($cImg) && !str_starts_with($cImg, 'http') && !str_starts_with($cImg, 'images/')) $cImg = 'uploads/' . $cImg;
                            $cImg = $cImg ?: 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg';
                          ?>
                            <li><a
                                href="activities/<?= htmlspecialchars($navActLoop['slug']) ?>"
                                data-img="<?= htmlspecialchars($cImg) ?>"
                                data-caption="<?= htmlspecialchars($navActLoop['name']) ?>"><?= htmlspecialchars($navActLoop['name']) ?></a>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      </div>
                      <?php $firstAct = false; endforeach; ?>
                  </div>
                  <div class="fa-mm-image">
                    <img id="mm-act-img" src="<?= htmlspecialchars($firstImg) ?>" alt="Activities">
                    <div class="mm-caption" id="mm-act-caption"><?= htmlspecialchars($firstCat) ?></div>
                  </div>
                </div>
              </div>
            </div>
          </li>

          <!-- TOURS -->
          <li>
            <a href="tours" class="nav-top-link">Tours</a>
            <div class="fa-megamenu">
              <button class="mm-close-btn">&times; Close</button>
              <div class="fa-megamenu-content">
                <div class="fa-megamenu-inner">
                  <div class="fa-mm-tabs">
                    <span class="mm-heading">Tours by Country</span>
                    <ul>
                      <?php $firstCountry = true;
                      foreach ($navToursByCountry as $country => $cTours): ?>
                        <li class="<?= $firstCountry ? 'mm-active' : '' ?>">
                          <a href="#" class="mm-tab-trigger"
                            data-panel="tour-<?= strtolower(preg_replace('/[^a-z0-9]/i', '-', $country)) ?>"
                            data-img="images/Filao/East Africa/pexels-droneafrica-13234382.jpg"
                            data-caption="<?= htmlspecialchars($country) ?> Safaris"><?= htmlspecialchars($country) ?>
                          </a>
                        </li>
                        <?php $firstCountry = false; endforeach; ?>
                      <li><a href="tours" style="margin-top:12px;border-top:1px solid #E5DDD0;padding-top:12px;">View
                          All Tours</a></li>
                    </ul>
                  </div>
                  <div class="fa-mm-links">
                    <?php $firstCountry = true;
                    foreach ($navToursByCountry as $country => $cTours): ?>
                      <div class="mm-panel" data-id="tour-<?= strtolower(preg_replace('/[^a-z0-9]/i', '-', $country)) ?>"
                        style="display:<?= $firstCountry ? 'block' : 'none' ?>">
                        <ul>
                          <?php foreach ($cTours as $t): 
                            $cImg = $t['featured_image'];
                            if (!empty($cImg) && !str_starts_with($cImg, 'http') && !str_starts_with($cImg, 'images/')) $cImg = 'uploads/' . $cImg;
                            $cImg = $cImg ?: 'images/Filao/East Africa/pexels-balazsimon-15994023.jpg';
                          ?>
                            <li><a href="tours/<?= $t['slug'] ?>"
                                data-img="<?= htmlspecialchars($cImg) ?>"
                                data-caption="<?= htmlspecialchars($t['title']) ?>"><?= htmlspecialchars($t['title']) ?></a></li>
                          <?php endforeach; ?>
                        </ul>
                      </div>
                      <?php $firstCountry = false; endforeach; ?>
                  </div>
                  <div class="fa-mm-image">
                    <img id="mm-tour-img" src="images/Filao/East Africa/pexels-balazsimon-15994023.jpg"
                      alt="Safari Tours">
                    <div class="mm-caption" id="mm-tour-caption">Explore Our Safari Tours</div>
                  </div>
                </div>
              </div>
            </div>
          </li>

          <!-- WE RECOMMEND -->
          <li>
            <a href="#" class="nav-top-link">We Recommend</a>
            <div class="fa-megamenu">
              <button class="mm-close-btn">&times; Close</button>
              <div class="fa-megamenu-content">
                <div class="fa-megamenu-inner">
                  <div class="fa-mm-tabs">
                    <span class="mm-heading">By Activity</span>
                    <ul>
                      <?php $firstAct = true;
                      foreach ($navRecByActivity as $activity => $recTours): ?>
                        <li class="<?= $firstAct ? 'mm-active' : '' ?>">
                          <a href="#" class="mm-tab-trigger"
                            data-panel="rec-<?= strtolower(preg_replace('/[^a-z0-9]/i', '-', $activity)) ?>"
                            data-img="images/Filao/East Africa/pexels-balazsimon-15993990.jpg"
                            data-caption="<?= htmlspecialchars($activity) ?> Tours"><?= htmlspecialchars($activity) ?>
                          </a>
                        </li>
                        <?php $firstAct = false; endforeach; ?>
                      <?php if (empty($navRecByActivity)): ?>
                        <li><a href="tours">All Tours</a></li><?php endif; ?>
                      <li><a href="tours" style="margin-top:12px;border-top:1px solid #E5DDD0;padding-top:12px;">View
                          All Tours</a></li>
                      <li><a href="hot-deals" style="margin-top:12px;border-top:1px solid #E5DDD0;padding-top:12px;">View all hot deals</a></li>
                    </ul>
                  </div>
                  <div class="fa-mm-links">
                    <?php if (empty($navRecByActivity)): ?>
                      <div class="mm-panel" style="display:block;">
                        <p style="color:#6B6358;font-size:14px;padding:20px 0;">No recommendations yet. Mark tours in the
                          admin panel.</p>
                      </div>
                    <?php else:
                      $firstAct = true;
                      foreach ($navRecByActivity as $activity => $recTours): ?>
                        <div class="mm-panel" data-id="rec-<?= strtolower(preg_replace('/[^a-z0-9]/i', '-', $activity)) ?>"
                          style="display:<?= $firstAct ? 'block' : 'none' ?>">
                          <ul>
                            <?php foreach ($recTours as $rt): ?>
                              <li><a
                                  href="tours/<?= $rt['slug'] ?>" data-img="<?= (!empty($rt['featured_image'])) ? (str_starts_with($rt['featured_image'], 'images/') ? $rt['featured_image'] : 'uploads/' . $rt['featured_image']) : 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg' ?>" data-caption="<?= htmlspecialchars($rt['title']) ?>"><?= htmlspecialchars($rt['title']) ?><?php if ($rt['duration_days']): ?>
                                    <span style="font-size:11px;color:#9E9083;"> &ndash; <?= $rt['duration_days'] ?>
                                      Days</span><?php endif; ?></a></li>
                            <?php endforeach; ?>
                          </ul>
                        </div>
                        <?php $firstAct = false; endforeach; endif; ?>
                  </div>
                  <div class="fa-mm-image">
                    <img id="mm-rec-img" src="images/Filao/East Africa/pexels-balazsimon-15993990.jpg"
                      alt="Recommended Tours">
                    <div class="mm-caption" id="mm-rec-caption">Our Specialist Picks</div>
                  </div>
                </div>
              </div>
            </div>
          </li>

          <!-- BLOG -->
          <li><a href="blog">Blog</a></li>

          <!-- MORE OPTIONS (MOBILE ONLY) -->
          <li class="d-lg-none" style="margin-top:10px; border-top:1px solid rgba(255,255,255,0.1); padding-top:15px;">
             <button id="fa-mobile-more" style="background:none;border:none;color:#C49018;font-family:'Inter',sans-serif;font-size:16px;font-weight:600;cursor:pointer;padding:0;display:flex;align-items:center;text-transform:uppercase;letter-spacing:0.05em;">
                More Options <i class="fa fa-angle-right" style="margin-left:8px;"></i>
             </button>
          </li>

        </ul>
      </nav>

      <!-- Right Controls -->
      <div class="fa-navbar-controls">
        <button class="fa-nav-toggle" id="fa-search-toggle" aria-label="Search">
          <i class="fa fa-search"></i>
        </button>
        <button class="fa-nav-toggle" id="fa-menu-open" aria-label="Open menu">
          <i class="fa fa-bars"></i> <span>Menu</span>
        </button>
        <button data-open-planner="true" class="btn-filao-cta"
          style="cursor:pointer;border:1px solid rgba(255,255,255,.35);">Start Planning</button>
      </div>

    </div><!-- /.fa-nav-row-inner -->
  </div><!-- /.fa-nav-row -->

  <!-- Search Bar -->
  <form action="tours" method="GET" id="fa-search-bar" style="display:none;background:#fff;padding:14px 24px;border-top:1px solid #E5DDD0;position:relative; margin:0;">
    <div style="max-width:600px;width:100%;margin:0 auto;display:flex;gap:10px;position:relative;">
      <input type="text" name="q" id="fa-search-input" placeholder="Search tours, destinations..."
        style="flex:1;padding:10px 16px;border:1px solid #E5DDD0;border-radius:4px;font-family:'Inter',sans-serif;font-size:14px;outline:none;" autocomplete="off">
      <button type="submit"
        style="background:#C49018;color:#fff;border:none;padding:10px 22px;border-radius:4px;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;cursor:pointer;font-family:'Inter',sans-serif;">Search</button>
      <button type="button" id="fa-search-close"
        style="background:none;border:none;cursor:pointer;font-size:20px;color:#6B6358;">&times;</button>
        
      <!-- AJAX Search Results Dropdown -->
      <div id="fa-search-results" style="display:none;position:absolute;top:100%;left:0;width:calc(100% - 100px);background:#fff;border:1px solid #E5DDD0;border-radius:0 0 6px 6px;box-shadow:0 10px 25px rgba(0,0,0,0.1);z-index:1000;max-height:400px;overflow-y:auto;margin-top:2px;">
      </div>
    </div>
  </form>

</header>

<!-- ====== HAMBURGER OVERLAY ====== -->
<div class="fa-hamburger-overlay" id="fa-hamburger-overlay">
  <div class="hb-topbar">
    <div class="hb-left">
      <a href="tel:+254757139239"><i class="fa fa-phone mr-1"></i> +254 757 139239</a>
      <a href="mailto:info@filaoadventures.co.ke" class="d-none d-md-inline"><i class="fa fa-envelope mr-1"></i>
        info@filaoadventures.co.ke</a>
    </div>
    <div class="hb-right">
      <button class="fa-close-btn" id="fa-menu-close" aria-label="Close menu">
        <i class="fa fa-times"></i> Close
      </button>
    </div>
  </div>

  <div class="hb-body">
    <div class="hb-col">
      <h4>Who We Are</h4>
      <ul>
        <li><a href="about">About Us</a></li>

        <li><a href="accreditations">Our Accreditations</a></li>
        <li><a href="testimonials">Client Testimonials</a></li>
        <li><a href="sustainable-tourism">Sustainable Tourism</a></li>
        <li><a href="privacy-policy">Privacy Policy</a></li>
        <li><a href="careers">Careers</a></li>
        <li><a href="contact">Contact Us</a></li>
      </ul>

    </div>
    <div class="hb-col">
      <h4>Book With Us</h4>
      <ul>
        <li><a href="why-us">Why Book With Filao?</a></li>
        <li><a href="tailor-made">Tailor-Made Itineraries</a></li>
        <li><a href="travel-confidence">Travel With Confidence</a></li>
        <li><a href="booking-terms">Booking Terms</a></li>
        <li><a href="travel-insurance">Travel Insurance</a></li>
        <li><a href="best-price-guarantee">Best Price Guarantee</a></li>
        <li><a href="best-time-to-visit">Best Time to Visit Africa</a></li>
      </ul>
    </div>
    <div class="hb-col">
      <h4>Our Impact</h4>
      <img src="images/Filao/East Africa/pexels-muganineza-arsene-2152761221-34784514.jpg" alt="Conservation"
        class="hb-impact-img">
      <p class="hb-impact-text">At Filao Adventures, we believe travel should leave a positive mark. We partner with
        eco-certified lodges, support local Maasai communities, and contribute to Kenya's wildlife conservation efforts
        on every journey we craft.</p>
    </div>
  </div>

  <div class="hb-footer">
    <div class="hb-socials">
      <a href="https://x.com/FilaoAdventures" aria-label="X (Twitter)" target="_blank" rel="noopener noreferrer" style="margin-right:15px;"><i class="fa fa-twitter"></i></a>
      <a href="https://www.facebook.com/profile.php?id=100084891550126#" aria-label="Facebook" target="_blank" rel="noopener noreferrer"><i class="fa fa-facebook"></i></a>
      <a href="https://www.instagram.com/filaoadventures/" aria-label="Instagram" target="_blank" rel="noopener noreferrer"><i class="fa fa-instagram"></i></a>
      <a href="https://www.tiktok.com/@filaoadventures" aria-label="TikTok" target="_blank" rel="noopener noreferrer">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-1px;">
          <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z"/>
        </svg>
      </a>
      <a href="https://ke.linkedin.com/jobs/view/travel-consultant-at-filao-adventures-4398464574" aria-label="LinkedIn" target="_blank" rel="noopener noreferrer"><i class="fa fa-linkedin"></i></a>
      <a href="https://wa.me/254757139239" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer"><i class="fa fa-whatsapp"></i></a>
    </div>
    <div class="hb-copy">&copy; <?php echo date('Y'); ?> Filao Adventures. All Rights Reserved.</div>
  </div>
</div>
<!-- ====== RHINO-STYLE MOBILE MENU ====== -->
<div id="rmm-overlay" class="rmm-overlay d-lg-none"></div>
<nav id="rmm-menu" class="rmm-menu d-lg-none">
  <!-- Header (Logo + Close) -->
  <div class="rmm-header">
    <img src="assets/logo/filao-logo.png" alt="Filao Logo">
    <div class="rmm-controls">
      <button class="rmm-close-btn">&times;</button>
    </div>
  </div>

  <!-- Panels Container -->
  <div class="rmm-panels-wrapper">
    <!-- Main Panel (Level 0) -->
    <div class="rmm-panel rmm-panel-main rmm-active" id="rmm-panel-main">
      <ul class="rmm-links rmm-bg-white">
        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-tours">TOURS <i class="fa fa-angle-right"></i></a></li>
        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-destinations">DESTINATIONS <i class="fa fa-angle-right"></i></a></li>
        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-safaris">SAFARI EXPERIENCES <i class="fa fa-angle-right"></i></a></li>
        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-activities">ACTIVITIES <i class="fa fa-angle-right"></i></a></li>
        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-recommend">WE RECOMMEND <i class="fa fa-angle-right"></i></a></li>
        <li><a href="blog">BLOG</a></li>
      </ul>
      <div class="rmm-bottom rmm-bg-gray">
        <ul class="rmm-links" style="margin-bottom:15px;">
          <li><a href="#" class="rmm-trigger" data-target="rmm-panel-who-we-are">WHO WE ARE <i class="fa fa-angle-right"></i></a></li>
          <li><a href="#" class="rmm-trigger" data-target="rmm-panel-book-with-us">BOOK WITH US <i class="fa fa-angle-right"></i></a></li>
        </ul>
        <div class="rmm-contact-info">
          <p><i class="fa fa-phone" style="margin-right:8px;color:#C49018;"></i> <a href="tel:+254757139239" style="color:#000;text-decoration:none;font-weight:600;">+254 757 139239</a></p>
          <p><i class="fa fa-envelope" style="margin-right:8px;color:#C49018;"></i> <a href="mailto:info@filaoadventures.co.ke" style="color:#000;text-decoration:none;font-size:13px;font-weight:600;">info@filaoadventures.co.ke</a></p>
          <div class="rmm-socials" style="margin-top:20px;justify-content:center;">
             <a href="https://x.com/FilaoAdventures" target="_blank"><i class="fa fa-twitter"></i></a>
             <a href="https://www.facebook.com/profile.php?id=100084891550126#" target="_blank"><i class="fa fa-facebook"></i></a>
             <a href="https://www.instagram.com/filaoadventures/" target="_blank"><i class="fa fa-instagram"></i></a>
             <a href="https://www.tiktok.com/@filaoadventures" target="_blank">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-1px;">
                 <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z"/>
               </svg>
             </a>
             <a href="#"><i class="fa fa-youtube"></i></a>
             <a href="https://wa.me/254757139239"><i class="fa fa-whatsapp"></i></a>
          </div>
        </div>
      </div>
    </div>

    <!-- Destinations Panel (Level 1) -->
    <div class="rmm-panel" id="rmm-panel-destinations">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-main"><i class="fa fa-angle-left"></i></button>
        <span>DESTINATIONS</span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <?php foreach($navDestinations as $navDestLoop): ?>
          <li><a href="#" class="rmm-trigger" data-target="rmm-panel-dest-<?= $navDestLoop['id'] ?>"><?= strtoupper(htmlspecialchars($navDestLoop['name'])) ?> <i class="fa fa-angle-right"></i></a></li>
        <?php endforeach; ?>
        <li class="rmm-view-all" style="margin-top:20px;"><a href="destinations">VIEW ALL DESTINATIONS</a></li>
      </ul>
    </div>

    <!-- Safari Experiences Panel (Level 1) -->
    <div class="rmm-panel" id="rmm-panel-safaris">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-main"><i class="fa fa-angle-left"></i></button>
        <span>SAFARI EXPERIENCES</span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <?php foreach($navSafarisByTheme as $themeName => $themeTours): ?>
          <li><a href="#" class="rmm-trigger" data-target="rmm-panel-saf-<?= strtolower(preg_replace('/[^a-z0-9]/i', '-', $themeName)) ?>"><?= strtoupper(htmlspecialchars($themeName)) ?> <i class="fa fa-angle-right"></i></a></li>
        <?php endforeach; ?>
        <li class="rmm-view-all" style="margin-top:20px;"><a href="safaris">VIEW ALL SAFARI EXPERIENCES</a></li>
      </ul>
    </div>

    <!-- We Recommend Panel (Level 1) -->
    <div class="rmm-panel" id="rmm-panel-recommend">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-main"><i class="fa fa-angle-left"></i></button>
        <span>WE RECOMMEND</span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <?php foreach($navRecByActivity as $activity => $recTours): ?>
          <li><a href="#" class="rmm-trigger" data-target="rmm-panel-rec-<?= strtolower(preg_replace('/[^a-z0-9]/i', '-', $activity)) ?>"><?= strtoupper(htmlspecialchars($activity)) ?> <i class="fa fa-angle-right"></i></a></li>
        <?php endforeach; ?>
        <li class="rmm-view-all" style="margin-top:20px;"><a href="tours">VIEW ALL RECOMMENDED</a></li>
      </ul>
    </div>

    <!-- Tours Panel (Level 1) -->
    <div class="rmm-panel" id="rmm-panel-tours">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-main"><i class="fa fa-angle-left"></i></button>
        <span>TOURS</span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <?php foreach($navToursByCountry as $country => $cTours): ?>
          <li><a href="#" class="rmm-trigger" data-target="rmm-panel-tours-<?= strtolower(preg_replace('/[^a-z0-9]/i', '-', $country)) ?>"><?= strtoupper(htmlspecialchars($country)) ?> <i class="fa fa-angle-right"></i></a></li>
        <?php endforeach; ?>
        <li class="rmm-view-all" style="margin-top:20px;"><a href="tours">VIEW ALL TOURS</a></li>
      </ul>
    </div>

    <!-- Activities Panel (Level 1) -->
    <div class="rmm-panel" id="rmm-panel-activities">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-main"><i class="fa fa-angle-left"></i></button>
        <span>ACTIVITIES</span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <?php foreach($navActByCategory as $category => $cActs): ?>
          <li><a href="#" class="rmm-trigger" data-target="rmm-panel-act-<?= strtolower(preg_replace('/[^a-z0-9]/i', '-', $category)) ?>"><?= strtoupper(htmlspecialchars($category)) ?> <i class="fa fa-angle-right"></i></a></li>
        <?php endforeach; ?>
        <li class="rmm-view-all" style="margin-top:20px;"><a href="activities">VIEW ALL ACTIVITIES</a></li>
      </ul>
    </div>

    <!-- Dynamically Generated Level 2 Panels -->
    <!-- Destinations Level 2 -->
    <?php foreach ($navDestinations as $navDestLoop): ?>
    <div class="rmm-panel" id="rmm-panel-dest-<?= $navDestLoop['id'] ?>">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-destinations"><i class="fa fa-angle-left"></i></button>
        <span><?= strtoupper(htmlspecialchars($navDestLoop['name'])) ?></span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <?php if(!empty($navDestLoop['tours'])): foreach ($navDestLoop['tours'] as $t): ?>
          <li><a href="tours/<?= $t['slug'] ?>"><?= strtoupper(htmlspecialchars($t['title'])) ?></a></li>
        <?php endforeach; else: ?>
          <li><a href="#">NO TOURS YET</a></li>
        <?php endif; ?>
        <li class="rmm-view-all" style="margin-top:20px;"><a href="destinations#<?= htmlspecialchars($navDestLoop['slug']) ?>">VIEW ALL IN <?= strtoupper(htmlspecialchars($navDestLoop['name'])) ?></a></li>
      </ul>
    </div>
    <?php endforeach; ?>

    <!-- Safari Experiences Level 2 -->
    <?php foreach ($navSafarisByTheme as $themeName => $themeTours): ?>
    <div class="rmm-panel" id="rmm-panel-saf-<?= strtolower(preg_replace('/[^a-z0-9]/i', '-', $themeName)) ?>">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-safaris"><i class="fa fa-angle-left"></i></button>
        <span><?= strtoupper(htmlspecialchars($themeName)) ?></span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <?php foreach ($themeTours as $t): ?>
          <li><a href="tours/<?= $t['tour_slug'] ?>"><?= strtoupper(htmlspecialchars($t['title'])) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endforeach; ?>

    <!-- We Recommend Level 2 -->
    <?php foreach ($navRecByActivity as $activity => $recTours): ?>
    <div class="rmm-panel" id="rmm-panel-rec-<?= strtolower(preg_replace('/[^a-z0-9]/i', '-', $activity)) ?>">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-recommend"><i class="fa fa-angle-left"></i></button>
        <span><?= strtoupper(htmlspecialchars($activity)) ?></span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <?php foreach ($recTours as $rt): ?>
          <li><a href="tours/<?= $rt['slug'] ?>"><?= strtoupper(htmlspecialchars($rt['title'])) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endforeach; ?>

    <!-- Tours Level 2 -->
    <?php foreach ($navToursByCountry as $country => $cTours): ?>
    <div class="rmm-panel" id="rmm-panel-tours-<?= strtolower(preg_replace('/[^a-z0-9]/i', '-', $country)) ?>">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-tours"><i class="fa fa-angle-left"></i></button>
        <span><?= strtoupper(htmlspecialchars($country)) ?></span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <?php foreach ($cTours as $t): ?>
          <li><a href="tours/<?= $t['slug'] ?>"><?= strtoupper(htmlspecialchars($t['title'])) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endforeach; ?>

    <!-- Activities Level 2 -->
    <?php foreach ($navActByCategory as $category => $cActs): ?>
    <div class="rmm-panel" id="rmm-panel-act-<?= strtolower(preg_replace('/[^a-z0-9]/i', '-', $category)) ?>">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-activities"><i class="fa fa-angle-left"></i></button>
        <span><?= strtoupper(htmlspecialchars($category)) ?></span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <?php foreach ($cActs as $navActLoop): ?>
          <li><a href="activities/<?= $navActLoop['slug'] ?>"><?= strtoupper(htmlspecialchars($navActLoop['name'])) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endforeach; ?>

    <!-- Who We Are Panel (Level 1) -->
    <div class="rmm-panel" id="rmm-panel-who-we-are">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-main"><i class="fa fa-angle-left"></i></button>
        <span>WHO WE ARE</span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <li><a href="about">ABOUT US</a></li>

        <li><a href="accreditations">OUR ACCREDITATIONS</a></li>
        <li><a href="testimonials">CLIENT TESTIMONIALS</a></li>
        <li><a href="sustainable-tourism">SUSTAINABLE TOURISM</a></li>
        <li><a href="privacy-policy">PRIVACY POLICY</a></li>
        <li><a href="careers">CAREERS</a></li>
        <li><a href="contact">CONTACT US</a></li>
      </ul>
    </div>

    <!-- Book With Us Panel (Level 1) -->
    <div class="rmm-panel" id="rmm-panel-book-with-us">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-main"><i class="fa fa-angle-left"></i></button>
        <span>BOOK WITH US</span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <li><a href="why-us">WHY BOOK WITH FILAO?</a></li>
        <li><a href="tailor-made">TAILOR-MADE ITINERARIES</a></li>
        <li><a href="travel-confidence">TRAVEL WITH CONFIDENCE</a></li>
        <li><a href="booking-terms">BOOKING TERMS</a></li>
        <li><a href="travel-insurance">TRAVEL INSURANCE</a></li>
        <li><a href="best-price-guarantee">BEST PRICE GUARANTEE</a></li>
        <li><a href="best-time-to-visit">BEST TIME TO VISIT AFRICA</a></li>
      </ul>
    </div>

  </div>
</nav>
