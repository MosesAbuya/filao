<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();
$facilities = $pdo->query("SELECT id, name FROM taxonomies WHERE type = 'facility' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect('create-accommodation.php');
    }

    try {
        $pdo->beginTransaction();
        $name = trim($_POST['name']);
        $slug = uniqueSlug('accommodations', generateSlug($name));
        
        $featuredImage = null;
        if (!empty($_FILES['featured_image']['name'])) {
            $uploaded = handleImageUpload('featured_image', 'accommodations');
            if ($uploaded) $featuredImage = $uploaded;
        }

        $stmt = $pdo->prepare("INSERT INTO accommodations (name, slug, description, featured_image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $_POST['description'], $featuredImage]);
        $accId = $pdo->lastInsertId();

        if (!empty($_POST['facilities']) && is_array($_POST['facilities'])) {
            $facStmt = $pdo->prepare("INSERT INTO accommodation_facilities (accommodation_id, taxonomy_id) VALUES (?, ?)");
            foreach ($_POST['facilities'] as $facId) {
                $facStmt->execute([$accId, (int)$facId]);
            }
        }

        $pdo->commit();
        setFlash('success', 'Accommodation created.');
        redirect('accommodations.php');

    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('error', 'Error: ' . $e->getMessage());
    }
}

$pageTitle = 'Add Accommodation';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    <div class="dashboard-content">
        <form action="create-accommodation.php" method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="container-fluid px-3 px-lg-4">
                <div class="page-heading">
                    <div class="page-heading-copy">
                        <div class="page-icon"><i class="bi bi-house-door"></i></div>
                        <div>
                            <span class="eyebrow">Catalog</span>
                            <h1>Add Accommodation</h1>
                        </div>
                    </div>
                    <div class="heading-actions">
                        <a href="accommodations.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Accommodation</button>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="panel mb-4">
                            <div class="mb-3">
                                <label class="form-label">Accommodation Name</label>
                                <input type="text" class="form-control" name="name" required placeholder="e.g. Mara Serena Safari Lodge">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control editor-simple" name="description" rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="panel mb-4">
                            <div class="panel-header mb-3"><h2>Featured Image</h2></div>
                            <input class="form-control" type="file" name="featured_image" accept="image/*">
                        </div>
                        <div class="panel">
                            <div class="panel-header mb-3"><h2>Facilities</h2></div>
                            <div class="category-chip-grid">
                                <?php foreach($facilities as $fac): ?>
                                    <div class="category-chip">
                                        <input type="checkbox" name="facilities[]" id="fac_<?= $fac['id'] ?>" value="<?= $fac['id'] ?>">
                                        <label for="fac_<?= $fac['id'] ?>"><?= sanitize($fac['name']) ?></label>
                                    </div>
                                <?php endforeach; ?>
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
