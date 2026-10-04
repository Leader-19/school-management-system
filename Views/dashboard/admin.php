<div class="page-head d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="page-title mb-1">Welcome back, <?= htmlspecialchars($adminName ?? 'Administrator') ?></h2>
        <p class="page-subtitle mb-0">Here's an overview of your school today, <?= date('l, M j') ?>.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= BASE_URL ?>/student/create" class="btn btn-sm btn-primary"><i class="fa-solid fa-user-plus me-1"></i> Add Student</a>
        <a href="<?= BASE_URL ?>/class/create" class="btn btn-sm btn-info text-white"><i class="fa-solid fa-door-open me-1"></i> New Class</a>
        <a href="<?= BASE_URL ?>/assignment/create" class="btn btn-sm btn-success"><i class="fa-solid fa-plus me-1"></i> Create Assignment</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon"><i class="fa-solid fa-users"></i></span>
                <div class="min-width-0">
                    <div class="stat-label">Total Students</div>
                    <div class="stat-value"><?= $totalStudents ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon tone-success"><i class="fa-solid fa-chalkboard-user"></i></span>
                <div class="min-width-0">
                    <div class="stat-label">Total Teachers</div>
                    <div class="stat-value"><?= $totalTeachers ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon tone-info"><i class="fa-solid fa-door-open"></i></span>
                <div class="min-width-0">
                    <div class="stat-label">Total Classes</div>
                    <div class="stat-value"><?= $totalClasses ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon tone-warning"><i class="fa-solid fa-clipboard-list"></i></span>
                <div class="min-width-0">
                    <div class="stat-label">Assignments</div>
                    <div class="stat-value"><?= $totalAssignments ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-bell me-2"></i>Recent Activity</h6>
                <a href="<?= BASE_URL ?>/class" class="btn btn-sm btn-outline-secondary">Manage Classes</a>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="stat-icon tone-success" style="width: 2.6rem; height: 2.6rem; font-size: 1rem;">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </span>
                    <div>
                        <div class="fw-bold">System is running smoothly</div>
                        <div class="text-muted small">All services operational. Welcome back to the admin portal.</div>
                    </div>
                    <span class="badge bg-success rounded-pill px-3 py-2 ms-auto">Live</span>
                </div>

                <div class="row g-3">
                    <div class="col-sm-4">
                        <a href="<?= BASE_URL ?>/student" class="text-decoration-none">
                            <div class="d-flex align-items-center gap-2 p-3 rounded-3 border h-100 quick-link">
                                <i class="fa-solid fa-users text-primary"></i>
                                <span class="fw-semibold text-body">View Students</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-4">
                        <a href="<?= BASE_URL ?>/assignment" class="text-decoration-none">
                            <div class="d-flex align-items-center gap-2 p-3 rounded-3 border h-100 quick-link">
                                <i class="fa-solid fa-book text-info"></i>
                                <span class="fw-semibold text-body">View Assignments</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-4">
                        <a href="<?= BASE_URL ?>/grade" class="text-decoration-none">
                            <div class="d-flex align-items-center gap-2 p-3 rounded-3 border h-100 quick-link">
                                <i class="fa-solid fa-graduation-cap text-success"></i>
                                <span class="fw-semibold text-body">View Grades</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-rocket me-2"></i>Getting Started</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    <a href="<?= BASE_URL ?>/student/create" class="d-flex align-items-center gap-3 text-decoration-none getting-started-item">
                        <span class="stat-icon tone-info" style="width: 2.4rem; height: 2.4rem; font-size: 0.9rem;"><i class="fa-solid fa-user-plus"></i></span>
                        <span>
                            <span class="fw-semibold text-body d-block">Add students</span>
                            <span class="text-muted small">Create student accounts one by one.</span>
                        </span>
                    </a>
                    <a href="<?= BASE_URL ?>/class/create" class="d-flex align-items-center gap-3 text-decoration-none getting-started-item">
                        <span class="stat-icon tone-warning" style="width: 2.4rem; height: 2.4rem; font-size: 0.9rem;"><i class="fa-solid fa-door-open"></i></span>
                        <span>
                            <span class="fw-semibold text-body d-block">Set up classes</span>
                            <span class="text-muted small">Group students and assign homeroom teachers.</span>
                        </span>
                    </a>
                    <a href="<?= BASE_URL ?>/assignment/create" class="d-flex align-items-center gap-3 text-decoration-none getting-started-item">
                        <span class="stat-icon tone-success" style="width: 2.4rem; height: 2.4rem; font-size: 0.9rem;"><i class="fa-solid fa-clipboard-list"></i></span>
                        <span>
                            <span class="fw-semibold text-body d-block">Post an assignment</span>
                            <span class="text-muted small">Give work to a class with a due date.</span>
                        </span>
                    </a>
                    <a href="<?= BASE_URL ?>/grade" class="d-flex align-items-center gap-3 text-decoration-none getting-started-item">
                        <span class="stat-icon" style="width: 2.4rem; height: 2.4rem; font-size: 0.9rem;"><i class="fa-solid fa-award"></i></span>
                        <span>
                            <span class="fw-semibold text-body d-block">Record grades</span>
                            <span class="text-muted small">Enter results so students can track progress.</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
