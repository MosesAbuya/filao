<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();

// Handle form submission for Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Security token invalid or expired.');
    } else {
        $id = (int)($_POST['tag_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $code = $_POST['code'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name) || empty($code)) {
            setFlash('danger', 'Name and Code are required.');
        } else {
            if ($id > 0) {
                // Update
                $stmt = $pdo->prepare("UPDATE head_tags SET name = ?, code = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $code, $is_active, $id]);
                setFlash('success', 'Tag updated successfully.');
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO head_tags (name, code, is_active) VALUES (?, ?, ?)");
                $stmt->execute([$name, $code, $is_active]);
                setFlash('success', 'Tag created successfully.');
            }
            header("Location: head-tags.php");
            exit;
        }
    }
}

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
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tagModal" onclick="openTagModal(0, '', '', 1)"><i class="bi bi-plus-lg me-1"></i> Add Tag</button>
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
                                                <?= csrfInput() ?>
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
                                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#tagModal"
                                                onclick="openTagModal(<?= $tag['id'] ?>, '<?= htmlspecialchars(addslashes($tag['name'])) ?>', `<?= htmlspecialchars($tag['code']) ?>`, <?= $tag['is_active'] ?>)"
                                                title="Edit"><i class="bi bi-pencil"></i></button>
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

<!-- Add/Edit Tag Modal -->
<div class="modal fade" id="tagModal" tabindex="-1" aria-labelledby="tagModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="head-tags.php" method="POST">
          <?= csrfInput() ?>
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="tag_id" id="modalTagId" value="0">
          
          <div class="modal-header">
            <h5 class="modal-title" id="tagModalLabel">Add Head Tag</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="mb-3">
                  <label for="modalTagName" class="form-label">Name</label>
                  <input type="text" class="form-control" id="modalTagName" name="name" required placeholder="e.g. Meta Pixel">
              </div>
              <div class="mb-3">
                  <label for="modalTagCode" class="form-label">Code Snippet (HTML/JS)</label>
                  <textarea class="form-control text-monospace" id="modalTagCode" name="code" rows="8" required placeholder="<script>...</script>"></textarea>
                  <div class="form-text">This code will be injected right before the closing &lt;/head&gt; tag on all public pages.</div>
              </div>
              <div class="mb-3 form-check">
                  <input type="checkbox" class="form-check-input" id="modalTagActive" name="is_active" value="1" checked>
                  <label class="form-check-label" for="modalTagActive">Active</label>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Tag</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include 'partials/scripts.php'; ?>
<script>
    function openTagModal(id = 0, name = '', code = '', isActive = 1) {
        document.getElementById('modalTagId').value = id;
        document.getElementById('modalTagName').value = name;
        
        // Decode HTML entities for textarea
        const txt = document.createElement("textarea");
        txt.innerHTML = code;
        document.getElementById('modalTagCode').value = txt.value;
        
        document.getElementById('modalTagActive').checked = (isActive == 1);
        document.getElementById('tagModalLabel').innerText = id > 0 ? 'Edit Head Tag' : 'Add Head Tag';
    }

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
