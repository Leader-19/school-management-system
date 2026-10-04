$pageTitle = 'Class Details';
<?php
/** @var array $class */
/** @var array $students */
/** @var array $subjects */
/** @var bool $canWrite */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800"><?= htmlspecialchars((string) $class['name']) ?></h2>
    <div>
        <?php if ($canWrite): ?>
            <a href="<?= BASE_URL ?>/class/edit/<?= (int) $class['id'] ?>" class="btn btn-sm btn-primary shadow-sm me-2">
                <i class="fa-solid fa-edit me-1"></i> Edit
            </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/class" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card stat-card border-left-primary h-100 shadow-sm py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Students</div>
                <div class="h4 mb-0 fw-bold text-gray-800"><?= count($students) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card stat-card border-left-info h-100 shadow-sm py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Subjects</div>
                <div class="h4 mb-0 fw-bold text-gray-800"><?= count($subjects) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card stat-card border-left-success h-100 shadow-sm py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Homeroom Teacher</div>
                <div class="h6 mb-0 fw-bold text-gray-800">
                    <?= htmlspecialchars((string) ($class['teacher_name'] ?: 'Unassigned')) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($class['description'])): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">Description</div>
            <div class="text-wrap" style="white-space: pre-line;"><?= htmlspecialchars($class['description']) ?></div>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3 bg-white">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-users me-2"></i>Enrolled Students</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Code</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th class="pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted"><?= htmlspecialchars((string) $student['student_code']) ?></td>
                                <td><?= htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name'])) ?></td>
                                <td class="text-muted"><?= htmlspecialchars((string) ($student['email'] ?: '—')) ?></td>
                                <td class="pe-4">
                                    <?php if (($student['user_status'] ?? 'active') === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">No students in this class yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3 bg-white">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-book me-2"></i>Subjects</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Code</th>
                        <th>Subject</th>
                        <th>Credits</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($subjects)): ?>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted"><?= htmlspecialchars((string) $subject['code']) ?></td>
                                <td><?= htmlspecialchars((string) $subject['name']) ?></td>
                                <td><?= (int) $subject['credit_hours'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center py-4 text-muted">No subjects mapped to this class.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>