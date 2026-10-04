<div class="page-head mb-4">
    <h2 class="page-title mb-1">Welcome back, <?= htmlspecialchars($studentName ?? 'Student') ?></h2>
    <p class="page-subtitle mb-0">Here's your school day at a glance, <?= date('l, M j') ?>.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-4 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon"><i class="fa-solid fa-users-rectangle"></i></span>
                <div class="min-width-0">
                    <div class="stat-label">My Class</div>
                    <div class="stat-value fs-4"><?= htmlspecialchars($className ?? 'Unassigned') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon tone-warning"><i class="fa-solid fa-clock"></i></span>
                <div class="min-width-0">
                    <div class="stat-label">Pending Assignments</div>
                    <div class="stat-value"><?= $pendingAssignments ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-12">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon tone-success"><i class="fa-solid fa-star"></i></span>
                <div class="min-width-0">
                    <div class="stat-label">Average Grade</div>
                    <div class="stat-value"><?= number_format($averageGrade ?? 0, 2) ?>%</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-list-check me-2"></i>Upcoming Assignments</h6>
                <a href="<?= BASE_URL ?>/assignment" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Title</th>
                                <th>Subject</th>
                                <th>Due Date</th>
                                <th class="pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($upcomingAssignments)): ?>
                                <?php foreach($upcomingAssignments as $assignment): ?>
                                    <tr>
                                        <td class="ps-3 fw-medium"><?= htmlspecialchars($assignment['title']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($assignment['subject_name'] ?? 'N/A') ?></span></td>
                                        <td>
                                            <?php 
                                                $dueDate = strtotime($assignment['due_date']);
                                                $isOverdue = $dueDate < time();
                                            ?>
                                            <span class="<?= $isOverdue ? 'text-danger fw-bold' : 'text-body' ?>">
                                                <i class="fa-regular fa-calendar me-1"></i> <?= htmlspecialchars(date('M d, Y', $dueDate)) ?>
                                            </span>
                                        </td>
                                        <td class="pe-3">
                                            <a href="<?= BASE_URL ?>/assignment/show/<?= $assignment['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">No upcoming assignments</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-success"><i class="fa-solid fa-graduation-cap me-2"></i>Recent Grades</h6>
                <a href="<?= BASE_URL ?>/grade" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Subject</th>
                                <th>Score</th>
                                <th class="pe-3">Semester</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentGrades)): ?>
                                <?php foreach($recentGrades as $grade): ?>
                                    <tr>
                                        <td class="ps-3 fw-medium"><?= htmlspecialchars($grade['subject_name'] ?? 'N/A') ?></td>
                                        <td><?= ViewHelper::scoreBadge($grade['score']) ?></td>
                                        <td class="pe-3 text-muted"><?= htmlspecialchars($grade['semester']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">No recent grades</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
