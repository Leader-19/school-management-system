<?php
/** @var array $teachers */
/** @var array $pagination */
/** @var string $keyword */
$pageTitle = 'Teachers';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-0 text-gray-800">Manage Teachers</h2>
        <p class="text-muted small mb-0 mt-1">
            <?= (int) ($pagination['total'] ?? 0) ?> teacher<?= (int) ($pagination['total'] ?? 0) === 1 ? '' : 's' ?>
            <?= !empty($keyword) ? 'matching your search' : 'on staff' ?>
        </p>
    </div>
    <a href="<?= BASE_URL ?>/teacher/create" class="btn btn-sm btn-primary shadow-sm">
        <i class="fa-solid fa-user-plus me-1"></i> Add Teacher
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-chalkboard-user me-2"></i>Teacher List</h6>
        <form action="<?= BASE_URL ?>/teacher" method="GET" class="d-flex gap-2">
            <div class="input-group input-group-sm" style="max-width: 240px;">
                <span class="input-group-text bg-light"><i class="fa-solid fa-search"></i></span>
                <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($keyword ?? '') ?>"
                       placeholder="Search username or email..." aria-label="Search teachers">
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Search</button>
            <?php if (!empty($keyword)): ?>
                <a href="<?= BASE_URL ?>/teacher" class="btn btn-sm btn-light border">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dataTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th class="text-center">Classes</th>
                        <th>Status</th>
                        <th class="pe-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($teachers)): ?>
                        <?php foreach ($teachers as $index => $teacher): ?>
                            <tr class="searchable-row">
                                <td class="ps-4"><?= (int) ($pagination['offset'] ?? 0) + $index + 1 ?></td>
                                <td class="fw-medium"><?= htmlspecialchars((string) $teacher['username']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars((string) $teacher['email']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-secondary rounded-pill"><?= (int) ($teacher['class_count'] ?? 0) ?></span>
                                </td>
                                <td>
                                    <?php if (($teacher['status'] ?? '') === 'active'): ?>
                                        <span class="badge bg-success rounded-pill px-2">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger rounded-pill px-2">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="btn-group" role="group">
                                        <a href="<?= BASE_URL ?>/teacher/edit/<?= (int) $teacher['id'] ?>"
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        <form action="<?= BASE_URL ?>/teacher/delete/<?= (int) $teacher['id'] ?>" method="POST"
                                              class="d-inline confirm-delete m-0 p-0"
                                              data-confirm="Delete this teacher account? They will lose access immediately. Classes they lead keep existing but lose their homeroom teacher.">
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
                            <td colspan="6"><?= ViewHelper::emptyState('fa-user-slash', 'No matching teachers', 'Try a different search term.') ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <?= ViewHelper::emptyState(
                                    'fa-chalkboard-user',
                                    'No teachers yet',
                                    'Create the first teacher account to get started.',
                                    BASE_URL . '/teacher/create',
                                    'Add Teacher'
                                ) ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        $paginationBaseUrl = BASE_URL . '/teacher';
        $paginationQuery   = !empty($keyword) ? ['q' => $keyword] : [];
        $paginationNoun    = 'teachers';
        require BASE_PATH . '/Views/partials/pagination.php';
        ?>
    </div>
</div>
