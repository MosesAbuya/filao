<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect("create-region.php");
    }

    try {
        $name = trim($_POST['name']);
        
        $featuredImage = null;
        if (!empty($_FILES['featured_image']['name'])) {
            $uploaded = handleImageUpload('featured_image', 'regions');
            if ($uploaded) $featuredImage = $uploaded;
        }

        $stmt = $pdo->prepare("INSERT INTO regions (name, slug, featured_image) VALUES (?, ?, ?)");
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $stmt->execute([$name, $slug, $featuredImage]);

        setFlash('success', 'Region created.');
        redirect("regions.php");

    } catch (Exception $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
    }
}

$pageTitle = 'Add Region';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    <div class="dashboard-content">
        <form action="create-region.php" method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="container-fluid px-3 px-lg-4">
                <div class="page-heading">
                    <div class="page-heading-copy">
                        <div class="page-icon"><i class="bi bi-plus-lg"></i></div>
                        <div>
                            <span class="eyebrow">Catalog</span>
                            <h1>Add Region</h1>
                        </div>
                    </div>
                    <div class="heading-actions">
                        <a href="regions.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Region</button>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="panel mb-4">
                            <div class="panel-header">
                                <h3 class="panel-title">Basic Information</h3>
                            </div>
                            <div class="panel-body">
                                <div class="mb-3">
                                    <label class="form-label">Name *</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="panel mb-4">
                            <div class="panel-header">
                                <h3 class="panel-title">Media</h3>
                            </div>
                            <div class="panel-body">
                                <div class="mb-3">
                                    <label class="form-label">Featured Image</label>
                                    <input type="file" name="featured_image" class="form-control" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php include 'partials/footer.php'; ?>
</div>

<?php include 'partials/scripts.php'; ?>
</body>
</html>
