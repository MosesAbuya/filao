<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();
$enquiries = $pdo->query("SELECT * FROM enquiries ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Inquiries';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    
    <div class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4">
            
            <div class="page-heading">
                <div class="page-heading-copy">
                    <div class="page-icon"><i class="bi bi-envelope"></i></div>
                    <div>
                        <span class="eyebrow">User Messages</span>
                        <h1>Inquiries</h1>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="table-responsive">
                    <table class="table align-middle table-hover" id="enquiriesTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Email / Phone</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($enquiries)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No inquiries found.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($enquiries as $enq): ?>
                                <tr>
                                    <td>
                                        <?= date('M j, Y g:i A', strtotime($enq['created_at'])) ?>
                                    </td>
                                    <td>
                                        <strong><?= sanitize($enq['first_name'] . ' ' . $enq['last_name']) ?></strong>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= sanitize($enq['email']) ?>"><?= sanitize($enq['email']) ?></a><br>
                                        <small class="text-muted"><?= sanitize($enq['phone'] ?: 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <?php if($enq['type'] == 'tour_enquiry'): ?>
                                            <span class="badge bg-primary text-white border border-primary">Tour Enquiry</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary text-white border border-secondary">Contact</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($enq['status'] == 'new'): ?>
                                            <span class="badge bg-success">New</span>
                                        <?php elseif($enq['status'] == 'in_progress'): ?>
                                            <span class="badge bg-warning text-dark">In Progress</span>
                                        <?php else: ?>
                                            <span class="badge bg-dark">Closed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end action-cell">
                                        <button class="btn btn-light btn-sm" onclick='viewEnquiry(<?= json_encode($enq, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)' title="View Details"><i class="bi bi-eye"></i> View</button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteRecord('enquiries', <?= $enq['id'] ?>)" title="Delete"><i class="bi bi-trash"></i></button>
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

<!-- View Enquiry Modal -->
<div class="modal fade" id="enquiryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Inquiry Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="enquiryModalBody">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include 'partials/scripts.php'; ?>
<script>
function viewEnquiry(enq) {
    let html = '<div class="row">';
    html += '<div class="col-md-6 mb-3"><strong>Name:</strong> ' + enq.first_name + ' ' + enq.last_name + '</div>';
    html += '<div class="col-md-6 mb-3"><strong>Email:</strong> <a href="mailto:' + enq.email + '">' + enq.email + '</a></div>';
    html += '<div class="col-md-6 mb-3"><strong>Phone:</strong> ' + (enq.phone || 'N/A') + '</div>';
    html += '<div class="col-md-6 mb-3"><strong>Date:</strong> ' + enq.created_at + '</div>';
    html += '<hr>';
    
    if(enq.type === 'tour_enquiry') {
        html += '<div class="col-md-12 mb-3"><strong>Tour Reference:</strong> ' + (enq.tour_title || 'Tour ID ' + enq.tour_id) + '</div>';
        html += '<div class="col-md-6 mb-3"><strong>Travel Date:</strong> ' + (enq.travel_month || 'N/A') + ' ' + (enq.travel_year || '') + '</div>';
        html += '<div class="col-md-6 mb-3"><strong>Travellers:</strong> Adults: ' + (enq.adults||0) + ', Children: ' + (enq.children||0) + '</div>';
    } else {
        if(enq.destination) html += '<div class="col-md-6 mb-3"><strong>Destination:</strong> ' + enq.destination + '</div>';
        if(enq.duration_days) html += '<div class="col-md-6 mb-3"><strong>Duration:</strong> ' + enq.duration_days + ' days</div>';
        if(enq.travel_month) html += '<div class="col-md-6 mb-3"><strong>Travel Date:</strong> ' + enq.travel_month + ' ' + (enq.travel_year||'') + '</div>';
        if(enq.adults) html += '<div class="col-md-6 mb-3"><strong>Travellers:</strong> Adults: ' + enq.adults + ', Children: ' + (enq.children||0) + '</div>';
        if(enq.budget_usd) html += '<div class="col-md-6 mb-3"><strong>Budget (per person):</strong> $' + enq.budget_usd + '</div>';
        if(enq.activities) html += '<div class="col-md-12 mb-3"><strong>Activities:</strong> ' + enq.activities + '</div>';
    }
    
    html += '<hr><div class="col-12 mt-2"><strong>Message:</strong><p class="mt-2 p-3 bg-light border rounded">' + (enq.message ? enq.message.replace(/\n/g, '<br>') : '<em>No message provided.</em>') + '</p></div>';
    html += '</div>';

    document.getElementById('enquiryModalBody').innerHTML = html;
    var myModal = new bootstrap.Modal(document.getElementById('enquiryModal'));
    myModal.show();
    
    // Auto-update status to in_progress if it was new
    if(enq.status === 'new') {
        fetch('ajax/update-record.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `table=enquiries&id=${enq.id}&field=status&value=in_progress&csrf_token=<?= csrfGenerate() ?>`
        }).then(res => res.json()).then(data => {
            if(data.success) {
                // silently updated status
                setTimeout(() => location.reload(), 2000);
            }
        });
    }
}

function deleteRecord(table, id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This inquiry will be permanently deleted.",
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
