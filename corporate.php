<?php
require_once 'includes/db.php';
$pdo = getPDO();

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $first_name = trim($_POST['first_name'] ?? '');
  $last_name = trim($_POST['last_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $company = trim($_POST['company'] ?? '');
  $group_size = trim($_POST['group_size'] ?? '');
  $travel_month = trim($_POST['travel_month'] ?? '');
  $service_type = trim($_POST['service_type'] ?? '');
  $message = trim($_POST['message'] ?? '');

  if ($first_name && $email) {
    try {
      $stmt = $pdo->prepare("INSERT INTO enquiries (type, first_name, last_name, email, phone, message, status) VALUES ('corporate', ?, ?, ?, ?, ?, 'new')");
      $fullMsg = "Company: $company\nGroup Size: $group_size\nTravel Month: $travel_month\nService Type: $service_type\n\n$message";
      $stmt->execute([$first_name, $last_name, $email, $phone, $fullMsg]);
      $success = true;
    } catch (Exception $e) {
      $error = 'Sorry, there was a problem submitting your enquiry. Please try again.';
    }
  } else {
    $error = 'Please fill in your name and email address.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php $base_href = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') ? '/filao/' : '/'; ?>
  <base href="<?= $base_href ?>">
  <title>Corporate &amp; MICE Travel | Filao Adventures</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description"
    content="Filao Adventures delivers premium corporate travel, group incentives, team-building safaris and MICE event management across East Africa and beyond.">
  <link rel="icon" href="assets/favicon_io/favicon.ico">
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css">
  <style>
    /* Stats strip */
    .corp-stats {
      background: #1C1712;
      padding: 32px 0;
    }

    .corp-stat {
      text-align: center;
    }

    .corp-stat .num {
      font-family: 'Cormorant Garant', serif;
      font-size: 42px;
      font-weight: 600;
      color: #C49018;
      line-height: 1;
    }

    .corp-stat .label {
      font-family: 'Inter', sans-serif;
      font-size: 12px;
      font-weight: 500;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.65);
      margin-top: 6px;
    }

    /* Services grid */
    .corp-service-card {
      border: 1px solid #E5DDD0;
      border-radius: 6px;
      padding: 36px 28px;
      background: #fff;
      height: 100%;
      transition: box-shadow 0.3s, transform 0.3s;
    }

    .corp-service-card:hover {
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
      transform: translateY(-4px);
    }

    .corp-service-icon {
      width: 52px;
      height: 52px;
      background: #FAF8F4;
      border: 1px solid #E5DDD0;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
    }

    .corp-service-icon i {
      font-size: 20px;
      color: #C49018;
    }

    .corp-service-card h3 {
      font-family: 'Cormorant Garant', serif;
      font-size: 22px;
      font-weight: 500;
      color: #1C1712;
      margin-bottom: 12px;
    }

    .corp-service-card p {
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      color: #6B6358;
      line-height: 1.7;
      margin: 0;
    }

    /* Why Filao */
    .corp-why {
      background: #FAF8F4;
    }

    .corp-check-item {
      display: flex;
      gap: 14px;
      margin-bottom: 20px;
    }

    .corp-check-item .icon {
      flex-shrink: 0;
      width: 28px;
      height: 28px;
      background: #C49018;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: 2px;
    }

    .corp-check-item .icon i {
      color: #fff;
      font-size: 12px;
    }

    .corp-check-item h5 {
      font-family: 'Inter', sans-serif;
      font-size: 15px;
      font-weight: 600;
      color: #1C1712;
      margin: 0 0 4px;
    }

    .corp-check-item p {
      font-family: 'Inter', sans-serif;
      font-size: 13px;
      color: #6B6358;
      margin: 0;
    }

    /* Form */
    .corp-form-section {
      background: #fff;
    }

    .corp-form-wrap {
      background: #FAF8F4;
      border: 1px solid #E5DDD0;
      border-radius: 8px;
      padding: 48px;
    }

    .corp-form-label {
      font-family: 'Inter', sans-serif;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: #4A4340;
      margin-bottom: 6px;
      display: block;
    }

    .corp-form-control {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #E5DDD0;
      border-radius: 4px;
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      color: #1C1712;
      background: #fff;
      transition: border-color 0.2s;
      outline: none;
    }

    .corp-form-control:focus {
      border-color: #C49018;
    }

    .corp-form-group {
      margin-bottom: 20px;
    }

    .corp-submit {
      background: #C49018;
      color: #fff;
      border: none;
      padding: 16px 40px;
      border-radius: 4px;
      font-family: 'Inter', sans-serif;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      cursor: pointer;
      transition: background 0.2s;
      width: 100%;
    }

    .corp-submit:hover {
      background: #a37614;
    }

    .corp-success {
      background: #d4edda;
      border: 1px solid #c3e6cb;
      color: #155724;
      border-radius: 4px;
      padding: 18px 24px;
      font-family: 'Inter', sans-serif;
      font-size: 14px;
    }

    .corp-error {
      background: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
      border-radius: 4px;
      padding: 14px 24px;
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      margin-bottom: 20px;
    }
  </style>
<?php @include_once __DIR__.'/includes/head_tags.php'; ?>
</head>

<body>
  <?php require_once 'includes/nav.php'; ?>


  <!-- Page Hero -->
  <section class="fa-page-hero" style="background-image:url('images/corporate-hero.jpg');">
    <div class="overlay"></div>
    <div class="container fa-page-hero-content" style="max-width:1280px;">
      <h1>Corporate &amp; MICE Travel</h1>
      <div class="breadcrumb-fa">
        <a href="index">Home</a>
        <span class="bc-sep">&#8250;</span>
        <span class="bc-current">Corporate Travel</span>
      </div>
    </div>
  </section>


  <!-- ── INTRO ── -->
  <section class="section-pad bg-white" style="padding-bottom:0;">
    <div class="container" style="max-width:1280px;">
      <div class="row align-items-center">
        <div class="col-lg-6 mb-5 mb-lg-0 pr-lg-5">
          <div class="fa-section-heading">
            <span class="eyebrow">What Is Corporate Travel?</span>
            <h2>Where Business Goals<br>Meet World-Class Experiences</h2>
          </div>
          <p style="font-family:'Inter',sans-serif;font-size:15px;color:#4A4340;line-height:1.8;margin-bottom:18px;">
            Corporate travel is more than booking flights and hotels. It's a strategic tool a way to motivate teams,
            reward performance, impress clients, and align organisations around shared goals through powerful shared
            experiences.
          </p>
          <p style="font-family:'Inter',sans-serif;font-size:15px;color:#4A4340;line-height:1.8;margin-bottom:18px;">
            At Filao Adventures, we specialise in designing <strong>bespoke corporate travel programmes</strong> that go
            beyond the ordinary. Whether it's a team-building safari across the Maasai Mara, a product launch set
            against the backdrop of Mount Kilimanjaro, or an incentive trip that leaves your top performers speechless
            we handle every single detail.
          </p>
          <p style="font-family:'Inter',sans-serif;font-size:15px;color:#4A4340;line-height:1.8;margin-bottom:28px;">
            We work with businesses of all sizes from start-ups recognising their first key hires to large
            multinationals running MICE events for hundreds of delegates delivering consistent, premium-quality
            experiences every time.
          </p>
          <a href="#corp-enquiry" class="btn-filao-cta"
            style="background:#C49018;border-color:#C49018;padding:14px 32px;font-size:12px;">Request a Proposal</a>
        </div>
        <div class="col-lg-6">
          <div style="position:relative;">
            <img src="images/corporate-hero.jpg" alt="Corporate Travel Africa"
              style="width:100%;border-radius:6px;box-shadow:0 24px 60px rgba(0,0,0,0.14);">
            <!-- Floating badge -->
            <div
              style="position:absolute;bottom:-20px;left:30px;background:#1C1712;color:#fff;padding:18px 28px;border-radius:4px;font-family:'Inter',sans-serif;box-shadow:0 8px 24px rgba(0,0,0,0.25);display:flex;align-items:center;gap:20px;">
              <div style="text-align:center;">
                <div
                  style="font-size:26px;font-weight:700;font-family:'Cormorant Garant',serif;color:#C49018;line-height:1;">
                  500+</div>
                <div
                  style="font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(255,255,255,0.6);margin-top:3px;">
                  Safaris Delivered</div>
              </div>
              <div style="width:1px;height:36px;background:rgba(255,255,255,0.12);"></div>
              <div style="text-align:center;">
                <div
                  style="font-size:26px;font-weight:700;font-family:'Cormorant Garant',serif;color:#C49018;line-height:1;">
                  2018</div>
                <div
                  style="font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(255,255,255,0.6);margin-top:3px;">
                  Founded</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── STATS ── -->
  <section class="corp-stats" style="margin-top:40px;">
    <div class="container" style="max-width:1280px;">
      <div class="row text-center">
        <div class="col-6 col-md-3 mb-4 mb-md-0">
          <div class="corp-stat">
            <div class="num">500+</div>
            <div class="label">Corporate Groups Hosted</div>
          </div>
        </div>
        <div class="col-6 col-md-3 mb-4 mb-md-0">
          <div class="corp-stat">
            <div class="num">12+</div>
            <div class="label">Countries Covered</div>
          </div>
        </div>
        <div class="col-6 col-md-3 mb-4 mb-md-0">
          <div class="corp-stat">
            <div class="num">98%</div>
            <div class="label">Client Satisfaction Rate</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="corp-stat">
            <div class="num">10+</div>
            <div class="label">Years of Experience</div>
          </div>
        </div>
      </div>
    </div>
  </section>



  <!-- ── WHAT WE OFFER ── -->
  <section class="section-pad bg-white">
    <div class="container" style="max-width:1280px;">
      <div class="row justify-content-center mb-5">
        <div class="col-lg-7 text-center">
          <div class="fa-section-heading" style="text-align:center;">
            <span class="eyebrow">Our Corporate Services</span>
            <h2>Tailored Solutions for<br>Every Business Need</h2>
            <p>We understand that corporate travel is about more than logistics. It's about motivation, recognition, and
              building a culture of excellence.</p>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="corp-service-card">
            <div class="corp-service-icon"><i class="fa fa-users"></i></div>
            <h3>Group &amp; Incentive Travel</h3>
            <p>Reward your top performers with once-in-a-lifetime experiences. We craft fully managed incentive trips to
              Maasai Mara, Zanzibar, Serengeti, and luxury destinations worldwide designed to inspire and retain talent.
            </p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="corp-service-card">
            <div class="corp-service-icon"><i class="fa fa-calendar-check-o"></i></div>
            <h3>MICE Event Management</h3>
            <p>From intimate board retreats to large conferences and product launches in the African bush, we handle
              every detail: venue scouting, AV setup, catering, accommodation, and on-the-ground coordination.</p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="corp-service-card">
            <div class="corp-service-icon"><i class="fa fa-leaf"></i></div>
            <h3>Team-Building Safaris</h3>
            <p>Nothing bonds a team quite like watching a lion pride at dawn or navigating the Mara at sunset. Our
              facilitated team-building safari programmes blend wildlife experiences with structured team activities.
            </p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="corp-service-card">
            <div class="corp-service-icon"><i class="fa fa-plane"></i></div>
            <h3>Executive Travel Management</h3>
            <p>Dedicated account management for frequent business travelers. We handle flights, airport transfers,
              premium accommodation, and itinerary changes in real-time so your executives can focus on what matters.
            </p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="corp-service-card">
            <div class="corp-service-icon"><i class="fa fa-handshake-o"></i></div>
            <h3>Client Entertainment</h3>
            <p>Impress clients and business partners with curated experiences private sundowner dinners in the bush,
              chartered dhow cruises along the coast, or exclusive game drives at premier conservancies.</p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="corp-service-card">
            <div class="corp-service-icon"><i class="fa fa-globe"></i></div>
            <h3>Multi-Destination Programs</h3>
            <p>Combine multiple countries in a single seamless itinerary a Nairobi conference followed by a Mara safari,
              then beach time in Zanzibar. We manage every transition to keep your group moving effortlessly.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── WHY FILAO FOR CORPORATE ── -->
  <section class="section-pad corp-why">
    <div class="container" style="max-width:1280px;">
      <div class="row align-items-center">
        <div class="col-lg-6 mb-5 mb-lg-0">
          <div class="fa-section-heading">
            <span class="eyebrow">Why Choose Filao</span>
            <h2>The Corporate Travel<br>Partner You Deserve</h2>
          </div>
          <div class="corp-check-item">
            <div class="icon"><i class="fa fa-check"></i></div>
            <div>
              <h5>Dedicated Corporate Account Manager</h5>
              <p>A single point of contact who knows your company's travel policy, preferences, and budget available
                24/7.</p>
            </div>
          </div>
          <div class="corp-check-item">
            <div class="icon"><i class="fa fa-check"></i></div>
            <div>
              <h5>Fully Managed End-to-End</h5>
              <p>From initial proposal to post-trip reporting, we handle every detail so your HR and admin teams don't
                have to.</p>
            </div>
          </div>
          <div class="corp-check-item">
            <div class="icon"><i class="fa fa-check"></i></div>
            <div>
              <h5>Competitive Group Rates</h5>
              <p>Direct relationships with lodges, airlines, and ground operators mean we pass genuine savings on to
                you.</p>
            </div>
          </div>
          <div class="corp-check-item">
            <div class="icon"><i class="fa fa-check"></i></div>
            <div>
              <h5>CSR &amp; Sustainable Travel Options</h5>
              <p>Incorporate community visits, conservation projects, or carbon offset programmes into your corporate
                itinerary.</p>
            </div>
          </div>
          <div class="corp-check-item">
            <div class="icon"><i class="fa fa-check"></i></div>
            <div>
              <h5>Flexible Invoicing &amp; Reporting</h5>
              <p>Consolidated invoicing, cost-centre reporting, and detailed expense breakdowns tailored to your finance
                team's requirements.</p>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <img src="images/corporate-hero.jpg" alt="Corporate Safari Travel"
            style="width:100%;border-radius:6px;box-shadow:0 20px 60px rgba(0,0,0,0.15);">
        </div>
      </div>
    </div>
  </section>

  <!-- ── ENQUIRY FORM ── -->
  <section class="section-pad corp-form-section" id="corp-enquiry">
    <div class="container" style="max-width:900px;">
      <div class="row justify-content-center mb-5">
        <div class="col-lg-8 text-center">
          <div class="fa-section-heading" style="text-align:center;">
            <span class="eyebrow">Get In Touch</span>
            <h2>Request a Corporate<br>Travel Proposal</h2>
            <p>Tell us about your group and travel goals. Our corporate travel specialists will get back to you within
              24 hours with a tailored proposal.</p>
          </div>
        </div>
      </div>

      <div class="corp-form-wrap">
        <?php if ($success): ?>
          <div class="corp-success">
            <strong>Thank you!</strong> Your enquiry has been received. Our corporate travel team will be in touch within
            24 hours.
          </div>
        <?php else: ?>
          <?php if ($error): ?>
            <div class="corp-error"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <form method="POST" action="corporate#corp-enquiry">
            <div class="row">
              <div class="col-md-6">
                <div class="corp-form-group">
                  <label class="corp-form-label">First Name *</label>
                  <input type="text" name="first_name" class="corp-form-control" placeholder="John" required
                    value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="corp-form-group">
                  <label class="corp-form-label">Last Name</label>
                  <input type="text" name="last_name" class="corp-form-control" placeholder="Kamau"
                    value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="corp-form-group">
                  <label class="corp-form-label">Email Address *</label>
                  <input type="email" name="email" class="corp-form-control" placeholder="john@company.com" required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="corp-form-group">
                  <label class="corp-form-label">Phone / WhatsApp</label>
                  <input type="tel" name="phone" class="corp-form-control" placeholder="+254 700 000 000"
                    value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="corp-form-group">
                  <label class="corp-form-label">Company / Organisation</label>
                  <input type="text" name="company" class="corp-form-control" placeholder="Acme Corporation"
                    value="<?= htmlspecialchars($_POST['company'] ?? '') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="corp-form-group">
                  <label class="corp-form-label">Estimated Group Size</label>
                  <select name="group_size" class="corp-form-control">
                    <option value="">Select group size</option>
                    <option value="5-10" <?= ($_POST['group_size'] ?? '') === '5-10' ? 'selected' : '' ?>>5 – 10 people
                    </option>
                    <option value="11-25" <?= ($_POST['group_size'] ?? '') === '11-25' ? 'selected' : '' ?>>11 – 25 people
                    </option>
                    <option value="26-50" <?= ($_POST['group_size'] ?? '') === '26-50' ? 'selected' : '' ?>>26 – 50 people
                    </option>
                    <option value="51-100" <?= ($_POST['group_size'] ?? '') === '51-100' ? 'selected' : '' ?>>51 – 100 people
                    </option>
                    <option value="100+" <?= ($_POST['group_size'] ?? '') === '100+' ? 'selected' : '' ?>>100+ people
                    </option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="corp-form-group">
                  <label class="corp-form-label">Preferred Travel Period</label>
                  <input type="text" name="travel_month" class="corp-form-control" placeholder="e.g. August 2026"
                    value="<?= htmlspecialchars($_POST['travel_month'] ?? '') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="corp-form-group">
                  <label class="corp-form-label">Type of Corporate Travel</label>
                  <select name="service_type" class="corp-form-control">
                    <option value="">Select service type</option>
                    <option value="Incentive Trip">Incentive / Reward Trip</option>
                    <option value="Team Building Safari">Team-Building Safari</option>
                    <option value="Conference / MICE">Conference / MICE Event</option>
                    <option value="Executive Retreat">Executive Retreat</option>
                    <option value="Client Entertainment">Client Entertainment</option>
                    <option value="Other">Other / Not Sure Yet</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="corp-form-group">
              <label class="corp-form-label">Tell Us More About Your Requirements</label>
              <textarea name="message" class="corp-form-control" rows="5"
                placeholder="Destination preferences, budget range, specific activities, accommodation style, any other details..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="corp-submit">
              <i class="fa fa-paper-plane mr-2"></i> Send My Enquiry
            </button>
            <p style="font-family:'Inter',sans-serif;font-size:12px;color:#9E9289;text-align:center;margin-top:16px;">
              We respond within 24 hours. Your details are kept strictly confidential.
            </p>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ── CTA BANNER ── -->
  <section class="fa-cta-banner"
    style="background-image:url('images/Filao/East Africa/pexels-zacchaeus-rains-262050732-20523197.jpg');">
    <div class="overlay"></div>
    <div class="container fa-cta-content" style="max-width:1280px;">
      <h2>Prefer to Talk? We're Here.</h2>
      <p>Speak directly to our corporate travel specialist. Call or WhatsApp us and we'll design your perfect programme
        together.</p>
      <div class="d-flex justify-content-center gap-3 flex-wrap" style="gap:12px;">
        <a href="tel:+254757139239" class="btn-filao-cta" style="padding:14px 32px;"><i
            class="fa fa-phone mr-2"></i>+254 757 139 239</a>
        <a href="https://wa.me/254757139239" target="_blank" class="btn-filao-outline"
          style="padding:14px 32px;border:2px solid rgba(255,255,255,0.5);color:#fff;border-radius:4px;font-family:'Inter',sans-serif;font-size:13px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:all 0.2s;"><i
            class="fa fa-whatsapp"></i> WhatsApp Us</a>
      </div>
    </div>
  </section>

  <?php require_once 'includes/footer.php'; ?>
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="assets/js/filao-nav.js"></script>
  <script src="js/start-planning.js?v=1781967414"></script>
</body>

</html>