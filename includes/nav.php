<?php
// includes/nav.php   Filao Adventures Global Navigation
require_once __DIR__ . '/db.php';
$navPdo = getPDO();

// Tours grouped by destination country from DB
$navCountries = $navPdo->query("
    SELECT DISTINCT d.country
    FROM destinations d
    JOIN itinerary_steps ist ON d.id = ist.destination_id
    JOIN tours t ON t.id = ist.tour_id
    WHERE t.status='published' AND d.country IS NOT NULL AND d.country != ''
    ORDER BY d.country ASC
    LIMIT 8
")->fetchAll(PDO::FETCH_COLUMN);

$navToursByCountry = [];
foreach ($navCountries as $country) {
  $rows = $navPdo->prepare("
        SELECT DISTINCT t.id, t.title, t.slug
        FROM tours t
        JOIN itinerary_steps ist ON t.id = ist.tour_id
        JOIN destinations d ON d.id = ist.destination_id
        WHERE d.country = ? AND t.status='published'
        ORDER BY t.title ASC LIMIT 6
    ");
  $rows->execute([$country]);
  $navToursByCountry[$country] = $rows->fetchAll();
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
        <a href="#" target="_blank" class="text-white" style="opacity: 0.85;"><i class="fa fa-instagram"></i></a>
        <a href="#" target="_blank" class="text-white" style="opacity: 0.85;"><i class="fa fa-facebook"></i></a>
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
                      <li class="mm-active"><a href="#" class="mm-tab-trigger" data-panel="dest-top"
                          data-img="images/Filao/East Africa/pexels-kelly-17291020.jpg"
                          data-caption="The Majestic East African Savannah">Our Top Destinations</a></li>
                      <li><a href="#" class="mm-tab-trigger" data-panel="dest-east"
                          data-img="images/Filao/East Africa/pexels-droneafrica-13234382.jpg"
                          data-caption="Explore East Africa Wildlife">East Africa Wildlife</a></li>
                      <li><a href="#" class="mm-tab-trigger" data-panel="dest-global"
                          data-img="images/Filao/Dubai/pexels-axp-photography-500641970-16412106.jpg"
                          data-caption="Global Luxury Escapes">Global Luxury</a></li>
                      <li><a href="#" class="mm-tab-trigger" data-panel="dest-ocean"
                          data-img="images/Filao/Indian Ocean/pexels-asadphoto-9394268.jpg"
                          data-caption="Pristine Indian Ocean Beaches">Indian Ocean &amp; Beaches</a></li>
                      <li><a href="#" class="mm-tab-trigger" data-panel="dest-parks"
                          data-img="images/Filao/East Africa/pexels-balazsimon-15993990.jpg"
                          data-caption="Iconic National Parks &amp; Reserves">National Parks &amp; Reserves</a></li>
                      <li><a href="destinations"
                          style="margin-top:12px;border-top:1px solid #E5DDD0;padding-top:12px;">View All
                          Destinations</a></li>
                    </ul>
                  </div>
                  <div class="fa-mm-links">
                    <div class="mm-panel" data-id="dest-top" style="display:block;">
                      <ul>
                        <li><a href="destinations/maasai-mara-national-reserve">Maasai Mara National Reserve</a></li>
                        <li><a href="destinations/amboseli-national-park">Amboseli National Park</a></li>
                        <li><a href="destinations/serengeti-national-park">Serengeti National Park</a></li>
                        <li><a href="destinations/ngorongoro-crater">Ngorongoro Crater</a></li>
                        <li><a href="destinations/zanzibar-island">Zanzibar Island</a></li>
                        <li><a href="destinations/diani-beach">Diani Beach</a></li>
                      </ul>
                    </div>
                    <div class="mm-panel" data-id="dest-east" style="display:none;">
                      <ul>
                        <li><a href="destinations/tsavo-east-national-park">Tsavo East National Park</a></li>
                        <li><a href="destinations/lake-nakuru-national-park">Lake Nakuru National Park</a></li>
                        <li><a href="destinations/maasai-mara-national-reserve">Maasai Mara National Reserve</a></li>
                        <li><a href="destinations/amboseli-national-park">Amboseli National Park</a></li>
                      </ul>
                    </div>
                    <div class="mm-panel" data-id="dest-global" style="display:none;">
                      <ul>
                        <li><a href="destinations/nairobi">Nairobi City</a></li>
                        <li><a href="destinations/mombasa-old-town">Mombasa Old Town</a></li>
                      </ul>
                    </div>
                    <div class="mm-panel" data-id="dest-ocean" style="display:none;">
                      <ul>
                        <li><a href="destinations/diani-beach">Diani Beach</a></li>
                        <li><a href="destinations/zanzibar-island">Zanzibar Island</a></li>
                      </ul>
                    </div>
                    <div class="mm-panel" data-id="dest-parks" style="display:none;">
                      <ul>
                        <li><a href="destinations/maasai-mara-national-reserve">Maasai Mara</a></li>
                        <li><a href="destinations/amboseli-national-park">Amboseli</a></li>
                        <li><a href="destinations/serengeti-national-park">Serengeti</a></li>
                        <li><a href="destinations/ngorongoro-crater">Ngorongoro</a></li>
                        <li><a href="destinations/tsavo-east-national-park">Tsavo East</a></li>
                        <li><a href="destinations/lake-nakuru-national-park">Lake Nakuru</a></li>
                      </ul>
                    </div>
                  </div>
                  <div class="fa-mm-image">
                    <img id="mm-dest-img" src="images/Filao/East Africa/pexels-kelly-17291020.jpg" alt="Destinations">
                    <div class="mm-caption" id="mm-dest-caption">The Majestic East African Savannah</div>
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
                          <?php foreach ($cActs as $navActLoop): ?>
                            <li><a
                                href="activities/<?= htmlspecialchars($navActLoop['slug']) ?>"><?= htmlspecialchars($navActLoop['name']) ?></a>
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
                          <?php foreach ($cTours as $t): ?>
                            <li><a href="tours/<?= $t['slug'] ?>"><?= htmlspecialchars($t['title']) ?></a></li>
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

          <!-- SAFARI EXPERIENCES -->
          <li class="has-mega-menu">
            <a href="safaris" class="nav-top-link">Safari Experiences</a>
            <div class="fa-megamenu">
              <button class="mm-close-btn">&times; Close</button>
              <div class="fa-megamenu-content">
                <div class="fa-megamenu-inner">
                  <div class="fa-mm-tabs">
                    <span class="mm-heading">Safari Experiences</span>
                    <ul>
                      <?php $firstSafTheme = true;
                      foreach ($navSafarisByTheme as $themeName => $themeTours): ?>
                        <li class="<?= $firstSafTheme ? 'mm-active' : '' ?>">
                          <a href="#" class="mm-tab-trigger"
                            data-panel="saf-<?= strtolower(preg_replace('/[^a-z0-9]/i', '-', $themeName)) ?>"
                            data-img="<?= (!empty($themeTours[0]['featured_image'])) ? (str_starts_with($themeTours[0]['featured_image'], 'images/') ? $themeTours[0]['featured_image'] : 'uploads/' . $themeTours[0]['featured_image']) : 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg' ?>"
                            data-caption="<?= htmlspecialchars($themeName) ?>"><?= htmlspecialchars($themeName) ?></a>
                        </li>
                        <?php $firstSafTheme = false; endforeach; ?>
                      <?php if (empty($navSafarisByTheme)): ?>
                        <li class="mm-active"><a href="#" class="mm-tab-trigger" data-panel="saf-featured"
                            data-img="images/Filao/East Africa/pexels-balazsimon-15993990.jpg"
                            data-caption="Featured Safaris">Featured Safaris</a></li>
                      <?php endif; ?>
                      <li><a href="safaris" style="margin-top:12px;border-top:1px solid #E5DDD0;padding-top:12px;">View
                          All Safaris</a></li>
                    </ul>
                  </div>
                  <div class="fa-mm-links">
                    <?php if (!empty($navSafarisByTheme)): ?>
                      <?php $firstSafTheme = true;
                      foreach ($navSafarisByTheme as $themeName => $themeTours): ?>
                        <div class="mm-panel" data-id="saf-<?= strtolower(preg_replace('/[^a-z0-9]/i', '-', $themeName)) ?>"
                          style="display:<?= $firstSafTheme ? 'block' : 'none' ?>;">
                          <ul>
                            <?php foreach ($themeTours as $t): ?>
                              <li><a href="tours/<?= $t['tour_slug'] ?>"><?= htmlspecialchars($t['title']) ?></a></li>
                            <?php endforeach; ?>
                          </ul>
                        </div>
                        <?php $firstSafTheme = false; endforeach; ?>
                    <?php else: ?>
                      <div class="mm-panel" data-id="saf-featured" style="display:block;">
                        <ul>
                          <li><a href="#">More safaris coming soon!</a></li>
                        </ul>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="fa-mm-image">
                    <?php
                    // Get the very first tour's image for the default active tab
                    $firstImg = 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg';
                    $firstCaption = 'Featured Safaris';
                    if (!empty($navSafarisByTheme)) {
                      $firstTheme = array_key_first($navSafarisByTheme);
                      $firstTour = $navSafarisByTheme[$firstTheme][0];
                      if (!empty($firstTour['featured_image'])) {
                        $firstImg = str_starts_with($firstTour['featured_image'], 'images/') ? $firstTour['featured_image'] : 'uploads/' . $firstTour['featured_image'];
                      }
                      $firstCaption = $firstTheme;
                    }
                    ?>
                    <img id="mm-safari-img" src="<?= htmlspecialchars($firstImg) ?>" alt="Safari Experiences">
                    <div class="mm-caption" id="mm-safari-caption"><?= htmlspecialchars($firstCaption) ?></div>
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
                                  href="tours/<?= $rt['slug'] ?>"><?= htmlspecialchars($rt['title']) ?><?php if ($rt['duration_days']): ?>
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
  <div id="fa-search-bar" style="display:none;background:#fff;padding:14px 24px;border-top:1px solid #E5DDD0;position:relative;">
    <div style="max-width:600px;width:100%;margin:0 auto;display:flex;gap:10px;position:relative;">
      <input type="text" id="fa-search-input" placeholder="Search tours, destinations..."
        style="flex:1;padding:10px 16px;border:1px solid #E5DDD0;border-radius:4px;font-family:'Inter',sans-serif;font-size:14px;outline:none;" autocomplete="off">
      <button
        style="background:#C49018;color:#fff;border:none;padding:10px 22px;border-radius:4px;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;cursor:pointer;font-family:'Inter',sans-serif;">Search</button>
      <button id="fa-search-close"
        style="background:none;border:none;cursor:pointer;font-size:20px;color:#6B6358;">&times;</button>
        
      <!-- AJAX Search Results Dropdown -->
      <div id="fa-search-results" style="display:none;position:absolute;top:100%;left:0;width:calc(100% - 100px);background:#fff;border:1px solid #E5DDD0;border-radius:0 0 6px 6px;box-shadow:0 10px 25px rgba(0,0,0,0.1);z-index:1000;max-height:400px;overflow-y:auto;margin-top:2px;">
      </div>
    </div>
  </div>

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
        <li><a href="team">Meet The Team</a></li>
        <li><a href="accreditations">Our Accreditations</a></li>
        <li><a href="testimonials">Client Testimonials</a></li>
        <li><a href="sustainable-tourism">Sustainable Tourism</a></li>
        <li><a href="privacy-policy">Privacy Policy</a></li>
        <li><a href="careers">Careers</a></li>
        <li><a href="contact">Contact Us</a></li>
      </ul>
      <div style="margin-top:32px;">
        <h4 style="margin-bottom:8px;">Our Affiliations</h4>
        <a href="https://www.safaribookings.com/" target="_blank" rel="noopener noreferrer">
          <img src="images/Filao/safaribookings.png" alt="Safari Bookings" style="height:35px;opacity:0.9;">
        </a>
      </div>
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
      <a href="#" aria-label="Facebook"><i class="fa fa-facebook"></i></a>
      <a href="#" aria-label="Instagram"><i class="fa fa-instagram"></i></a>
      <a href="#" aria-label="TikTok"><i class="fa fa-music"></i></a>
      <a href="#" aria-label="YouTube"><i class="fa fa-youtube"></i></a>
      <a href="https://wa.me/254757139239" aria-label="WhatsApp"><i class="fa fa-whatsapp"></i></a>
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
             <a href="#"><i class="fa fa-facebook"></i></a>
             <a href="#"><i class="fa fa-instagram"></i></a>
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
        <li><a href="team">MEET THE TEAM</a></li>
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
