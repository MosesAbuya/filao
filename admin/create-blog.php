<?php
require_once __DIR__ . '/auth_guard.php';
$pdo = getPDO();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $slug    = trim($_POST['slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $body    = $_POST['body'] ?? '';
    $author  = trim($_POST['author'] ?? 'Filao Adventures');
    $category = trim($_POST['category'] ?? '');
    $tags    = trim($_POST['tags'] ?? '');
    $status  = $_POST['status'] ?? 'draft';
    $seo_title = trim($_POST['seo_title'] ?? '');
    $meta_desc = trim($_POST['meta_description'] ?? '');

    if (!$title) $errors[] = 'Title is required.';
    if (!$slug) $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));

    // Handle image upload
    $featured_image = '';
    if (!empty($_FILES['featured_image']['name'])) {
        $ext = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
        $fname = 'blog-' . time() . '.' . $ext;
        move_uploaded_file($_FILES['featured_image']['tmp_name'], dirname(__DIR__) . '/uploads/' . $fname);
        $featured_image = $fname;
    }

    if (empty($errors)) {
        $pdo->prepare('INSERT INTO blogs (title,slug,excerpt,body,featured_image,author,category,tags,status,seo_title,meta_description) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$title,$slug,$excerpt,$body,$featured_image,$author,$category,$tags,$status,$seo_title,$meta_desc]);
        header('Location: blogs.php?saved=1'); exit;
    }
}

$pageTitle = 'Create Blog Post';
include 'partials/head.php';
include 'partials/sidebar.php';
?>
<div class="admin-main">
<?php include 'partials/navbar.php'; ?>
<div class="dashboard-content">
<div class="container-fluid px-3 px-lg-4">

  <div class="page-heading">
    <div class="page-heading-copy">
      <div class="page-icon"><i class="bi bi-plus-square"></i></div>
      <div><span class="eyebrow">Blog</span><h1>New Blog Post</h1></div>
    </div>
    <div class="heading-actions">
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
              <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required id="postTitle">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Slug (URL)</label>
              <div class="input-group">
                <span class="input-group-text">/blog/</span>
                <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>" id="postSlug">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Excerpt / Summary</label>
              <textarea name="excerpt" class="form-control" rows="3"><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Body Content (HTML)</label>
              <textarea name="body" id="postBody" class="form-control" rows="20" style="font-family:monospace;font-size:13px;"><?= htmlspecialchars($_POST['body'] ?? '') ?></textarea>
              <small class="text-muted">You can use full HTML markup for rich formatting.</small>
            </div>
          </div>
        </div>

        <!-- SEO -->
        <div class="panel">
          <div class="panel-header"><div><h2>SEO Settings</h2></div></div>
          <div class="p-4">
            <div class="mb-3">
              <label class="form-label fw-semibold">SEO Title</label>
              <input type="text" name="seo_title" class="form-control" value="<?= htmlspecialchars($_POST['seo_title'] ?? '') ?>" placeholder="Leave blank to use post title">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Meta Description</label>
              <textarea name="meta_description" class="form-control" rows="2" placeholder="Max 160 characters..."><?= htmlspecialchars($_POST['meta_description'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <!-- Publish -->
        <div class="panel mb-4">
          <div class="panel-header"><div><h2>Publish</h2></div></div>
          <div class="p-4">
            <div class="mb-3">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i> Save Post</button>
          </div>
        </div>

        <!-- Meta -->
        <div class="panel mb-4">
          <div class="panel-header"><div><h2>Post Details</h2></div></div>
          <div class="p-4">
            <div class="mb-3">
              <label class="form-label fw-semibold">Author</label>
              <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($_POST['author'] ?? 'Filao Adventures') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Category</label>
              <select name="category" class="form-select">
                <option value="">Select Category</option>
                <option value="Wildlife">Wildlife</option>
                <option value="Travel Tips">Travel Tips</option>
                <option value="Destinations">Destinations</option>
                <option value="Conservation">Conservation</option>
                <option value="Luxury Travel">Luxury Travel</option>
                <option value="Culture">Culture</option>
                <option value="News">News</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Tags (comma-separated)</label>
              <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($_POST['tags'] ?? '') ?>" placeholder="safari, kenya, wildlife">
            </div>
          </div>
        </div>

        <!-- Featured Image -->
        <div class="panel">
          <div class="panel-header"><div><h2>Featured Image</h2></div></div>
          <div class="p-4">
            <input type="file" name="featured_image" class="form-control" accept="image/*">
            <small class="text-muted mt-1 d-block">Upload a high-quality landscape image (1200x800px recommended).</small>
          </div>
        </div>
      </div>
    </div>
  </form>

</div>
</div>
<?php include 'partials/footer.php'; ?>
</div>
<script>
document.getElementById('postTitle').addEventListener('input', function() {
    const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    document.getElementById('postSlug').value = slug;
});
</script>
<?php include 'partials/scripts.php'; ?>
</body></html>
