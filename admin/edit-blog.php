<?php
require_once __DIR__ . '/auth_guard.php';
$pdo = getPDO();

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: blogs.php'); exit; }

$blog = $pdo->prepare('SELECT * FROM blogs WHERE id=?');
$blog->execute([$id]);
$blog = $blog->fetch();
if (!$blog) { header('Location: blogs.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $slug     = trim($_POST['slug'] ?? '');
    $excerpt  = trim($_POST['excerpt'] ?? '');
    $body     = $_POST['body'] ?? '';
    $author   = trim($_POST['author'] ?? 'Filao Adventures');
    $category = trim($_POST['category'] ?? '');
    $tags     = trim($_POST['tags'] ?? '');
    $status   = $_POST['status'] ?? 'draft';
    $seo_title = trim($_POST['seo_title'] ?? '');
    $meta_desc = trim($_POST['meta_description'] ?? '');

    if (!$title) $errors[] = 'Title is required.';

    $featured_image = $blog['featured_image'];
    if (!empty($_FILES['featured_image']['name'])) {
        $ext = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
        $fname = 'blog-' . time() . '.' . $ext;
        move_uploaded_file($_FILES['featured_image']['tmp_name'], dirname(__DIR__) . '/uploads/' . $fname);
        $featured_image = $fname;
    }

    if (empty($errors)) {
        $pdo->prepare('UPDATE blogs SET title=?,slug=?,excerpt=?,body=?,featured_image=?,author=?,category=?,tags=?,status=?,seo_title=?,meta_description=?,updated_at=NOW() WHERE id=?')
            ->execute([$title,$slug,$excerpt,$body,$featured_image,$author,$category,$tags,$status,$seo_title,$meta_desc,$id]);
        header('Location: blogs.php?saved=1'); exit;
    }
    $blog = array_merge($blog, $_POST);
}

$pageTitle = 'Edit Blog Post';
include 'partials/head.php';
include 'partials/sidebar.php';
?>
<div class="admin-main">
<?php include 'partials/navbar.php'; ?>
<div class="dashboard-content">
<div class="container-fluid px-3 px-lg-4">

  <div class="page-heading">
    <div class="page-heading-copy">
      <div class="page-icon"><i class="bi bi-pencil-square"></i></div>
      <div><span class="eyebrow">Blog</span><h1>Edit Post</h1></div>
    </div>
    <div class="heading-actions">
      <a href="../blog-detail.php?slug=<?= $blog['slug'] ?>" target="_blank" class="btn btn-outline-secondary me-2"><i class="bi bi-eye me-1"></i> Preview</a>
      <a href="blogs.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>
  </div>

  <?php if ($errors): ?>
  <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="panel mb-4">
          <div class="panel-header"><div><h2>Post Content</h2></div></div>
          <div class="p-4">
            <div class="mb-3">
              <label class="form-label fw-semibold">Title *</label>
              <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($blog['title']) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Slug (URL)</label>
              <div class="input-group">
                <span class="input-group-text">/blog/</span>
                <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($blog['slug']) ?>">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Excerpt / Summary</label>
              <textarea name="excerpt" class="form-control" rows="3"><?= htmlspecialchars($blog['excerpt']) ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Body Content (HTML)</label>
              <textarea name="body" class="form-control" rows="20" style="font-family:monospace;font-size:13px;"><?= htmlspecialchars($blog['body']) ?></textarea>
            </div>
          </div>
        </div>
        <div class="panel">
          <div class="panel-header"><div><h2>SEO Settings</h2></div></div>
          <div class="p-4">
            <div class="mb-3">
              <label class="form-label fw-semibold">SEO Title</label>
              <input type="text" name="seo_title" class="form-control" value="<?= htmlspecialchars($blog['seo_title']) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Meta Description</label>
              <textarea name="meta_description" class="form-control" rows="2"><?= htmlspecialchars($blog['meta_description']) ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="panel mb-4">
          <div class="panel-header"><div><h2>Publish</h2></div></div>
          <div class="p-4">
            <div class="mb-3">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="draft" <?= $blog['status']==='draft'?'selected':'' ?>>Draft</option>
                <option value="published" <?= $blog['status']==='published'?'selected':'' ?>>Published</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i> Update Post</button>
          </div>
        </div>
        <div class="panel mb-4">
          <div class="panel-header"><div><h2>Post Details</h2></div></div>
          <div class="p-4">
            <div class="mb-3">
              <label class="form-label fw-semibold">Author</label>
              <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($blog['author']) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Category</label>
              <select name="category" class="form-select">
                <?php foreach (['Wildlife','Travel Tips','Destinations','Conservation','Luxury Travel','Culture','News'] as $cat): ?>
                <option value="<?= $cat ?>" <?= $blog['category']===$cat?'selected':'' ?>><?= $cat ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Tags</label>
              <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($blog['tags']) ?>">
            </div>
          </div>
        </div>
        <div class="panel">
          <div class="panel-header"><div><h2>Featured Image</h2></div></div>
          <div class="p-4">
            <?php if ($blog['featured_image']): ?>
            <?php $imgSrc = str_starts_with($blog['featured_image'], 'images/') ? '../'.$blog['featured_image'] : '../uploads/'.$blog['featured_image']; ?>
            <img src="<?= htmlspecialchars($imgSrc) ?>" class="img-fluid rounded mb-2" style="max-height:180px;object-fit:cover;width:100%;">
            <?php endif; ?>
            <input type="file" name="featured_image" class="form-control" accept="image/*">
          </div>
        </div>
      </div>
    </div>
  </form>

</div>
</div>
<?php include 'partials/footer.php'; ?>
</div>
<?php include 'partials/scripts.php'; ?>
</body></html>
