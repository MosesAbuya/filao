<?php
require_once __DIR__ . '/auth_guard.php';
$pdo = getPDO();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $pdo->prepare('DELETE FROM blogs WHERE id=?')->execute([intval($_POST['id'])]);
    header('Location: blogs.php?deleted=1'); exit;
}

$blogs = $pdo->query("SELECT id, title, category, author, status, created_at FROM blogs ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Blog Manager';
include 'partials/head.php';
include 'partials/sidebar.php';
?>
<div class="admin-main">
<?php include 'partials/navbar.php'; ?>
<div class="dashboard-content">
<div class="container-fluid px-3 px-lg-4">

  <div class="page-heading">
    <div class="page-heading-copy">
      <div class="page-icon"><i class="bi bi-newspaper"></i></div>
      <div>
        <span class="eyebrow">Content</span>
        <h1>Blog Manager</h1>
      </div>
    </div>
    <div class="heading-actions">
      <a href="create-blog.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> New Post</a>
    </div>
  </div>

  <?php if (isset($_GET['deleted'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">Blog post deleted successfully. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>
  <?php if (isset($_GET['saved'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">Blog post saved successfully. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>

  <div class="panel">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Author</th>
            <th>Status</th>
            <th>Date</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($blogs)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No blog posts found. <a href="create-blog.php">Create your first post</a>.</td></tr>
          <?php else: ?>
          <?php foreach ($blogs as $blog): ?>
          <tr>
            <td>
              <strong><?= sanitize($blog['title']) ?></strong><br>
              <small class="text-muted">
                <a href="../blog-detail.php?slug=<?= sanitize($blog['id']) ?>" target="_blank">View on site <i class="bi bi-box-arrow-up-right"></i></a>
              </small>
            </td>
            <td><span class="badge bg-info text-dark"><?= sanitize($blog['category'] ?: 'Uncategorized') ?></span></td>
            <td><?= sanitize($blog['author']) ?></td>
            <td>
              <?php if ($blog['status'] === 'published'): ?>
                <span class="badge bg-success">Published</span>
              <?php else: ?>
                <span class="badge bg-warning text-dark">Draft</span>
              <?php endif; ?>
            </td>
            <td><?= date('d M Y', strtotime($blog['created_at'])) ?></td>
            <td class="text-end">
              <a href="edit-blog.php?id=<?= $blog['id'] ?>" class="btn btn-light btn-sm me-1"><i class="bi bi-pencil"></i></a>
              <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteRecord('blogs', <?= $blog['id'] ?>)" title="Delete"><i class="bi bi-trash"></i></button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</div>
<?php include 'partials/footer.php'; ?>
</div>
<?php include 'partials/scripts.php'; ?>
<script>
    function deleteRecord(table, id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('ajax/delete-record.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `table=${table}&id=${id}&csrf_token=<?= csrfGenerate() ?>`
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
            }
        })
    }
</script>
</body></html>
