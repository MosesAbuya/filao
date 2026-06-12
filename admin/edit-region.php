<?php
require_once __DIR__ . '/auth_guard.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) redirect('regions.php');

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM regions WHERE id = ?");
$stmt->execute([$id]);
$region = $stmt->fetch();

if (!$region) redirect('regions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect("edit-region.php?id=$id");
    }

    try {
        $name = trim($_POST['name']);
        
        $featuredImage = $region['featured_image'];
        if (!empty($_FILES['featured_image']['name'])) {
            $uploaded = handleImageUpload('featured_image', 'regions');
            if ($uploaded) $featuredImage = $uploaded;
        }

        $stmt = $pdo->prepare("UPDATE regions SET name=?, slug=?, featured_image=? WHERE id=?");
        // Use the old slug if unchanged, otherwise generate a new one
        $slug = ($name === $region['name']) ? $region['slug'] : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $stmt->execute([$name, $slug, $featuredImage, $id]);

        setFlash('success', 'Region updated.');
        redirect("edit-region.php?id=$id");

    } catch (Exception $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
    }
}

$pageTitle = 'Edit Region';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    <div class="dashboard-content">
        <form action="edit-region.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="container-fluid px-3 px-lg-4">
                <div class="page-heading">
                    <div class="page-heading-copy">
                        <div class="page-icon"><i class="bi bi-pencil-square"></i></div>
                        <div>
                            <span class="eyebrow">Catalog</span>
                            <h1>Edit Region</h1>
                        </div>
                    </div>
                    <div class="heading-actions">
                        <a href="regions.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Region</button>
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
                                    <input type="text" name="name" class="form-control" value="<?= sanitize($region['name']) ?>" required>
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
                                    <?php if ($region['featured_image']): 
                                        $img = $region['featured_image'];
                                        if (str_starts_with($img, 'images/')) {
                                            $src = '../' . $img;
                                        } elseif (str_starts_with($img, 'destinations/') || str_starts_with($img, 'countries/') || str_starts_with($img, 'regions/')) {
                                            $src = '../uploads/' . $img;
                                        } else {
                                            $src = '../uploads/destinations/' . $img;
                                        }
                                    ?>
                                        <div class="mb-2">
                                            <img src="<?= sanitize($src) ?>" class="img-fluid rounded border">
                                        </div>
                                    <?php endif; ?>
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
