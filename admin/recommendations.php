<?php
require_once __DIR__ . '/auth_guard.php';
$pdo = getPDO();

try {
    $pdo->exec("ALTER TABLE tours ADD COLUMN is_hot_offer TINYINT(1) DEFAULT 0");
} catch (PDOException $e) {
    // Column might already exist
}

// Handle toggle recommended & hot offer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
  $tid = intval($_POST['tour_id']);
  $rec = intval($_POST['is_recommended']);
  $hot = intval($_POST['is_hot_offer'] ?? 0);
  $ad = intval($_POST['is_active_ad'] ?? 0);
  $join = intval($_POST['is_joining_tour'] ?? 0);
  $act = trim($_POST['recommended_activity'] ?? '');
  $pdo->prepare('UPDATE tours SET is_recommended=?, is_hot_offer=?, is_active_ad=?, is_joining_tour=?, recommended_activity=? WHERE id=?')->execute([$rec, $hot, $ad, $join, $act ?: null, $tid]);
  header('Location: recommendations.php?saved=1');
  exit;
}

// Fetch all published tours with their primary destination country
$tours = $pdo->query("
    SELECT t.id, t.slug, t.title, t.duration_days, t.price_from_usd, t.is_recommended, t.is_hot_offer, t.is_active_ad, t.is_joining_tour, t.recommended_activity, t.featured_image,
           (SELECT d.country FROM destinations d JOIN itinerary_steps ist ON d.id=ist.destination_id WHERE ist.tour_id=t.id ORDER BY ist.step_number LIMIT 1) AS country
    FROM tours t
    WHERE t.status='published'
    ORDER BY t.is_recommended DESC, t.title ASC
")->fetchAll();

$activities = ['Game Drive', 'Bush Walk', 'Balloon Safari', 'Beach Holiday', 'Cultural Visit', 'Mountain Trek', 'Birdwatching', 'Photography Safari', 'Honeymoon', 'Family Safari'];

$pageTitle = 'Recommendations';
include 'partials/head.php';
include 'partials/sidebar.php';
?>
<div class="admin-main">
  <?php include 'partials/navbar.php'; ?>
  <div class="dashboard-content">
    <div class="container-fluid px-3 px-lg-4">

      <div class="page-heading">
        <div class="page-heading-copy">
          <div class="page-icon"><i class="bi bi-star-fill"></i></div>
          <div><span class="eyebrow">Frontend</span>
            <h1>Tour Recommendations</h1>
          </div>
        </div>
      </div>

      <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success alert-dismissible fade show">Recommendations updated. <button class="btn-close"
            data-bs-dismiss="alert"></button></div>
      <?php endif; ?>

      <div class="panel mb-3">
        <div class="panel-header">
          <div>
            <h2>Manage Recommended Tours</h2>
            <p>Check tours to feature in the Recommendations mega menu. Assign an activity category to group them.</p>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Recommendation</th>
                <th style="width:50px;">Hot</th>
                <th style="width:50px;">Active Ad</th>
                <th style="width:50px;">Joining Tour</th>
                <th>Tour</th>
                <th>Country</th>
                <th>Duration</th>
                <th>Activity Group</th>
                <th class="text-end">Save</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tours as $tour): ?>
                <tr class="<?= $tour['is_recommended'] ? 'table-warning' : '' ?>">
                  <td>
                    <div class="form-check">
                      <input class="form-check-input rec-check" type="checkbox" id="rec-<?= $tour['id'] ?>"
                        <?= $tour['is_recommended'] ? 'checked' : '' ?> data-id="<?= $tour['id'] ?>">
                    </div>
                  </td>
                  <td>
                    <div class="form-check">
                      <input class="form-check-input hot-check" type="checkbox" id="hot-<?= $tour['id'] ?>"
                        <?= $tour['is_hot_offer'] ? 'checked' : '' ?> data-id="<?= $tour['id'] ?>">
                    </div>
                  </td>
                  <td>
                    <div class="form-check">
                      <input class="form-check-input ad-check" type="checkbox" id="ad-<?= $tour['id'] ?>"
                        <?= $tour['is_active_ad'] ? 'checked' : '' ?> data-id="<?= $tour['id'] ?>">
                    </div>
                  </td>
                  <td>
                    <div class="form-check">
                      <input class="form-check-input join-check" type="checkbox" id="join-<?= $tour['id'] ?>"
                        <?= $tour['is_joining_tour'] ? 'checked' : '' ?> data-id="<?= $tour['id'] ?>">
                    </div>
                  </td>
                  <td>
                    <?php if ($tour['featured_image']): ?>
                      <?php $img = str_starts_with($tour['featured_image'], 'images/') ? '../' . $tour['featured_image'] : '../uploads/' . $tour['featured_image']; ?>
                      <img src="<?= htmlspecialchars($img) ?>"
                        style="width:50px;height:35px;object-fit:cover;border-radius:3px;margin-right:8px;vertical-align:middle;">
                    <?php endif; ?>
                    <strong><?= sanitize($tour['title']) ?></strong>
                    <?php if ($tour['is_active_ad']): ?>
                      <div class="mt-1">
                        <a href="../ad/<?= $tour['slug'] ?>" target="_blank" class="badge bg-danger text-decoration-none" style="font-size:11px;"><i class="fa fa-external-link me-1"></i>View Ad Page</a>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td><?= sanitize($tour['country'] ?: 'Kenya') ?></td>
                  <td><?= $tour['duration_days'] ?> Days</td>
                  <td>
                    <select class="form-select form-select-sm activity-select" style="min-width:160px;"
                      data-id="<?= $tour['id'] ?>">
                      <option value=""> Select Activity </option>
                      <?php foreach ($activities as $act): ?>
                        <option value="<?= $act ?>" <?= $tour['recommended_activity'] === $act ? 'selected' : '' ?>><?= $act ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td class="text-end">
                    <form method="post" class="d-inline">
                      <input type="hidden" name="action" value="toggle">
                      <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                      <input type="hidden" name="is_recommended" class="rec-val" value="<?= $tour['is_recommended'] ?>">
                      <input type="hidden" name="is_hot_offer" class="hot-val" value="<?= $tour['is_hot_offer'] ?>">
                      <input type="hidden" name="is_active_ad" class="ad-val" value="<?= $tour['is_active_ad'] ?>">
                      <input type="hidden" name="is_joining_tour" class="join-val" value="<?= $tour['is_joining_tour'] ?>">
                      <input type="hidden" name="recommended_activity" class="act-val"
                        value="<?= htmlspecialchars($tour['recommended_activity'] ?? '') ?>">
                      <button type="submit" class="btn btn-sm btn-primary save-btn">Save</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
  <?php include 'partials/footer.php'; ?>
</div>
<script>
  document.querySelectorAll('tbody tr').forEach(function (row) {
    const check = row.querySelector('.rec-check');
    const hotCheck = row.querySelector('.hot-check');
    const adCheck = row.querySelector('.ad-check');
    const joinCheck = row.querySelector('.join-check');
    const sel = row.querySelector('.activity-select');
    const form = row.querySelector('form');
    if (!form) return;
    form.addEventListener('submit', function () {
      form.querySelector('.rec-val').value = check.checked ? 1 : 0;
      form.querySelector('.hot-val').value = hotCheck.checked ? 1 : 0;
      form.querySelector('.ad-val').value = adCheck.checked ? 1 : 0;
      form.querySelector('.join-val').value = joinCheck.checked ? 1 : 0;
      form.querySelector('.act-val').value = sel ? sel.value : '';
    });
  });
</script>
<?php include 'partials/scripts.php'; ?>
</body>

</html>