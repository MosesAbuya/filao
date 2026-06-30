<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();

// Handle toggle status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle') {
     if (csrfVerify($_POST['csrf_token'] ?? '')) {
         $id = (int)$_POST['tag_id'];
         $status = (int)$_POST['status'];
         $stmt = $pdo->prepare("UPDATE head_tags SET is_active = ? WHERE id = ?");
         $stmt->execute([$status, $id]);
         setFlash('success', 'Tag status updated.');
         header("Location: head-tags.php");
         exit;
     }
}

$tags = $pdo->query("SELECT * FROM head_tags ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Dynamic Head Tags';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>

    <div class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4">

            <div class="page-heading">
                <div class="page-heading-copy">
                    <div class="page-icon"><i class="bi bi-code-square"></i></div>
                    <div>
                        <span class="eyebrow">Settings</span>
                        <h1>Dynamic Head Tags</h1>
                    </div>
                </div>
                <div class="heading-actions">
                    <a href="create-head-tag.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Tag</a>
                </div>
            </div>

            <div class="panel">
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tags)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No tags found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tags as $tag): ?>
                                    <tr>
                                        <td><strong><?= sanitize($tag['name']) ?></strong></td>
                                        <td>
                                            <form method="POST" class="d-inline" action="head-tags.php">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="tag_id" value="<?= $tag['id'] ?>">
                                                <input type="hidden" name="status" value="<?= $tag['is_active'] ? '0' : '1' ?>">
                                                <?php if ($tag['is_active']): ?>
                                                    <button type="submit" class="btn btn-sm btn-success" title="Click to disable">Active</button>
                                                <?php else: ?>
                                                    <button type="submit" class="btn btn-sm btn-secondary" title="Click to enable">Inactive</button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                        <td><?= date('M j, Y', strtotime($tag['created_at'])) ?></td>
                                        <td class="text-end action-cell">
                                            <a href="edit-head-tag.php?id=<?= $tag['id'] ?>" class="btn btn-light btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="deleteRecord('head_tags', <?= $tag['id'] ?>)" title="Delete"><i
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
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('table', table);
                formData.append('id', id);
                formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');

                fetch('ajax/delete-record.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            Swal.fire('Error!', data.message || 'Something went wrong.', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error!', 'Network error occurred.', 'error');
                    });
            }
        });
    }
</script>
</body>
</html>
