<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();

$pdo->exec("CREATE TABLE IF NOT EXISTS newsletters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$subscribers = $pdo->query("SELECT * FROM newsletters ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Newsletter Subscribers';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    
    <div class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4">
            
            <div class="page-heading">
                <div class="page-heading-copy">
                    <div class="page-icon"><i class="bi bi-envelope-paper"></i></div>
                    <div>
                        <span class="eyebrow">Marketing</span>
                        <h1>Newsletter Subscribers</h1>
                    </div>
                </div>
                <div class="heading-actions">
                    <button class="btn btn-primary" onclick="exportToPDF()"><i class="bi bi-file-earmark-pdf me-2"></i> Export to PDF</button>
                </div>
            </div>

            <div class="panel">
                <div class="table-responsive">
                    <table class="table align-middle table-hover" id="subscribersTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Email</th>
                                <th>Subscribed At</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($subscribers)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No subscribers found.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($subscribers as $index => $sub): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= sanitize($sub['email']) ?></strong>
                                    </td>
                                    <td>
                                        <?= date('M j, Y g:i A', strtotime($sub['created_at'])) ?>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteRecord('newsletters', <?= $sub['id'] ?>)" title="Delete"><i class="bi bi-trash"></i></button>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script>
    function exportToPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        doc.text("Newsletter Subscribers", 14, 15);
        
        const tableData = [];
        document.querySelectorAll('#subscribersTable tbody tr').forEach(row => {
            const cols = row.querySelectorAll('td');
            if (cols.length > 1) { // Skip "No subscribers found" row
                tableData.push([
                    cols[0].innerText,
                    cols[1].innerText,
                    cols[2].innerText
                ]);
            }
        });
        
        doc.autoTable({
            head: [['#', 'Email', 'Subscribed At']],
            body: tableData,
            startY: 20,
        });
        
        doc.save('newsletter-subscribers.pdf');
    }

    function deleteRecord(table, id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This email will be removed from your newsletter list.",
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
