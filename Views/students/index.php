<?php
/** @var array $students */
/** @var string $keyword */
/** @var array $pagination */
$pageTitle = 'Students';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-0 text-gray-800">Manage Students</h2>
        <p class="text-muted small mb-0 mt-1">
            <?= (int) ($pagination['total'] ?? 0) ?> student<?= (int) ($pagination['total'] ?? 0) === 1 ? '' : 's' ?>
            <?= !empty($keyword) ? 'matching your search' : 'enrolled' ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/student/import" class="btn btn-sm btn-outline-primary">
            <i class="fa-solid fa-file-excel me-1"></i> Import Excel
        </a>
        <a href="<?= BASE_URL ?>/student/create" class="btn btn-sm btn-primary shadow-sm">
            <i class="fa-solid fa-user-plus me-1"></i> Add New Student
        </a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3 bg-white">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-users me-2"></i>Student List</h6>
    </div>
<div class="card-body border-bottom bg-white">
        <form action="<?= BASE_URL ?>/student" method="GET" class="row g-2 align-items-end">
            <div class="col-md-9 col-sm-8">
                <label for="tableSearch" class="form-label small fw-semibold mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-search"></i></span>
                    <input type="text" class="form-control" id="tableSearch" name="q"
                           value="<?= htmlspecialchars($keyword ?? '') ?>"
                           data-target="#dataTable"
                           placeholder="Search name, code or email..."
                           aria-label="Search students">
                </div>
                <div class="form-text">Filters as you type. Press Search to include matches from every page.</div>
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                </button>
                <?php if (!empty($keyword)): ?>
                    <a href="<?= BASE_URL ?>/student" class="btn btn-sm btn-light border">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dataTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Student Code</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Class</th>
                        <th>Status</th>
                        <th class="pe-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $index => $student): ?>
                            <tr class="searchable-row">
                                <td class="ps-4"><?= (int) ($pagination['offset'] ?? 0) + $index + 1 ?></td>
                                <td class="fw-bold text-muted"><?= htmlspecialchars((string) $student['student_code']) ?></td>
                                <td class="fw-medium">
                                    <a href="<?= BASE_URL ?>/student/show/<?= (int) $student['id'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name'])) ?>
                                    </a>
                                </td>
                                <td class="text-muted"><?= htmlspecialchars((string) ($student['username'] ?? '—')) ?></td>
                                <td>
                                    <?php if (!empty($student['email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($student['email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($student['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars((string) ($student['class_name'] ?? 'Unassigned')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($student['user_status'] ?? 'active') === 'active'): ?>
                                        <span class="badge bg-success rounded-pill px-2">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger rounded-pill px-2">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="btn-group" role="group">
                                        <a href="<?= BASE_URL ?>/student/show/<?= (int) $student['id'] ?>"
                                           class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/student/edit/<?= (int) $student['id'] ?>"
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        <form action="<?= BASE_URL ?>/student/delete/<?= (int) $student['id'] ?>" method="POST" class="d-inline confirm-delete m-0 p-0">
                                            <?= ViewHelper::csrfField() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
<?php endforeach; ?>
                        <tr class="no-match" style="display:none">
                            <td colspan="8"><?= ViewHelper::emptyState('fa-user-slash', 'No matching students', 'Try a different name, code or email.') ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <?= ViewHelper::emptyState(
                                    'fa-user-plus',
                                    'No students yet',
                                    'Add your first student to get started.',
                                    BASE_URL . '/student/create',
                                    'Add New Student'
                                ) ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        $paginationBaseUrl = BASE_URL . '/student';
        $paginationQuery   = !empty($keyword) ? ['q' => $keyword] : [];
        $paginationNoun    = 'students';
        require BASE_PATH . '/Views/partials/pagination.php';
        ?>
    </div>
</div>