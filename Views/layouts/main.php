<?php
/**
 * Main layout used by every authenticated page.
 *
 * @var string $viewContent Rendered page body.
 * @var string $pageTitle   Optional document title.
 */

$currentUser = Auth::user();
$pageTitle = $pageTitle ?? '';

/**
 * Marks the sidebar entry matching the current request path.
 * Server-side so the highlight is correct on first paint.
 */
$smsNavActive = function ($path) {
    $current = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/');
    $base = rtrim(BASE_URL, '/');
    if ($base !== '' && strpos($current, $base) === 0) {
        $current = trim(substr($current, strlen($base)), '/');
    }
    $target = trim($path, '/');

    return $target !== '' && ($current === $target || strpos($current, $target . '/') === 0);
};

/**
 * Sidebar entry helper - keeps the nav markup terse.
 */
$smsNavItem = function ($href, $icon, $label, $path) use ($smsNavActive) {
    $active = $smsNavActive($path);
    echo '<li class="mb-1">'
        . '<a href="' . htmlspecialchars($href) . '" class="sidebar-link' . ($active ? ' active' : '') . '"'
        . ($active ? ' aria-current="page"' : '') . '>'
        . '<i class="fa-solid ' . htmlspecialchars($icon) . ' me-2"></i> ' . htmlspecialchars($label)
        . '</a></li>';
};

// Initials for the avatar circles, derived from the username.
$smsInitials = (static function (string $name): string {
    $parts = preg_split('/[\s._-]+/', trim($name)) ?: [];
    $parts = array_filter($parts);
    if ($parts === []) {
        return 'U';
    }
    if (count($parts) === 1) {
        return mb_strtoupper(mb_substr($parts[0], 0, 1));
    }
    return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(array_slice($parts, -1)[0], 0, 1));
})((string) ($currentUser['username'] ?? 'User'));

$userRole = (string) ($currentUser['role_name'] ?? '');
$roleTone = ['admin' => 'danger', 'teacher' => 'primary', 'student' => 'success'][$userRole] ?? 'primary';
$roleLabel = $userRole !== '' ? ucfirst($userRole) : 'User';
$successMessage = Session::getFlash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#171d3a">
    <title><?= $pageTitle ? htmlspecialchars($pageTitle) . ' | ' : '' ?>School Management System</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎓</text></svg>">
    <!-- Inter font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <?php if (Auth::check()): ?>
        <a class="skip-link" href="#mainContent">Skip to main content</a>

        <!-- Off-canvas backdrop for small screens -->
        <div class="sidebar-backdrop d-lg-none" data-sidebar-close></div>

        <!-- Sidebar -->
        <nav id="sidebar" aria-label="Main navigation">
            <div class="sidebar-header">
                <a href="<?= BASE_URL ?>/dashboard" class="sidebar-brand">
                    <span class="brand-tile"><i class="fa-solid fa-graduation-cap"></i></span>
                    <span>
                        <h4>School MS</h4>
                        <small>Management Portal</small>
                    </span>
                </a>
            </div>

            <ul class="list-unstyled components">
                <li class="sidebar-label">Overview</li>
                <?php $smsNavItem(BASE_URL . '/dashboard', 'fa-gauge', 'Dashboard', 'dashboard'); ?>

                <?php if (Auth::hasPermission('manage_students')
                    || Auth::hasPermission('manage_classes')
                    || Auth::hasPermission('view_assignments')
                    || Auth::hasPermission('manage_grades')
                    || Auth::hasPermission('view_own_grades')): ?>
                    <li class="sidebar-label">Management</li>
                <?php endif; ?>

                <?php if (Auth::hasPermission('manage_students')): ?>
                    <?php $smsNavItem(BASE_URL . '/student', 'fa-users', 'Students', 'student'); ?>
                    <?php $smsNavItem(BASE_URL . '/attendance', 'fa-clipboard-check', 'Attendance', 'attendance'); ?>
                <?php endif; ?>

                <?php if (Auth::hasPermission('manage_users')): ?>
                    <?php $smsNavItem(BASE_URL . '/teacher', 'fa-chalkboard-user', 'Teachers', 'teacher'); ?>
                <?php endif; ?>

                <?php if (Auth::hasPermission('manage_classes') || Auth::hasPermission('manage_students')): ?>
                    <?php $smsNavItem(BASE_URL . '/class', 'fa-door-open', 'Classes', 'class'); ?>
                <?php endif; ?>

                <?php if (Auth::hasPermission('view_assignments')): ?>
                    <?php $smsNavItem(BASE_URL . '/assignment', 'fa-book', 'Assignments', 'assignment'); ?>
                <?php endif; ?>

                <?php if (Auth::hasPermission('manage_grades') || Auth::hasPermission('view_own_grades')): ?>
                    <?php $smsNavItem(BASE_URL . '/grade', 'fa-graduation-cap', 'Grades', 'grade'); ?>
                <?php endif; ?>
            </ul>

            <div class="sidebar-footer">
                <span class="avatar avatar-tone-<?= htmlspecialchars($roleTone) ?>"
                    aria-hidden="true"><?= htmlspecialchars($smsInitials) ?></span>
                <div class="min-width-0">
                    <div class="sidebar-user-name"><?= htmlspecialchars((string) ($currentUser['username'] ?? 'User')) ?></div>
                    <div class="sidebar-user-role"><?= htmlspecialchars($roleLabel) ?></div>
                </div>
                <a href="<?= BASE_URL ?>/logout" class="sidebar-logout ms-auto"
                   title="Log out" aria-label="Log out">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <div id="content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg topbar mb-4 px-3 py-2 shadow-sm bg-white">
                <div class="container-fluid px-0">
                    <!-- Sidebar Collapse Toggle -->
                    <button type="button" id="sidebarCollapse" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center"
                            style="width: 40px; height: 40px;" aria-label="Toggle sidebar" aria-expanded="true">
                        <i class="fa-solid fa-bars text-secondary"></i>
                    </button>

                    <!-- Improved Global Search Bar -->
                    <form action="<?= BASE_URL ?>/search" method="GET" class="d-none d-md-flex align-items-center ms-3 position-relative" style="max-width: 360px; width: 100%;">
                        <input type="text" name="q" class="form-control rounded-pill ps-4 pe-5 bg-light border-0 shadow-none text-secondary" placeholder="What do you want to find?" style="font-size: 0.875rem; height: 42px;" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                        <button type="submit" class="btn position-absolute end-0 border-0 bg-transparent pe-3 shadow-none d-flex align-items-center justify-content-center" aria-label="Search" style="height: 100%;">
                            <i class="fa-solid fa-magnifying-glass" style="color: #8b5cf6;"></i>
                        </button>
                    </form>

                    <!-- Right Navigation Elements -->
                    <div class="ms-auto d-flex align-items-center gap-2 gap-sm-3">
                        <!-- Notification Bell Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-light rounded-circle position-relative d-flex align-items-center justify-content-center border-0 bg-light" style="width: 40px; height: 40px;" type="button" id="notificationMenuButton" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                                <i class="fa-regular fa-bell text-secondary fs-6"></i>
                                <!-- Notification badge indicator -->
                                <span class="position-absolute top-25 start-75 translate-middle p-1 bg-danger border border-light rounded-circle">
                                    <span class="visually-hidden">New notifications</span>
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2 p-2 rounded-4" aria-labelledby="notificationMenuButton" style="width: 300px;">
                                <li class="px-3 py-2 fw-bold text-dark border-bottom d-flex justify-content-between align-items-center">
                                    <span>Notifications</span>
                                    <span class="badge bg-primary rounded-pill fs-7">New</span>
                                </li>
                                <li class="py-3 text-center text-muted small">No new notifications right now.</li>
                            </ul>
                        </div>

                        <!-- Message/Chat Icon Link -->
                        <a href="<?= BASE_URL ?>/messages" class="btn btn-light rounded-circle position-relative d-flex align-items-center justify-content-center border-0 bg-light text-secondary" style="width: 40px; height: 40px;" aria-label="Messages">
                            <i class="fa-regular fa-comment-dots fs-6"></i>
                        </a>

                        <!-- User Profile Dropdown Menu -->
                        <div class="dropdown">
                            <button class="btn btn-light d-flex align-items-center gap-2 rounded-pill px-2 px-sm-3 py-1 bg-light border-0"
                                    type="button" id="profileMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="avatar avatar-sm avatar-tone-<?= htmlspecialchars($roleTone) ?>" aria-hidden="true"><?= htmlspecialchars($smsInitials) ?></span>
                                <span class="text-start d-none d-sm-block">
                                    <span class="user-name d-block lh-1 fw-semibold text-dark" style="font-size: 0.875rem;"><?= htmlspecialchars((string) ($currentUser['username'] ?? 'User')) ?></span>
                                    <span class="small text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($roleLabel) ?></span>
                                </span>
                                <i class="fa-solid fa-chevron-down small text-muted ms-1" aria-hidden="true"></i>
                                <span class="visually-hidden">Open profile menu</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-2 mt-2 rounded-4" aria-labelledby="profileMenuButton" style="min-width: 250px">
                                <div class="px-3 py-2 border-bottom mb-2">
                                    <div class="fw-semibold text-truncate text-dark"><?= htmlspecialchars((string) ($currentUser['username'] ?? 'User')) ?></div>
                                    <div class="small text-muted text-truncate"><?= htmlspecialchars((string) ($currentUser['email'] ?? '')) ?></div>
                                </div>
                                <a class="dropdown-item rounded-2 py-2 d-flex align-items-center text-secondary" href="<?= BASE_URL ?>/profile">
                                    <i class="fa-regular fa-user me-2 text-muted"></i> My profile
                                </a>
                                <div class="dropdown-divider my-1"></div>
                                <a class="dropdown-item rounded-2 py-2 d-flex align-items-center text-danger" href="<?= BASE_URL ?>/logout">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Log out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="container-fluid px-4 pb-4 flex-grow-1 app-main" id="mainContent">
                <?php // Keep errors inline so they remain visible beside the page content. ?>
                <?php if ($error = Session::getFlash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show flash-message" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?= $viewContent ?>
            </main>
        </div>
    <?php else: ?>
        <!-- Minimal layout for non-logged in users -->
        <div class="container-fluid p-0 d-flex flex-column min-vh-100">
            <main class="flex-grow-1 d-flex align-items-center justify-content-center">
                <?php if ($error = Session::getFlash('error')): ?>
                    <div class="position-fixed top-0 start-50 translate-middle-x p-3 w-100 d-flex justify-content-center" style="z-index: 1080; max-width: 480px;">
                        <div class="alert alert-danger alert-dismissible fade show flash-message shadow-sm w-100" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                <?php endif; ?>

                <?= $viewContent ?>
            </main>
        </div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
            <div class="toast align-items-center text-bg-success border-0 show flash-toast" role="status" aria-live="polite" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body"><i class="fa-solid fa-circle-check me-2" aria-hidden="true"></i><?= htmlspecialchars($successMessage) ?></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Dismiss message"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/public/js/app.js"></script>
</body>
</html>