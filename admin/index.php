<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();

// Get basic stats
$tourCount = $pdo->query("SELECT COUNT(*) FROM tours")->fetchColumn();
$destCount = $pdo->query("SELECT COUNT(*) FROM destinations")->fetchColumn();
$accomCount = $pdo->query("SELECT COUNT(*) FROM accommodations")->fetchColumn();
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Get recent tours
$recentTours = $pdo->query("SELECT t.id, t.title, t.status, t.created_at
                           FROM tours t 
                           ORDER BY t.created_at DESC LIMIT 5")->fetchAll();

$pageTitle = 'Dashboard';
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
                        <i class="bi bi-grid-1x2"></i>
                    </div>
                    <div>
                        <span class="eyebrow">Overview</span>
                        <h1>Dashboard</h1>
                    </div>
                </div>
                <div class="heading-actions">
                    <a href="create-tour.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> New Tour
                    </a>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card metric-primary">
                        <div class="metric-top">
                            <span class="metric-label">Total Tours</span>
                            <div class="metric-icon"><i class="bi bi-map"></i></div>
                        </div>
                        <div class="metric-value"><?= number_format($tourCount) ?></div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card metric-success">
                        <div class="metric-top">
                            <span class="metric-label">Destinations</span>
                            <div class="metric-icon"><i class="bi bi-geo-alt"></i></div>
                        </div>
                        <div class="metric-value"><?= number_format($destCount) ?></div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card metric-warning">
                        <div class="metric-top">
                            <span class="metric-label">Accommodations</span>
                            <div class="metric-icon"><i class="bi bi-house-door"></i></div>
                        </div>
                        <div class="metric-value"><?= number_format($accomCount) ?></div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card metric-danger">
                        <div class="metric-top">
                            <span class="metric-label">System Users</span>
                            <div class="metric-icon"><i class="bi bi-people"></i></div>
                        </div>
                        <div class="metric-value"><?= number_format($userCount) ?></div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="panel h-100">
                        <div class="panel-header">
                            <div>
                                <h2>Recent Tours</h2>
                                <p>Latest safari and tour packages created.</p>
                            </div>
                            <a href="tours.php" class="btn btn-outline-secondary btn-sm">View All</a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Tour Name</th>
                                        <th>Status</th>
                                        <th>Date Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($recentTours)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No tours found.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach($recentTours as $tour): ?>
                                        <tr>
                                            <td><strong><?= sanitize($tour['title']) ?></strong></td>
                                            <td>
                                                <?php if($tour['status'] === 'published'): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php elseif($tour['status'] === 'draft'): ?>
                                                    <span class="badge bg-warning text-dark">Draft</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Archived</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= timeSince($tour['created_at']) ?></td>
                                            <td class="text-end">
                                                <a href="edit-tour.php?id=<?= $tour['id'] ?>" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="panel h-100">
                        <div class="panel-header">
                            <div>
                                <h2>Quick Actions</h2>
                                <p>Common administrative tasks.</p>
                            </div>
                        </div>
                        <div class="activity-list mt-3">
                            <a href="create-tour.php" class="activity-item text-decoration-none">
                                <div class="activity-dot bg-gold-pale text-gold d-flex align-items-center justify-content-center p-3 rounded-circle"><i class="bi bi-plus-lg"></i></div>
                                <div>
                                    <strong class="text-dark d-block">Create New Tour</strong>
                                    <span class="text-muted small">Draft a new itinerary</span>
                                </div>
                            </a>
                            <a href="create-destination.php" class="activity-item text-decoration-none">
                                <div class="activity-dot bg-success-subtle text-success d-flex align-items-center justify-content-center p-3 rounded-circle"><i class="bi bi-geo-alt"></i></div>
                                <div>
                                    <strong class="text-dark d-block">Add Destination</strong>
                                    <span class="text-muted small">Add a new park or city</span>
                                </div>
                            </a>
                            <a href="taxonomies.php" class="activity-item text-decoration-none">
                                <div class="activity-dot bg-info-subtle text-info d-flex align-items-center justify-content-center p-3 rounded-circle"><i class="bi bi-tags"></i></div>
                                <div>
                                    <strong class="text-dark d-block">Manage Taxonomies</strong>
                                    <span class="text-muted small">Update activities &amp; features</span>
                                </div>
                            </a>
                            <a href="blogs.php" class="activity-item text-decoration-none">
                                <div class="activity-dot bg-primary-subtle text-primary d-flex align-items-center justify-content-center p-3 rounded-circle"><i class="bi bi-newspaper"></i></div>
                                <div>
                                    <strong class="text-dark d-block">Blog Manager</strong>
                                    <span class="text-muted small">Create &amp; manage blog posts</span>
                                </div>
                            </a>
                            <a href="recommendations.php" class="activity-item text-decoration-none">
                                <div class="activity-dot bg-warning-subtle text-warning d-flex align-items-center justify-content-center p-3 rounded-circle"><i class="bi bi-star-fill"></i></div>
                                <div>
                                    <strong class="text-dark d-block">Recommendations</strong>
                                    <span class="text-muted small">Feature tours on the homepage</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <?php include 'partials/footer.php'; ?>
</div>

<?php include 'partials/scripts.php'; ?>
</body>
</html>
