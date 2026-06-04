<?php
$user = currentUser();
?>
<nav class="admin-navbar px-3 px-lg-4 d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <div class="d-none d-md-block">
            <span class="eyebrow d-block mb-0">Filao Adventures</span>
            <span class="text-muted small">CMS Platform v1.0</span>
        </div>
    </div>

    <div class="navbar-actions">
        <button class="icon-button" id="themeToggle" title="Toggle Dark Mode">
            <i class="bi bi-moon-stars-fill"></i>
        </button>

        <a href="../" target="_blank" class="icon-button" title="View Site">
            <i class="bi bi-box-arrow-up-right"></i>
        </a>

        <div class="dropdown">
            <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name'] ?? 'U') ?>&background=C49018&color=fff" alt="User" class="avatar-sm">
                <span class="d-none d-md-inline ms-1"><?= sanitize(explode(' ', $user['name'] ?? 'User')[0]) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                <li><h6 class="dropdown-header">Signed in as <?= sanitize($user['email'] ?? '') ?></h6></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2 text-muted"></i> Sign Out</a></li>
            </ul>
        </div>
    </div>
</nav>
