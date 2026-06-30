<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    setFlash('danger', 'Invalid Tag ID.');
    redirect("head-tags.php");
}

$stmt = $pdo->prepare("SELECT * FROM head_tags WHERE id = ?");
$stmt->execute([$id]);
$tag = $stmt->fetch();

if (!$tag) {
    setFlash('danger', 'Tag not found.');
    redirect("head-tags.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        redirect("edit-head-tag.php?id=$id");
    }

    try {
        $name = trim($_POST['name'] ?? '');
        $code = $_POST['code'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name) || empty($code)) {
            setFlash('danger', 'Name and Code are required.');
            redirect("edit-head-tag.php?id=$id");
        }

        $stmt = $pdo->prepare("UPDATE head_tags SET name = ?, code = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$name, $code, $is_active, $id]);

        setFlash('success', 'Tag updated successfully.');
        redirect("head-tags.php");

    } catch (Exception $e) {
        setFlash('danger', 'Error: ' . $e->getMessage());
    }
}

$pageTitle = 'Edit Head Tag';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    <div class="dashboard-content">
        <form action="edit-head-tag.php?id=<?= $id ?>" method="POST">
            <?= csrfField() ?>
            <div class="container-fluid px-3 px-lg-4">
                <div class="page-heading">
                    <div class="page-heading-copy">
                        <div class="page-icon"><i class="bi bi-pencil"></i></div>
                        <div>
                            <span class="eyebrow">Settings</span>
                            <h1>Edit Head Tag</h1>
                        </div>
                    </div>
                    <div class="heading-actions">
                        <a href="head-tags.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Tag</button>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="panel mb-4">
                            <div class="panel-header">
                                <h3 class="panel-title">Tag Details</h3>
                            </div>
                            <div class="panel-body">
                                <div class="mb-3">
                                    <label class="form-label">Name *</label>
                                    <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($tag['name']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Code Snippet (HTML/JS) *</label>
                                    <textarea class="form-control text-monospace" name="code" rows="12" required><?= htmlspecialchars($tag['code']) ?></textarea>
                                    <div class="form-text">This code will be injected right before the closing &lt;/head&gt; tag on all public pages.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="panel mb-4">
                            <div class="panel-header">
                                <h3 class="panel-title">Status</h3>
                            </div>
                            <div class="panel-body">
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" <?= $tag['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label ms-2" for="isActive">Active</label>
                                </div>
                                <div class="form-text mt-2">If inactive, the tag will not be injected into the site.</div>
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
