<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$user = currentUser();
?>
<!-- Sidebar Backdrop for Mobile -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="index.php" class="brand-mark">
            <div class="brand-icon">F</div>
            <div class="brand-copy">
                <span class="brand-title">Filao Adventures</span>
                <span class="brand-subtitle">Admin Panel</span>
            </div>
        </a>
    </div>

    <nav class="sidebar-nav">
        <a href="index.php" class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-grid-1x2-fill"></i></span>
            <span class="nav-text">Dashboard</span>
        </a>

        <a href="enquiries.php" class="nav-link <?= $currentPage === 'enquiries.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-envelope-fill"></i></span>
            <span class="nav-text">Inquiries</span>
        </a>

        <a href="partner_agents.php" class="nav-link <?= $currentPage === 'partner_agents.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-briefcase-fill"></i></span>
            <span class="nav-text">Agents</span>
        </a>

        <a href="newsletters.php" class="nav-link <?= $currentPage === 'newsletters.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-envelope-paper-fill"></i></span>
            <span class="nav-text">Newsletters</span>
        </a>

        <div class="nav-group-label">Catalog</div>
        
        <a href="tours.php" class="nav-link <?= in_array($currentPage, ['tours.php', 'create-tour.php', 'edit-tour.php']) ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-map-fill"></i></span>
            <span class="nav-text">Tours & Safaris</span>
        </a>

        <a href="destinations.php" class="nav-link <?= in_array($currentPage, ['destinations.php', 'create-destination.php', 'edit-destination.php']) ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-geo-alt-fill"></i></span>
            <span class="nav-text">Destinations</span>
        </a>

        <a href="regions.php" class="nav-link <?= in_array($currentPage, ['regions.php', 'create-region.php', 'edit-region.php']) ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-globe"></i></span>
            <span class="nav-text">Regions</span>
        </a>

        <a href="countries.php" class="nav-link <?= in_array($currentPage, ['countries.php', 'create-country.php', 'edit-country.php']) ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-flag-fill"></i></span>
            <span class="nav-text">Countries</span>
        </a>

        <a href="activities.php" class="nav-link <?= in_array($currentPage, ['activities.php', 'create-activity.php', 'edit-activity.php']) ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-bicycle"></i></span>
            <span class="nav-text">Activities</span>
        </a>

        <a href="accommodations.php" class="nav-link <?= in_array($currentPage, ['accommodations.php', 'create-accommodation.php', 'edit-accommodation.php']) ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-house-door-fill"></i></span>
            <span class="nav-text">Accommodations</span>
        </a>
        
        <a href="taxonomies.php" class="nav-link <?= $currentPage === 'taxonomies.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-tags-fill"></i></span>
            <span class="nav-text">Taxonomies</span>
        </a>

        <div class="nav-group-label">Content</div>

        <a href="blogs.php" class="nav-link <?= in_array($currentPage, ['blogs.php', 'create-blog.php', 'edit-blog.php']) ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-newspaper"></i></span>
            <span class="nav-text">Blog Manager</span>
        </a>

        <a href="recommendations.php" class="nav-link <?= $currentPage === 'recommendations.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-star-fill"></i></span>
            <span class="nav-text">Recommendations</span>
        </a>

        <?php if ($user && $user['role'] === 'admin'): ?>
        <div class="nav-group-label">System</div>
        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'users.php') !== false ? 'active' : '' ?>" href="users.php">
          <span class="nav-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
          <span class="nav-text">Users</span>
        </a>
        <a href="settings.php" class="nav-link <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-gear-fill"></i></span>
            <span class="nav-text">Settings</span>
        </a>
        <a href="head-tags.php" class="nav-link <?= $currentPage === 'head-tags.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-code-square"></i></span>
            <span class="nav-text">Head Tags</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-user">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name'] ?? 'U') ?>&background=C49018&color=fff" alt="User" class="avatar-sm sidebar-user-avatar">
        <div>
            <strong><?= sanitize($user['name'] ?? 'User') ?></strong><br>
            <small><?= sanitize(ucfirst($user['role'] ?? 'Editor')) ?></small>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="status-dot"></div>
        <span class="sidebar-footer-text">System Online</span>
    </div>
</aside>
