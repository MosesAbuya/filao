<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!$slug) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }

    if (!$name || !$slug || !$category) {
        $error = "Name, Slug, and Category are required.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM activities WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $error = "An activity with this slug already exists.";
        } else {
            $featuredImage = null;
            if (!empty($_FILES['featured_image']['name'])) {
                $uploaded = handleImageUpload('featured_image', 'activities');
                if ($uploaded) $featuredImage = $uploaded;
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO activities (name, slug, category, description, featured_image) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $name, $slug, $category, $description, $featuredImage
                ]);
                header('Location: activities.php?msg=created');
                exit;
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Add Activity';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    
    <div class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4">
            
            <div class="page-heading">
                <div class="page-heading-copy">
                    <a href="activities.php" class="text-muted text-decoration-none mb-2 d-inline-block"><i class="bi bi-arrow-left me-1"></i>Back to Activities</a>
                    <h1>Add Activity</h1>
                </div>
            </div>

            <?php if($error): ?>
                <div class="alert alert-danger"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="panel">
                            <div class="panel-header"><h2>Basic Info</h2></div>
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" required value="<?= sanitize($_POST['name'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug <span class="text-muted">(auto-generated if blank)</span></label>
                                <input type="text" name="slug" id="slug" class="form-control" value="<?= sanitize($_POST['slug'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <input type="text" name="category" class="form-control" placeholder="e.g. Wildlife, Beach, Cultural..." required value="<?= sanitize($_POST['category'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="5"><?= sanitize($_POST['description'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="panel">
                            <div class="panel-header"><h2>Featured Image</h2></div>
                            <input class="form-control mb-3" type="file" name="featured_image" accept="image/*">
                            <p class="text-muted small">Recommended size: 1200x800px. Used for header banners and cards.</p>
                        </div>
                        
                        <div class="panel">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i> Save Activity</button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
    
    <?php include 'partials/footer.php'; ?>
</div>

<?php include 'partials/scripts.php'; ?>
<script>
document.getElementById('name').addEventListener('input', function(e) {
    if(!document.getElementById('slug').dataset.manuallyEdited) {
        let slug = e.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        document.getElementById('slug').value = slug;
    }
});
document.getElementById('slug').addEventListener('input', function() {
    this.dataset.manuallyEdited = true;
});
</script>
</body>
</html>
