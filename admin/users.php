<?php
require_once __DIR__ . '/auth_guard.php';
$pdo = getPDO();

$stmt = $pdo->query("SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// Counts
$total = count($users);
$active = count(array_filter($users, fn($u) => $u['status'] === 'active'));
$pending = count(array_filter($users, fn($u) => $u['status'] === 'pending'));
$suspended = count(array_filter($users, fn($u) => $u['status'] === 'suspended'));

$pageTitle = 'Users';
include 'partials/head.php';
include 'partials/sidebar.php';
?>
<div class="admin-main">
  <?php include 'partials/navbar.php'; ?>
  
  <div class="dashboard-content">
    <div class="container-fluid px-3 px-lg-4 py-4">
      <div class="page-heading">
        <div class="page-heading-copy">
          <span class="page-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
          <div>
            <p class="eyebrow mb-1">Management</p>
            <h1 class="h3 mb-1">Users</h1>
            <p class="text-muted mb-0">Review accounts, roles, and account status.</p>
          </div>
        </div>
      </div>

      <section class="row g-3 mt-1">
        <div class="col-12 col-sm-6 col-xl-3">
          <article class="metric-card metric-primary">
            <div class="metric-top">
              <span class="metric-label">Total Users</span>
              <span class="metric-icon"><i class="bi bi-people"></i></span>
            </div>
            <div class="metric-value"><?= $total ?></div>
          </article>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <article class="metric-card metric-success">
            <div class="metric-top">
              <span class="metric-label">Active</span>
              <span class="metric-icon"><i class="bi bi-check2-circle"></i></span>
            </div>
            <div class="metric-value"><?= $active ?></div>
          </article>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <article class="metric-card metric-warning">
            <div class="metric-top">
              <span class="metric-label">Pending</span>
              <span class="metric-icon"><i class="bi bi-hourglass-split"></i></span>
            </div>
            <div class="metric-value"><?= $pending ?></div>
          </article>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <article class="metric-card metric-danger">
            <div class="metric-top">
              <span class="metric-label">Suspended</span>
              <span class="metric-icon"><i class="bi bi-slash-circle"></i></span>
            </div>
            <div class="metric-value"><?= $suspended ?></div>
          </article>
        </div>
      </section>

      <section class="panel mt-3">
        <div class="panel-header">
          <div>
            <h2 class="h5 mb-1 section-title"><i class="bi bi-table"></i><span>User List</span></h2>
            <p class="text-muted mb-0">Search, review, and manage team member accounts.</p>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table align-middle mb-0" id="usersTable">
            <thead>
              <tr>
                <th scope="col">User</th>
                <th scope="col">Role</th>
                <th scope="col">Status</th>
                <th scope="col">Joined</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($users as $user): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar-img avatar-sm bg-primary text-white d-flex align-items-center justify-content-center" style="border-radius:50%; font-weight:bold;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <div>
                      <p class="fw-semibold mb-0"><?= htmlspecialchars($user['name']) ?></p>
                      <p class="text-muted small mb-0"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                  </div>
                </td>
                <td><span style="text-transform: capitalize;"><?= htmlspecialchars($user['role']) ?></span></td>
                <td>
                    <?php 
                    $badgeClass = 'secondary';
                    if ($user['status'] === 'active') $badgeClass = 'success';
                    if ($user['status'] === 'pending') $badgeClass = 'warning';
                    if ($user['status'] === 'suspended') $badgeClass = 'danger';
                    ?>
                    <span class="badge text-bg-<?= $badgeClass ?>" style="text-transform: capitalize;"><?= htmlspecialchars($user['status']) ?></span>
                </td>
                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
      
    </div>
  </div>
  <?php include 'partials/footer.php'; ?>
</div>
<?php include 'partials/scripts.php'; ?>
</body>
</html>
