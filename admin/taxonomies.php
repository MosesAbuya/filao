<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect('taxonomies.php');
    }

    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $name = trim($_POST['name']);
        $type = $_POST['type'];
        $slug = uniqueSlug('taxonomies', generateSlug($name));
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $uploaded = handleImageUpload('image', 'taxonomies');
            if ($uploaded) $imagePath = $uploaded;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO taxonomies (type, name, slug, image) VALUES (?, ?, ?, ?)");
            $stmt->execute([$type, $name, $slug, $imagePath]);
            setFlash('success', 'Taxonomy term added.');
        } catch (Exception $e) {
            setFlash('error', 'Error adding term: ' . $e->getMessage());
        }
        redirect('taxonomies.php');
    }
}

$types = ['activity', 'feature', 'age', 'language', 'facility'];

$taxonomies = [];
foreach ($types as $type) {
    $stmt = $pdo->prepare("SELECT * FROM taxonomies WHERE type = ? ORDER BY name");
    $stmt->execute([$type]);
    $taxonomies[$type] = $stmt->fetchAll();
}

$pageTitle = 'Taxonomies';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    <div class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4">
            <div class="page-heading">
                <div class="page-heading-copy">
                    <div class="page-icon"><i class="bi bi-tags"></i></div>
                    <div>
                        <span class="eyebrow">Catalog</span>
                        <h1>Taxonomies</h1>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="panel">
                        <div class="panel-header mb-3"><h2>Add New Term</h2></div>
                        <form action="taxonomies.php" method="POST" enctype="multipart/form-data">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="create">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" required placeholder="e.g. Wi-Fi or Birdwatching">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="type" required>
                                    <option value="activity">Activity (Tours)</option>
                                    <option value="feature">Feature (Tours)</option>
                                    <option value="facility">Facility (Accommodations)</option>
                                    <option value="age">Age Group</option>
                                    <option value="language">Language</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Image (Optional)</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Add Term</button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="panel">
                        <div class="panel-header mb-3"><h2>Existing Terms</h2></div>
                        
                        <ul class="nav nav-pills mb-3" id="taxTabs" role="tablist">
                            <?php foreach($types as $idx => $type): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= $idx===0?'active':'' ?>" data-bs-toggle="tab" data-bs-target="#tab-<?= $type ?>" type="button"><?= ucfirst($type) ?></button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="tab-content">
                            <?php foreach($types as $idx => $type): ?>
                                <div class="tab-pane fade <?= $idx===0?'show active':'' ?>" id="tab-<?= $type ?>" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead><tr><th>Term</th><th>Slug</th><th class="text-end">Actions</th></tr></thead>
                                            <tbody>
                                                <?php if(empty($taxonomies[$type])): ?>
                                                    <tr><td colspan="3" class="text-muted text-center py-3">No terms found.</td></tr>
                                                <?php else: ?>
                                                    <?php foreach($taxonomies[$type] as $term): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <?php if($term['image']): ?>
                                                                        <img src="../uploads/<?= sanitize($term['image']) ?>" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">
                                                                    <?php endif; ?>
                                                                    <?= sanitize($term['name']) ?>
                                                                </div>
                                                            </td>
                                                            <td class="text-muted"><?= sanitize($term['slug']) ?></td>
                                                            <td class="text-end">
                                                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteRecord('taxonomies', <?= $term['id'] ?>)"><i class="bi bi-trash"></i></button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
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
                if(data.success) { window.location.reload(); }
                else { Swal.fire('Error', data.message, 'error'); }
            });
        }
    })
}
</script>
</body>
</html>
