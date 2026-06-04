<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();

$stmt = $pdo->query("SELECT id, title, slug, duration_days, price_from_usd, status, created_at FROM tours ORDER BY created_at DESC");
$tours = $stmt->fetchAll();

$pageTitle = 'Tours & Safaris';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    
    <div class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4">
            
            <div class="page-heading">
                <div class="page-heading-copy">
                    <div class="page-icon">
                        <i class="bi bi-map"></i>
                    </div>
                    <div>
                        <span class="eyebrow">Catalog</span>
                        <h1>Tours & Safaris</h1>
                    </div>
                </div>
                <div class="heading-actions">
                    <a href="create-tour.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Create Tour
                    </a>
                </div>
            </div>

            <div class="panel">
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead>
                            <tr>
                                <th>Tour Title</th>
                                <th>Duration</th>
                                <th>Starting Price</th>
                                <th>Status</th>
                                <th>Date Added</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tours)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No tours found. Create one to get started.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($tours as $tour): ?>
                                <tr>
                                    <td>
                                        <strong><?= sanitize($tour['title']) ?></strong><br>
                                        <small class="text-muted">/<?= sanitize($tour['slug']) ?></small>
                                    </td>
                                    <td><?= $tour['duration_days'] ?> Days</td>
                                    <td><?= formatPrice($tour['price_from_usd']) ?></td>
                                    <td>
                                        <?php if($tour['status'] === 'published'): ?>
                                            <span class="badge bg-success">Published</span>
                                        <?php elseif($tour['status'] === 'draft'): ?>
                                            <span class="badge bg-warning text-dark">Draft</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Archived</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($tour['created_at'])) ?></td>
                                    <td class="text-end action-cell">
                                        <a href="../tour.php?slug=<?= $tour['slug'] ?>" target="_blank" class="btn btn-light btn-sm" title="View"><i class="bi bi-eye"></i></a>
                                        <a href="edit-tour.php?id=<?= $tour['id'] ?>" class="btn btn-light btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteRecord('tours', <?= $tour['id'] ?>)" title="Delete"><i class="bi bi-trash"></i></button>
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
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#C0352B',
        cancelButtonColor: '#6B6358',
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
                    Swal.fire('Error', data.message || 'Could not delete record.', 'error');
                }
            })
            .catch(err => Swal.fire('Error', 'Server error occurred.', 'error'));
        }
    })
}
</script>
</body>
</html>
