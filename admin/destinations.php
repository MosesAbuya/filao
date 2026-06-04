<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();
$destinations = $pdo->query("SELECT * FROM destinations ORDER BY name")->fetchAll();

$pageTitle = 'Destinations';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>

    <div class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4">

            <div class="page-heading">
                <div class="page-heading-copy">
                    <div class="page-icon"><i class="bi bi-geo-alt"></i></div>
                    <div>
                        <span class="eyebrow">Catalog</span>
                        <h1>Destinations</h1>
                    </div>
                </div>
                <div class="heading-actions">
                    <a href="create-destination.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add
                        Destination</a>
                </div>
            </div>

            <div class="panel">
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Region</th>
                                <th>Country</th>
                                <th>Type</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($destinations)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No destinations found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($destinations as $dest): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <?php if ($dest['featured_image']): ?>
                                                    <img src="../uploads/<?= sanitize($dest['featured_image']) ?>" class="rounded"
                                                        style="width:48px;height:48px;object-fit:cover;">
                                                <?php else: ?>
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted"
                                                        style="width:48px;height:48px;">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?= sanitize($dest['name']) ?></strong><br>
                                                    <small class="text-muted">/<?= sanitize($dest['slug']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= sanitize($dest['region'] ?? ' ') ?></td>
                                        <td><?= sanitize($dest['country']) ?></td>
                                        <td><span
                                                class="badge bg-light text-dark border"><?= sanitize($dest['region_type']) ?></span>
                                        </td>
                                        <td class="text-end action-cell">
                                            <a href="edit-destination.php?id=<?= $dest['id'] ?>" class="btn btn-light btn-sm"
                                                title="Edit"><i class="bi bi-pencil"></i></a>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="deleteRecord('destinations', <?= $dest['id'] ?>)" title="Delete"><i
                                                    class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
            text: "If a tour uses this destination, it might cause issues.",
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
                        if (data.success) {
                            window.location.reload();
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
            }
        })
    }
</script>
</body>

</html>