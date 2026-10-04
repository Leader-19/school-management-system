<?php
/** @var array $classes */
/** @var bool $canWrite */
$pageTitle = 'Classes';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-0 text-gray-800">Manage Classes</h2>
        <p class="text-muted small mb-0 mt-1">
            <?= (int) ($pagination['total'] ?? 0) ?> class<?= (int) ($pagination['total'] ?? 0) === 1 ? '' : 'es' ?> in this school year
        </p>
    </div>
    <?php if ($canWrite): ?>
        <a href="<?= BASE_URL ?>/class/create" class="btn btn-sm btn-primary shadow-sm">
            <i class="fa-solid fa-door-open me-1"></i> New Class
        </a>
    <?php endif; ?>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-door-open me-2"></i>Class List</h6>
        <div class="input-group input-group-sm" style="max-width: 260px;">
            <span class="input-group-text bg-light"><i class="fa-solid fa-search"></i></span>
            <input type="text" class="form-control" id="tableSearch" data-target="#dataTable"
                   placeholder="Search classes..." aria-label="Search classes">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dataTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Class Name</th>
                        <th>Description</th>
                        <th>Homeroom Teacher</th>
                        <th class="text-center">Students</th>
                        <?php if ($canWrite): ?>
                            <th class="pe-4 text-center">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($classes)): ?>
                        <?php foreach ($classes as $index => $class): ?>
                            <?php $classId = (int) $class['id']; ?>
                            <tr class="searchable-row">
                                <td class="ps-4"><?= (int) ($pagination['offset'] ?? 0) + $index + 1 ?></td>
                                <td class="fw-semibold">
                                    <a href="<?= BASE_URL ?>/class/show/<?= $classId ?>" class="text-decoration-none">
                                        <?= htmlspecialchars((string) $class['name']) ?>
                                    </a>
                                </td>
                                <td class="text-muted small">
                                    <?= htmlspecialchars((string) ($class['description'] ?: '—')) ?>
                                </td>
                                <td>
                                    <?php if (!empty($class['teacher_name'])): ?>
                                        <span class="badge bg-info-subtle text-info-emphasis">
                                            <i class="fa-solid fa-chalkboard-user me-1"></i>
                                            <?= htmlspecialchars($class['teacher_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/class/show/<?= $classId ?>" class="badge bg-secondary rounded-pill text-decoration-none">
                                        <?= (int) ($class['student_count'] ?? 0) ?>
                                    </a>
                                </td>
                                <?php if ($canWrite): ?>
                                    <td class="pe-4 text-center">
                                        <div class="btn-group" role="group">
                                            <a href="<?= BASE_URL ?>/class/show/<?= $classId ?>"
                                               class="btn btn-sm btn-outline-secondary" title="View">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/class/edit/<?= $classId ?>"
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fa-solid fa-edit"></i>
                                            </a>
                                            <form action="<?= BASE_URL ?>/class/delete/<?= $classId ?>" method="POST"
                                                  class="d-inline confirm-delete m-0 p-0"
                                                  data-confirm="Delete this class? Its students will be unassigned but not deleted.">
                                                <?= ViewHelper::csrfField() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>

                        <tr class="no-match" style="display:none">
                            <td colspan="<?= $canWrite ? 6 : 5 ?>">
                                <?= ViewHelper::emptyState('fa-door-closed', 'No matching classes', 'Try a different search term.') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $canWrite ? 6 : 5 ?>">
                                <?php if ($canWrite): ?>
                                    <?= ViewHelper::emptyState(
                                        'fa-door-open',
                                        'No classes yet',
                                        'Create a class first, then assign students and subjects to it.',
                                        BASE_URL . '/class/create',
                                        'Create Class'
                                    ) ?>
                                <?php else: ?>
                                    <?= ViewHelper::emptyState('fa-door-closed', 'No classes yet', 'An administrator has not created any classes.') ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        $paginationBaseUrl = BASE_URL . '/class';
        $paginationQuery   = !empty($keyword) ? ['q' => $keyword] : [];
        $paginationNoun    = 'classes';
        require BASE_PATH . '/Views/partials/pagination.php';
        ?>
    </div>
</div>