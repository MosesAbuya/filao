<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();

$allRegions = $pdo->query("SELECT name FROM regions ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$allCountries = $pdo->query("SELECT name FROM countries ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect('create-destination.php');
    }

    try {
        $pdo = getPDO();
        $name = trim($_POST['name']);
        $slug = uniqueSlug('destinations', generateSlug($name));
        
        $featuredImage = null;
        if (!empty($_FILES['featured_image']['name'])) {
            $uploaded = handleImageUpload('featured_image', 'destinations');
            if ($uploaded) $featuredImage = $uploaded;
        }

        $stmt = $pdo->prepare("INSERT INTO destinations (name, slug, region, country, region_type, description, latitude, longitude, featured_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $name, $slug, $_POST['region'], $_POST['country'], $_POST['region_type'], $_POST['description'], 
            (float)$_POST['latitude'], (float)$_POST['longitude'], $featuredImage
        ]);

        setFlash('success', 'Destination created.');
        redirect('destinations.php');

    } catch (Exception $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
    }
}

$pageTitle = 'Add Destination';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    <div class="dashboard-content">
        <form action="create-destination.php" method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="container-fluid px-3 px-lg-4">
                <div class="page-heading">
                    <div class="page-heading-copy">
                        <div class="page-icon"><i class="bi bi-geo-alt"></i></div>
                        <div>
                            <span class="eyebrow">Catalog</span>
                            <h1>Add Destination</h1>
                        </div>
                    </div>
                    <div class="heading-actions">
                        <a href="destinations.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Destination</button>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="panel mb-4">
                            <div class="mb-3">
                                <label class="form-label">Destination Name</label>
                                <input type="text" class="form-control" name="name" required placeholder="e.g. Maasai Mara">
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Region</label>
                                    <select class="form-select" name="region">
                                        <option value="">Select a region</option>
                                        <?php foreach ($allRegions as $rName): ?>
                                            <option value="<?= htmlspecialchars($rName) ?>"><?= htmlspecialchars($rName) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Country</label>
                                    <select class="form-select" name="country" required>
                                        <option value="">Select a country</option>
                                        <?php foreach ($allCountries as $cName): ?>
                                            <option value="<?= htmlspecialchars($cName) ?>"><?= htmlspecialchars($cName) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Region Type</label>
                                    <input type="text" class="form-control" name="region_type" placeholder="e.g. National Reserve" value="National Park">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control editor-simple" name="description" rows="4"></textarea>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="number" step="any" class="form-control" name="latitude" value="0.0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="number" step="any" class="form-control" name="longitude" value="0.0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="panel">
                            <div class="panel-header"><h2>Featured Image</h2></div>
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
