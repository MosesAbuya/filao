<?php
require_once __DIR__ . '/auth_guard.php';
$pdo = getPDO();

// Ensure settings table exists
$pdo->exec('CREATE TABLE IF NOT EXISTS settings (id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(100) UNIQUE NOT NULL, setting_value TEXT);');

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = ['site_name', 'contact_email', 'contact_phone', 'facebook_url', 'instagram_url', 'tripadvisor_url', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_from_email', 'smtp_from_name'];
    
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            $val = trim($_POST[$key]);
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $val, $val]);
        }
    }
    $message = 'Settings saved successfully.';
}

// Fetch current settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$settings = [
    'site_name' => $settings_raw['site_name'] ?? 'Filao Adventures',
    'contact_email' => $settings_raw['contact_email'] ?? 'info@filaoadventures.com',
    'contact_phone' => $settings_raw['contact_phone'] ?? '+254 700 000 000',
    'facebook_url' => $settings_raw['facebook_url'] ?? 'https://facebook.com',
    'instagram_url' => $settings_raw['instagram_url'] ?? 'https://instagram.com',
    'tripadvisor_url' => $settings_raw['tripadvisor_url'] ?? 'https://tripadvisor.com',
    'smtp_host' => $settings_raw['smtp_host'] ?? '',
    'smtp_port' => $settings_raw['smtp_port'] ?? '587',
    'smtp_username' => $settings_raw['smtp_username'] ?? '',
    'smtp_password' => $settings_raw['smtp_password'] ?? '',
    'smtp_from_email' => $settings_raw['smtp_from_email'] ?? '',
    'smtp_from_name' => $settings_raw['smtp_from_name'] ?? 'Filao Adventures'
];

$pageTitle = 'Settings';
include 'partials/head.php';
include 'partials/sidebar.php';
?>
<div class="admin-main">
  <?php include 'partials/navbar.php'; ?>
  
  <div class="dashboard-content">
    <div class="container-fluid px-3 px-lg-4">
      <div class="page-heading">
        <div class="page-heading-copy">
          <div class="page-icon"><i class="bi bi-gear"></i></div>
          <div>
            <span class="eyebrow">Workspace</span>
            <h1>General Settings</h1>
          </div>
        </div>
      </div>

      <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <section class="row g-3">
        <div class="col-12 col-xl-8">
          <form class="panel" method="POST">
            <div class="panel-header">
              <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-sliders"></i> Global Settings</h2>
                <p class="text-muted mb-0">Configure website identity and contact links.</p>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label" for="site_name">Website Name</label>
              <input class="form-control" id="site_name" name="site_name" type="text" value="<?= htmlspecialchars($settings['site_name']) ?>">
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="contact_email">Contact Email</label>
                  <input class="form-control" id="contact_email" name="contact_email" type="email" value="<?= htmlspecialchars($settings['contact_email']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="contact_phone">Contact Phone</label>
                  <input class="form-control" id="contact_phone" name="contact_phone" type="text" value="<?= htmlspecialchars($settings['contact_phone']) ?>">
                </div>
            </div>

            <hr>
            
            <div class="mb-3">
              <label class="form-label" for="facebook_url">Facebook URL</label>
              <input class="form-control" id="facebook_url" name="facebook_url" type="url" value="<?= htmlspecialchars($settings['facebook_url']) ?>">
            </div>
            
            <div class="mb-3">
              <label class="form-label" for="instagram_url">Instagram URL</label>
              <input class="form-control" id="instagram_url" name="instagram_url" type="url" value="<?= htmlspecialchars($settings['instagram_url']) ?>">
            </div>

            <div class="mb-3">
              <label class="form-label" for="tripadvisor_url">TripAdvisor URL</label>
              <input class="form-control" id="tripadvisor_url" name="tripadvisor_url" type="url" value="<?= htmlspecialchars($settings['tripadvisor_url']) ?>">
            </div>

            <hr>
            <div class="panel-header mt-4">
              <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-envelope"></i> SMTP Settings</h2>
                <p class="text-muted mb-0">Configure outgoing email server credentials for forms.</p>
              </div>
            </div>
            <div class="row">
                <div class="col-md-8 mb-3">
                  <label class="form-label" for="smtp_host">SMTP Host</label>
                  <input class="form-control" id="smtp_host" name="smtp_host" type="text" value="<?= htmlspecialchars($settings['smtp_host']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label" for="smtp_port">SMTP Port</label>
                  <input class="form-control" id="smtp_port" name="smtp_port" type="text" value="<?= htmlspecialchars($settings['smtp_port']) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="smtp_username">SMTP Username</label>
                  <input class="form-control" id="smtp_username" name="smtp_username" type="text" value="<?= htmlspecialchars($settings['smtp_username']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="smtp_password">SMTP Password</label>
                  <input class="form-control" id="smtp_password" name="smtp_password" type="password" value="<?= htmlspecialchars($settings['smtp_password']) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="smtp_from_email">From Email</label>
                  <input class="form-control" id="smtp_from_email" name="smtp_from_email" type="email" value="<?= htmlspecialchars($settings['smtp_from_email']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="smtp_from_name">From Name</label>
                  <input class="form-control" id="smtp_from_name" name="smtp_from_name" type="text" value="<?= htmlspecialchars($settings['smtp_from_name']) ?>">
                </div>
            </div>

            <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Save Settings</button>
          </form>
        </div>
      </section>
      
    </div>
  </div>
  <?php include 'partials/footer.php'; ?>
</div>

<?php include 'partials/scripts.php'; ?>
</body>
</html>
