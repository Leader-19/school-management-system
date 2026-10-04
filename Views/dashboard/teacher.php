<div class="page-head d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="page-title mb-1">Welcome back, <?= htmlspecialchars($teacherName ?? 'Teacher') ?></h2>
        <p class="page-subtitle mb-0">Here's your teaching overview for today, <?= date('l, M j') ?>.</p>
    </div>
    <a href="<?= BASE_URL ?>/assignment/create" class="btn btn-sm btn-primary"><i class="fa-solid fa-plus me-1"></i> Create Assignment</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-4 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon"><i class="fa-solid fa-chalkboard"></i></span>
                <div class="min-width-0">
                    <div class="stat-label">My Classes</div>
                    <div class="stat-value"><?= $myClassesCount ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon tone-success"><i class="fa-solid fa-book-open"></i></span>
                <div class="min-width-0">
                    <div class="stat-label">My Assignments</div>
                    <div class="stat-value"><?= $myAssignmentsCount ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-12">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon tone-warning"><i class="fa-solid fa-file-signature"></i></span>
                <div class="min-width-0">
                    <div class="stat-label">Pending Submissions</div>
                    <div class="stat-value"><?= $pendingSubmissions ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-list me-2"></i>Recent Assignments</h6>
        <a href="<?= BASE_URL ?>/assignment" class="btn btn-sm btn-outline-secondary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Title</th>
                        <th>Class</th>
                        <th>Due Date</th>
                        <th>Submissions</th>
                        <th class="pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentAssignments)): ?>
                        <?php foreach($recentAssignments as $assignment): ?>
                            <tr>
                                <td class="ps-4 fw-medium"><?= htmlspecialchars($assignment['title']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($assignment['class_name'] ?? 'N/A') ?></span></td>
                                <td>
                                    <?php 
                                        $dueDate = strtotime($assignment['due_date']);
                                        $isOverdue = $dueDate < time();
                                    ?>
                                    <span class="<?= $isOverdue ? 'text-danger fw-bold' : 'text-body' ?>">
                                        <i class="fa-regular fa-calendar me-1"></i> <?= htmlspecialchars(date('M d, Y H:i', $dueDate)) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info rounded-pill px-3"><?= $assignment['submissions_count'] ?? 0 ?></span>
                                </td>
                                <td class="pe-4">
                                    <a href="<?= BASE_URL ?>/assignment/show/<?= $assignment['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye me-1"></i> View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No assignments found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
