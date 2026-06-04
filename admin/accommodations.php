<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();
$accommodations = $pdo->query("SELECT * FROM accommodations ORDER BY name")->fetchAll();

$pageTitle = 'Accommodations';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    <div class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4">
            
            <div class="page-heading">
                <div class="page-heading-copy">
                    <div class="page-icon"><i class="bi bi-house-door"></i></div>
                    <div>
                        <span class="eyebrow">Catalog</span>
                        <h1>Accommodations</h1>
                    </div>
                </div>
                <div class="heading-actions">
                    <a href="create-accommodation.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Accommodation</a>
                </div>
            </div>

            <div class="panel">
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead>
                            <tr>
                                <th>Accommodation Name</th>
                                <th>Added Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($accommodations)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No accommodations found.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($accommodations as $acc): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if($acc['featured_image']): ?>
                                                <img src="../uploads/<?= sanitize($acc['featured_image']) ?>" class="rounded" style="width:48px;height:48px;object-fit:cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width:48px;height:48px;">
                                                    <i class="bi bi-image"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= sanitize($acc['name']) ?></strong><br>
                                                <small class="text-muted">/<?= sanitize($acc['slug']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($acc['created_at'])) ?></td>
                                    <td class="text-end action-cell">
                                        <a href="edit-accommodation.php?id=<?= $acc['id'] ?>" class="btn btn-light btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteRecord('accommodations', <?= $acc['id'] ?>)" title="Delete"><i class="bi bi-trash"></i></button>
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
        text: "You won't be able to revert this! If a tour step uses this, it might cause issues.",
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
                if(data.success) {
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
