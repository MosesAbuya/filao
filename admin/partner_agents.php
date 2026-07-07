<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();
// Create table just in case they visit before any submissions
$pdo->exec("CREATE TABLE IF NOT EXISTS partner_agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    street_address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) DEFAULT NULL,
    country VARCHAR(100) NOT NULL,
    company_reg_number VARCHAR(100) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    
    agent_name VARCHAR(255) NOT NULL,
    agent_phone VARCHAR(60) NOT NULL,
    agent_email VARCHAR(255) NOT NULL,
    agent_mobile VARCHAR(60) DEFAULT NULL,
    
    emergency_name VARCHAR(255) NOT NULL,
    emergency_phone VARCHAR(60) NOT NULL,
    emergency_email VARCHAR(255) NOT NULL,
    
    agent_type ENUM('RETAIL', 'WHOLESALE') NOT NULL,
    product_updates ENUM('YES', 'NO') NOT NULL DEFAULT 'NO',
    updates_email VARCHAR(255) DEFAULT NULL,
    
    status ENUM('new', 'reviewed', 'approved', 'rejected') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$agents = $pdo->query("SELECT * FROM partner_agents ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Agents';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    
    <div class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4">
            
            <div class="page-heading">
                <div class="page-heading-copy">
                    <div class="page-icon"><i class="bi bi-briefcase"></i></div>
                    <div>
                        <span class="eyebrow">Partnerships</span>
                        <h1>Agents</h1>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="table-responsive">
                    <table class="table align-middle table-hover" id="agentsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Company</th>
                                <th>Agent Contact</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($agents)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No agent applications found.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($agents as $agent): ?>
                                <tr>
                                    <td>
                                        <?= date('M j, Y', strtotime($agent['created_at'])) ?>
                                    </td>
                                    <td>
                                        <strong><?= sanitize($agent['company_name']) ?></strong><br>
                                        <small class="text-muted"><?= sanitize($agent['country']) ?></small>
                                    </td>
                                    <td>
                                        <?= sanitize($agent['agent_name']) ?><br>
                                        <a href="mailto:<?= sanitize($agent['agent_email']) ?>"><?= sanitize($agent['agent_email']) ?></a>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary text-white border border-secondary"><?= sanitize($agent['agent_type']) ?></span>
                                    </td>
                                    <td>
                                        <?php if($agent['status'] == 'new'): ?>
                                            <span class="badge bg-success">New</span>
                                        <?php elseif($agent['status'] == 'reviewed'): ?>
                                            <span class="badge bg-warning text-dark">Reviewed</span>
                                        <?php elseif($agent['status'] == 'approved'): ?>
                                            <span class="badge bg-primary">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end action-cell">
                                        <button class="btn btn-light btn-sm" onclick='viewAgent(<?= json_encode($agent, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)' title="View Details"><i class="bi bi-eye"></i> View</button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteRecord('partner_agents', <?= $agent['id'] ?>)" title="Delete"><i class="bi bi-trash"></i></button>
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

<!-- View Agent Modal -->
<div class="modal fade" id="agentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Agent Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="agentModalBody">
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <div>
            <select class="form-select form-select-sm d-inline-block w-auto" id="statusSelect">
                <option value="new">New</option>
                <option value="reviewed">Reviewed</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <button class="btn btn-primary btn-sm ms-2" onclick="updateAgentStatus()">Update Status</button>
        </div>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include 'partials/scripts.php'; ?>
<script>
let currentAgentId = null;

function viewAgent(agent) {
    currentAgentId = agent.id;
    document.getElementById('statusSelect').value = agent.status;
    
    let html = '<div class="row">';
    
    html += '<div class="col-12"><h6 class="text-muted text-uppercase mb-3 mt-2 border-bottom pb-2">Company Information</h6></div>';
    html += '<div class="col-md-6 mb-3"><strong>Company Name:</strong> ' + agent.company_name + '</div>';
    html += '<div class="col-md-6 mb-3"><strong>Registration No:</strong> ' + (agent.company_reg_number || 'N/A') + '</div>';
    html += '<div class="col-md-6 mb-3"><strong>Address:</strong> ' + agent.street_address + '<br>' + agent.city + (agent.state ? ', ' + agent.state : '') + '<br>' + agent.country + '</div>';
    html += '<div class="col-md-6 mb-3"><strong>Website:</strong> ' + (agent.website ? '<a href="'+agent.website+'" target="_blank">'+agent.website+'</a>' : 'N/A') + '</div>';
    
    html += '<div class="col-12"><h6 class="text-muted text-uppercase mb-3 mt-4 border-bottom pb-2">Booking Agent Details</h6></div>';
    html += '<div class="col-md-6 mb-3"><strong>Agent Name:</strong> ' + agent.agent_name + '</div>';
    html += '<div class="col-md-6 mb-3"><strong>Email:</strong> <a href="mailto:' + agent.agent_email + '">' + agent.agent_email + '</a></div>';
    html += '<div class="col-md-6 mb-3"><strong>Phone:</strong> ' + agent.agent_phone + '</div>';
    html += '<div class="col-md-6 mb-3"><strong>Mobile:</strong> ' + (agent.agent_mobile || 'N/A') + '</div>';
    
    html += '<div class="col-12"><h6 class="text-muted text-uppercase mb-3 mt-4 border-bottom pb-2">24hrs Emergency Contact</h6></div>';
    html += '<div class="col-md-6 mb-3"><strong>Name:</strong> ' + agent.emergency_name + '</div>';
    html += '<div class="col-md-6 mb-3"><strong>Email:</strong> <a href="mailto:' + agent.emergency_email + '">' + agent.emergency_email + '</a></div>';
    html += '<div class="col-md-6 mb-3"><strong>Phone:</strong> ' + agent.emergency_phone + '</div>';
    
    html += '<div class="col-12"><h6 class="text-muted text-uppercase mb-3 mt-4 border-bottom pb-2">Business Type & Preferences</h6></div>';
    html += '<div class="col-md-6 mb-3"><strong>Type:</strong> ' + agent.agent_type + '</div>';
    html += '<div class="col-md-6 mb-3"><strong>Consortium:</strong> ' + (agent.consortium || 'None') + '</div>';
    html += '<div class="col-md-6 mb-3"><strong>Product Updates:</strong> ' + agent.product_updates + (agent.updates_email ? ' (' + agent.updates_email + ')' : '') + '</div>';
    html += '<div class="col-md-6 mb-3"><strong>Webconnect Rates:</strong> ' + agent.webconnect_rates + '</div>';
    html += '<div class="col-md-12 mb-3"><strong>Application Date:</strong> ' + agent.created_at + '</div>';
    
    html += '</div>';

    document.getElementById('agentModalBody').innerHTML = html;
    var myModal = new bootstrap.Modal(document.getElementById('agentModal'));
    myModal.show();
    
    if(agent.status === 'new') {
        fetch('ajax/update-record.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `table=partner_agents&id=${agent.id}&field=status&value=reviewed&csrf_token=<?= csrfGenerate() ?>`
        }).then(res => res.json()).then(data => {
            if(data.success) {
                document.getElementById('statusSelect').value = 'reviewed';
                setTimeout(() => location.reload(), 2000);
            }
        });
    }
}

function updateAgentStatus() {
    if(!currentAgentId) return;
    const newStatus = document.getElementById('statusSelect').value;
    
    fetch('ajax/update-record.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `table=partner_agents&id=${currentAgentId}&field=status&value=${newStatus}&csrf_token=<?= csrfGenerate() ?>`
    }).then(res => res.json()).then(data => {
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Status Updated',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to update status', 'error');
        }
    });
}

function deleteRecord(table, id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This application will be permanently deleted.",
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
