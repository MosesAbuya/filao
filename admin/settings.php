<?php
require_once __DIR__ . '/auth_guard.php';
$pdo = getPDO();

// Ensure settings table exists
$pdo->exec('CREATE TABLE IF NOT EXISTS settings (id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(100) UNIQUE NOT NULL, setting_value TEXT);');

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = ['site_name', 'contact_email', 'contact_phone', 'facebook_url', 'instagram_url', 'tripadvisor_url', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_from_email', 'smtp_from_name', 'contact_notify_email', 'tour_smtp_host', 'tour_smtp_port', 'tour_smtp_username', 'tour_smtp_password', 'tour_smtp_from_email', 'tour_smtp_from_name', 'tour_notify_email', 'plan_smtp_host', 'plan_smtp_port', 'plan_smtp_username', 'plan_smtp_password', 'plan_smtp_from_email', 'plan_smtp_from_name', 'plan_notify_email', 'agent_smtp_host', 'agent_smtp_port', 'agent_smtp_username', 'agent_smtp_password', 'agent_smtp_from_email', 'agent_smtp_from_name', 'agent_notify_email'];
    
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
    'smtp_from_name' => $settings_raw['smtp_from_name'] ?? 'Filao Adventures',
    'contact_notify_email' => $settings_raw['contact_notify_email'] ?? '',
    'tour_smtp_host' => $settings_raw['tour_smtp_host'] ?? '',
    'tour_smtp_port' => $settings_raw['tour_smtp_port'] ?? '587',
    'tour_smtp_username' => $settings_raw['tour_smtp_username'] ?? '',
    'tour_smtp_password' => $settings_raw['tour_smtp_password'] ?? '',
    'tour_smtp_from_email' => $settings_raw['tour_smtp_from_email'] ?? '',
    'tour_smtp_from_name' => $settings_raw['tour_smtp_from_name'] ?? '',
    'tour_notify_email' => $settings_raw['tour_notify_email'] ?? '',
    'plan_smtp_host' => $settings_raw['plan_smtp_host'] ?? '',
    'plan_smtp_port' => $settings_raw['plan_smtp_port'] ?? '587',
    'plan_smtp_username' => $settings_raw['plan_smtp_username'] ?? '',
    'plan_smtp_password' => $settings_raw['plan_smtp_password'] ?? '',
    'plan_smtp_from_email' => $settings_raw['plan_smtp_from_email'] ?? '',
    'plan_smtp_from_name' => $settings_raw['plan_smtp_from_name'] ?? '',
    'plan_notify_email' => $settings_raw['plan_notify_email'] ?? '',
    'agent_smtp_host' => $settings_raw['agent_smtp_host'] ?? '',
    'agent_smtp_port' => $settings_raw['agent_smtp_port'] ?? '587',
    'agent_smtp_username' => $settings_raw['agent_smtp_username'] ?? '',
    'agent_smtp_password' => $settings_raw['agent_smtp_password'] ?? '',
    'agent_smtp_from_email' => $settings_raw['agent_smtp_from_email'] ?? '',
    'agent_smtp_from_name' => $settings_raw['agent_smtp_from_name'] ?? '',
    'agent_notify_email' => $settings_raw['agent_notify_email'] ?? '',
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
            <div class='panel-header mt-4'>
              <div>
                <h2 class='h5 mb-1 section-title'><i class='bi bi-envelope'></i> Email &amp; Notification Settings</h2>
                <p class='text-muted mb-0'>Configure where notifications are sent and the outgoing SMTP server for each form.</p>
              </div>
            </div>

            <nav>
              <div class='nav nav-tabs' id='nav-tab' role='tablist'>
                <button class='nav-link active' id='nav-general-tab' data-bs-toggle='tab' data-bs-target='#nav-general' type='button' role='tab'>General / Contact</button>
                <button class='nav-link' id='nav-tour-tab' data-bs-toggle='tab' data-bs-target='#nav-tour' type='button' role='tab'>Tour Inquiries</button>
                <button class='nav-link' id='nav-plan-tab' data-bs-toggle='tab' data-bs-target='#nav-plan' type='button' role='tab'>Start Planning</button>
                <button class='nav-link' id='nav-agent-tab' data-bs-toggle='tab' data-bs-target='#nav-agent' type='button' role='tab'>Agent Applications</button>
              </div>
            </nav>
            <div class='tab-content border border-top-0 p-3 mb-4' id='nav-tabContent'>
                
        <div class='tab-pane fade show active' id='nav-general' role='tabpanel'>
            <div class='mt-3 mb-4'>
                <h3 class='h6 mb-1'>General / Contact Form</h3>
                <p class='text-muted small'>Default settings used by the general contact form and as a fallback if other forms are not explicitly configured.</p>
            </div>
            
            <div class='mb-3'>
              <label class='form-label' for='contact_notify_email'><strong>Company Notify Email</strong> (Receives inquiries/submissions)</label>
              <input class='form-control' id='contact_notify_email' name='contact_notify_email' type='email' value='<?= htmlspecialchars($settings['contact_notify_email'] ?? ($settings['contact_email'] ?? '')) ?>'>
              <div class='form-text'>Leave blank to fallback to the general contact email.</div>
            </div>
            
            <hr>
            <h4 class='h6 mb-3'>Outgoing Mail Server (SMTP)</h4>
            
            <div class='row'>
                <div class='col-md-8 mb-3'>
                  <label class='form-label' for='smtp_host'>SMTP Host</label>
                  <input class='form-control' id='smtp_host' name='smtp_host' type='text' value='<?= htmlspecialchars($settings['smtp_host']) ?>'>
                </div>
                <div class='col-md-4 mb-3'>
                  <label class='form-label' for='smtp_port'>SMTP Port</label>
                  <input class='form-control' id='smtp_port' name='smtp_port' type='text' value='<?= htmlspecialchars($settings['smtp_port']) ?>'>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='smtp_username'>SMTP Username</label>
                  <input class='form-control' id='smtp_username' name='smtp_username' type='text' value='<?= htmlspecialchars($settings['smtp_username']) ?>'>
                </div>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='smtp_password'>SMTP Password</label>
                  <input class='form-control' id='smtp_password' name='smtp_password' type='password' value='<?= htmlspecialchars($settings['smtp_password']) ?>'>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='smtp_from_email'>From Email</label>
                  <input class='form-control' id='smtp_from_email' name='smtp_from_email' type='email' value='<?= htmlspecialchars($settings['smtp_from_email']) ?>'>
                </div>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='smtp_from_name'>From Name</label>
                  <input class='form-control' id='smtp_from_name' name='smtp_from_name' type='text' value='<?= htmlspecialchars($settings['smtp_from_name']) ?>'>
                </div>
            </div>
        </div>
    
                
        <div class='tab-pane fade' id='nav-tour' role='tabpanel'>
            <div class='mt-3 mb-4'>
                <h3 class='h6 mb-1'>Tour Inquiries</h3>
                <p class='text-muted small'>Settings for inquiries made on specific tour pages.</p>
            </div>
            
            <div class='mb-3'>
              <label class='form-label' for='tour_notify_email'><strong>Company Notify Email</strong> (Receives inquiries/submissions)</label>
              <input class='form-control' id='tour_notify_email' name='tour_notify_email' type='email' value='<?= htmlspecialchars($settings['tour_notify_email'] ?? ($settings['contact_email'] ?? '')) ?>'>
              <div class='form-text'>Leave blank to fallback to the general contact email.</div>
            </div>
            
            <hr>
            <h4 class='h6 mb-3'>Outgoing Mail Server (SMTP)</h4>
            
            <div class='row'>
                <div class='col-md-8 mb-3'>
                  <label class='form-label' for='tour_smtp_host'>SMTP Host</label>
                  <input class='form-control' id='tour_smtp_host' name='tour_smtp_host' type='text' value='<?= htmlspecialchars($settings['tour_smtp_host']) ?>'>
                </div>
                <div class='col-md-4 mb-3'>
                  <label class='form-label' for='tour_smtp_port'>SMTP Port</label>
                  <input class='form-control' id='tour_smtp_port' name='tour_smtp_port' type='text' value='<?= htmlspecialchars($settings['tour_smtp_port']) ?>'>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='tour_smtp_username'>SMTP Username</label>
                  <input class='form-control' id='tour_smtp_username' name='tour_smtp_username' type='text' value='<?= htmlspecialchars($settings['tour_smtp_username']) ?>'>
                </div>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='tour_smtp_password'>SMTP Password</label>
                  <input class='form-control' id='tour_smtp_password' name='tour_smtp_password' type='password' value='<?= htmlspecialchars($settings['tour_smtp_password']) ?>'>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='tour_smtp_from_email'>From Email</label>
                  <input class='form-control' id='tour_smtp_from_email' name='tour_smtp_from_email' type='email' value='<?= htmlspecialchars($settings['tour_smtp_from_email']) ?>'>
                </div>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='tour_smtp_from_name'>From Name</label>
                  <input class='form-control' id='tour_smtp_from_name' name='tour_smtp_from_name' type='text' value='<?= htmlspecialchars($settings['tour_smtp_from_name']) ?>'>
                </div>
            </div>
        </div>
    
                
        <div class='tab-pane fade' id='nav-plan' role='tabpanel'>
            <div class='mt-3 mb-4'>
                <h3 class='h6 mb-1'>Start Planning Form</h3>
                <p class='text-muted small'>Settings for custom itinerary requests.</p>
            </div>
            
            <div class='mb-3'>
              <label class='form-label' for='plan_notify_email'><strong>Company Notify Email</strong> (Receives inquiries/submissions)</label>
              <input class='form-control' id='plan_notify_email' name='plan_notify_email' type='email' value='<?= htmlspecialchars($settings['plan_notify_email'] ?? ($settings['contact_email'] ?? '')) ?>'>
              <div class='form-text'>Leave blank to fallback to the general contact email.</div>
            </div>
            
            <hr>
            <h4 class='h6 mb-3'>Outgoing Mail Server (SMTP)</h4>
            
            <div class='row'>
                <div class='col-md-8 mb-3'>
                  <label class='form-label' for='plan_smtp_host'>SMTP Host</label>
                  <input class='form-control' id='plan_smtp_host' name='plan_smtp_host' type='text' value='<?= htmlspecialchars($settings['plan_smtp_host']) ?>'>
                </div>
                <div class='col-md-4 mb-3'>
                  <label class='form-label' for='plan_smtp_port'>SMTP Port</label>
                  <input class='form-control' id='plan_smtp_port' name='plan_smtp_port' type='text' value='<?= htmlspecialchars($settings['plan_smtp_port']) ?>'>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='plan_smtp_username'>SMTP Username</label>
                  <input class='form-control' id='plan_smtp_username' name='plan_smtp_username' type='text' value='<?= htmlspecialchars($settings['plan_smtp_username']) ?>'>
                </div>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='plan_smtp_password'>SMTP Password</label>
                  <input class='form-control' id='plan_smtp_password' name='plan_smtp_password' type='password' value='<?= htmlspecialchars($settings['plan_smtp_password']) ?>'>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='plan_smtp_from_email'>From Email</label>
                  <input class='form-control' id='plan_smtp_from_email' name='plan_smtp_from_email' type='email' value='<?= htmlspecialchars($settings['plan_smtp_from_email']) ?>'>
                </div>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='plan_smtp_from_name'>From Name</label>
                  <input class='form-control' id='plan_smtp_from_name' name='plan_smtp_from_name' type='text' value='<?= htmlspecialchars($settings['plan_smtp_from_name']) ?>'>
                </div>
            </div>
        </div>
    
                
        <div class='tab-pane fade' id='nav-agent' role='tabpanel'>
            <div class='mt-3 mb-4'>
                <h3 class='h6 mb-1'>Agent Applications</h3>
                <p class='text-muted small'>Settings for the partner agent application form.</p>
            </div>
            
            <div class='mb-3'>
              <label class='form-label' for='agent_notify_email'><strong>Company Notify Email</strong> (Receives inquiries/submissions)</label>
              <input class='form-control' id='agent_notify_email' name='agent_notify_email' type='email' value='<?= htmlspecialchars($settings['agent_notify_email'] ?? ($settings['contact_email'] ?? '')) ?>'>
              <div class='form-text'>Leave blank to fallback to the general contact email.</div>
            </div>
            
            <hr>
            <h4 class='h6 mb-3'>Outgoing Mail Server (SMTP)</h4>
            
            <div class='row'>
                <div class='col-md-8 mb-3'>
                  <label class='form-label' for='agent_smtp_host'>SMTP Host</label>
                  <input class='form-control' id='agent_smtp_host' name='agent_smtp_host' type='text' value='<?= htmlspecialchars($settings['agent_smtp_host']) ?>'>
                </div>
                <div class='col-md-4 mb-3'>
                  <label class='form-label' for='agent_smtp_port'>SMTP Port</label>
                  <input class='form-control' id='agent_smtp_port' name='agent_smtp_port' type='text' value='<?= htmlspecialchars($settings['agent_smtp_port']) ?>'>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='agent_smtp_username'>SMTP Username</label>
                  <input class='form-control' id='agent_smtp_username' name='agent_smtp_username' type='text' value='<?= htmlspecialchars($settings['agent_smtp_username']) ?>'>
                </div>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='agent_smtp_password'>SMTP Password</label>
                  <input class='form-control' id='agent_smtp_password' name='agent_smtp_password' type='password' value='<?= htmlspecialchars($settings['agent_smtp_password']) ?>'>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='agent_smtp_from_email'>From Email</label>
                  <input class='form-control' id='agent_smtp_from_email' name='agent_smtp_from_email' type='email' value='<?= htmlspecialchars($settings['agent_smtp_from_email']) ?>'>
                </div>
                <div class='col-md-6 mb-3'>
                  <label class='form-label' for='agent_smtp_from_name'>From Name</label>
                  <input class='form-control' id='agent_smtp_from_name' name='agent_smtp_from_name' type='text' value='<?= htmlspecialchars($settings['agent_smtp_from_name']) ?>'>
                </div>
            </div>
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
