<?php
require_once __DIR__ . '/auth_guard.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) redirect('accommodations.php');

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM accommodations WHERE id = ?");
$stmt->execute([$id]);
$acc = $stmt->fetch();

if (!$acc) redirect('accommodations.php');

$facilities = $pdo->query("SELECT id, name FROM taxonomies WHERE type = 'facility' ORDER BY name")->fetchAll();

$facStmt = $pdo->prepare("SELECT taxonomy_id FROM accommodation_facilities WHERE accommodation_id = ?");
$facStmt->execute([$id]);
$selectedFac = $facStmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect("edit-accommodation.php?id=$id");
    }

    try {
        $pdo->beginTransaction();
        $name = trim($_POST['name']);
        
        $featuredImage = $acc['featured_image'];
        if (!empty($_FILES['featured_image']['name'])) {
            $uploaded = handleImageUpload('featured_image', 'accommodations');
            if ($uploaded) $featuredImage = $uploaded;
        }

        $stmt = $pdo->prepare("UPDATE accommodations SET name=?, description=?, featured_image=? WHERE id=?");
        $stmt->execute([$name, $_POST['description'], $featuredImage, $id]);

        $pdo->prepare("DELETE FROM accommodation_facilities WHERE accommodation_id = ?")->execute([$id]);
        if (!empty($_POST['facilities']) && is_array($_POST['facilities'])) {
            $facStmt2 = $pdo->prepare("INSERT INTO accommodation_facilities (accommodation_id, taxonomy_id) VALUES (?, ?)");
            foreach ($_POST['facilities'] as $facId) {
                $facStmt2->execute([$id, (int)$facId]);
            }
        }

        $pdo->commit();
        setFlash('success', 'Accommodation updated.');
        redirect("edit-accommodation.php?id=$id");

    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('error', 'Error: ' . $e->getMessage());
    }
}

$pageTitle = 'Edit Accommodation';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    <div class="dashboard-content">
        <form action="edit-accommodation.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="container-fluid px-3 px-lg-4">
                <div class="page-heading">
                    <div class="page-heading-copy">
                        <div class="page-icon"><i class="bi bi-pencil-square"></i></div>
                        <div>
                            <span class="eyebrow">Catalog</span>
                            <h1>Edit Accommodation</h1>
                        </div>
                    </div>
                    <div class="heading-actions">
                        <a href="accommodations.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Accommodation</button>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="panel mb-4">
                            <div class="mb-3">
                                <label class="form-label">Accommodation Name</label>
                                <input type="text" class="form-control" name="name" required value="<?= sanitize($acc['name']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control editor-simple" name="description" rows="5"><?= sanitize($acc['description']) ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="panel mb-4">
                            <div class="panel-header mb-3"><h2>Featured Image</h2></div>
                            <?php if($acc['featured_image']): ?>
                                <div class="mb-3">
                                    <img src="../uploads/<?= sanitize($acc['featured_image']) ?>" class="img-fluid rounded">
                                </div>
                            <?php endif; ?>
                            <input class="form-control" type="file" name="featured_image" accept="image/*">
                        </div>
                        <div class="panel">
                            <div class="panel-header mb-3"><h2>Facilities</h2></div>
                            <div class="category-chip-grid">
                                <?php foreach($facilities as $fac): ?>
                                    <div class="category-chip">
                                        <input type="checkbox" name="facilities[]" id="fac_<?= $fac['id'] ?>" value="<?= $fac['id'] ?>" <?= in_array($fac['id'], $selectedFac) ? 'checked' : '' ?>>
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
