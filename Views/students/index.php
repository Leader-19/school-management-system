<?php
/** @var array $students */
/** @var string $keyword */
/** @var array $pagination */
$pageTitle = 'Students';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h3 mb-1 text-gray-800">Students</h2>
        <p class="text-muted small mb-0 mt-1">
            <?= !empty($keyword) ? 'Showing matches for “' . htmlspecialchars($keyword) . '”' : 'Manage student records, accounts, and class assignments.' ?>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= BASE_URL ?>/student/import" class="btn btn-outline-primary">
            <i class="fa-solid fa-file-import me-1"></i> Import students
        </a>
        <a href="<?= BASE_URL ?>/student/create" class="btn btn-primary shadow-sm">
            <i class="fa-solid fa-user-plus me-1"></i> Add student
        </a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header py-3 px-4 bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-users me-2"></i>Student directory</h6>
            <span class="small text-muted"><?= (int) ($pagination['total'] ?? 0) ?> student<?= (int) ($pagination['total'] ?? 0) === 1 ? '' : 's' ?><?= !empty($keyword) ? ' found' : ' enrolled' ?></span>
        </div>
    </div>
<div class="card-body border-bottom bg-light-subtle px-4">
        <form action="<?= BASE_URL ?>/student" method="GET" class="row g-2 align-items-end">
            <div class="col-lg">
                <label for="tableSearch" class="form-label small fw-semibold mb-1">Find a student</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control" id="tableSearch" name="q"
                           value="<?= htmlspecialchars($keyword ?? '') ?>"
                           data-target="#dataTable"
                           placeholder="Name, student code, username or email"
                           aria-label="Search students">
                </div>
                <div class="form-text">Type to filter this page, or press Search to find students across all pages.</div>
            </div>
            <div class="col-sm-auto">
                <label for="studentPageSize" class="form-label small fw-semibold mb-1">Rows per page</label>
                <select class="form-select form-select-sm" id="studentPageSize" name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 15, 25, 50, 100] as $size): ?>
                        <option value="<?= $size ?>" <?= (int) ($pagination['per_page'] ?? 15) === $size ? 'selected' : '' ?>><?= $size ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-auto d-flex gap-2">
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
                                           class="btn btn-sm btn-outline-secondary" title="View student" aria-label="View <?= htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name'])) ?>">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/student/edit/<?= (int) $student['id'] ?>"
                                           class="btn btn-sm btn-outline-primary" title="Edit student" aria-label="Edit <?= htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name'])) ?>">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        <form action="<?= BASE_URL ?>/student/delete/<?= (int) $student['id'] ?>" method="POST" class="d-inline confirm-delete m-0 p-0">
                                            <?= ViewHelper::csrfField() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete student" aria-label="Delete <?= htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name'])) ?>">
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
        $paginationQuery   = [];
        if (!empty($keyword)) { $paginationQuery['q'] = $keyword; }
        if ((int) ($pagination['per_page'] ?? 15) !== 15) { $paginationQuery['per_page'] = (int) $pagination['per_page']; }
        $paginationNoun    = 'students';
        require BASE_PATH . '/Views/partials/pagination.php';
        ?>
    </div>
</div>
