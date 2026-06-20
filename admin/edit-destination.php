<?php
require_once __DIR__ . '/auth_guard.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) redirect('destinations.php');

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->execute([$id]);
$dest = $stmt->fetch();

$allRegions = $pdo->query("SELECT name FROM regions ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$allCountries = $pdo->query("SELECT name FROM countries ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

if (!$dest) redirect('destinations.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect("edit-destination.php?id=$id");
    }

    try {
        $name = trim($_POST['name']);
        
        $featuredImage = $dest['featured_image'];
        if (!empty($_FILES['featured_image']['name'])) {
            $uploaded = handleImageUpload('featured_image', 'destinations');
            if ($uploaded) $featuredImage = $uploaded;
        }

        $stmt = $pdo->prepare("UPDATE destinations SET name=?, region=?, country=?, region_type=?, description=?, latitude=?, longitude=?, featured_image=? WHERE id=?");
        $stmt->execute([
            $name, $_POST['region'], $_POST['country'], $_POST['region_type'], $_POST['description'], 
            (float)$_POST['latitude'], (float)$_POST['longitude'], $featuredImage, $id
        ]);

        setFlash('success', 'Destination updated.');
        redirect("edit-destination.php?id=$id");

    } catch (Exception $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
    }
}

$pageTitle = 'Edit Destination';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    <div class="dashboard-content">
        <form action="edit-destination.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="container-fluid px-3 px-lg-4">
                <div class="page-heading">
                    <div class="page-heading-copy">
                        <div class="page-icon"><i class="bi bi-pencil-square"></i></div>
                        <div>
                            <span class="eyebrow">Catalog</span>
                            <h1>Edit Destination</h1>
                        </div>
                    </div>
                    <div class="heading-actions">
                        <a href="destinations.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Destination</button>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="panel mb-4">
                            <div class="mb-3">
                                <label class="form-label">Destination Name</label>
                                <input type="text" class="form-control" name="name" required value="<?= sanitize($dest['name']) ?>">
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Region</label>
                                    <select class="form-select" name="region">
                                        <option value="">Select a region</option>
                                        <?php foreach ($allRegions as $rName): ?>
                                            <option value="<?= htmlspecialchars($rName) ?>" <?= ($dest['region'] == $rName) ? 'selected' : '' ?>><?= htmlspecialchars($rName) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Country</label>
                                    <select class="form-select" name="country" required>
                                        <option value="">Select a country</option>
                                        <?php foreach ($allCountries as $cName): ?>
                                            <option value="<?= htmlspecialchars($cName) ?>" <?= ($dest['country'] == $cName) ? 'selected' : '' ?>><?= htmlspecialchars($cName) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Region Type</label>
                                    <input type="text" class="form-control" name="region_type" value="<?= sanitize($dest['region_type']) ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control editor-simple" name="description" rows="4"><?= sanitize($dest['description']) ?></textarea>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="number" step="any" class="form-control" name="latitude" value="<?= $dest['latitude'] ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="number" step="any" class="form-control" name="longitude" value="<?= $dest['longitude'] ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="panel">
                            <div class="panel-header"><h2>Featured Image</h2></div>
                            <?php if($dest['featured_image']): ?>
                                <div class="mb-3">
                                    <img src="../uploads/<?= sanitize($dest['featured_image']) ?>" class="img-fluid rounded">
                                </div>
                            <?php endif; ?>
                            <input class="form-control" type="file" name="featured_image" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php include 'partials/footer.php'; ?>
</div>

<?php include 'partials/scripts.php'; ?>
<script>
    
    $(document).ready(function() {
        $('.editor-simple').summernote({
            height: 250,
            toolbar: [
                ['font', ['bold', 'italic', 'clear']],
                ['para', ['ul', 'ol']],
                ['insert', ['link']],
                ['view', ['codeview']]
            ]
        });
    });

</script>
</body>
</html>
