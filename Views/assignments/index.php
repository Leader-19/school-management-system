<?php
/** @var array $assignments */
$canManage = Auth::hasPermission('create_assignments');
$pageTitle = 'Assignments';

$overdueCount = 0;
foreach ($assignments as $a) {
    if (strtotime($a['due_date']) < time()) {
        $overdueCount++;
    }
}
$totalRows = isset($pagination) ? (int) $pagination['total'] : count($assignments);
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-0 text-gray-800">Assignments</h2>
        <p class="text-muted small mb-0 mt-1">
            <?= $totalRows ?> assignment<?= $totalRows === 1 ? '' : 's' ?>
            <?= $overdueCount > 0 ? '&middot; <span class="text-danger fw-semibold">' . $overdueCount . ' overdue</span>' : '' ?>
        </p>
    </div>
    <?php if ($canManage): ?>
        <a href="<?= BASE_URL ?>/assignment/create" class="btn btn-sm btn-primary shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Create Assignment
        </a>
    <?php endif; ?>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-book me-2"></i>Assignment List</h6>
        <form action="<?= BASE_URL ?>/assignment" method="GET" class="d-flex gap-2">
            <div class="input-group input-group-sm" style="max-width: 240px;">
                <span class="input-group-text bg-light"><i class="fa-solid fa-search"></i></span>
                <input type="text" class="form-control" id="tableSearch" name="q"
                       value="<?= htmlspecialchars($keyword ?? '') ?>" data-target="#dataTable"
                       placeholder="Search title, subject or class..." aria-label="Search assignments">
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Search</button>
            <?php if (!empty($keyword)): ?>
                <a href="<?= BASE_URL ?>/assignment" class="btn btn-sm btn-light border">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dataTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Teacher</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="pe-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($assignments)): ?>
                        <?php foreach ($assignments as $index => $assignment): ?>
                            <?php
                            $assignmentId = (int) $assignment['id'];
                            $state = ViewHelper::dueDateState($assignment['due_date']);
                            $canEditThis = $canManage
                                && (Auth::hasRole('admin') || (int) $assignment['teacher_id'] === (int) Auth::userId());
                            ?>
                            <tr class="searchable-row">
                                <td class="ps-4"><?= (int) ($pagination['offset'] ?? 0) + $index + 1 ?></td>
                                <td class="fw-medium">
                                    <a href="<?= BASE_URL ?>/assignment/show/<?= $assignmentId ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($assignment['title']) ?>
                                    </a>
                                    <?php if (!empty($assignment['attachments'])): ?>
                                        <i class="fa-solid fa-paperclip text-muted ms-1" title="Has an attached document"></i>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($assignment['subject_name'] ?? 'N/A') ?></span></td>
                                <td><?= htmlspecialchars($assignment['class_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($assignment['teacher_name'] ?? 'N/A') ?></td>
                                <td>
                                    <div class="small"><?= htmlspecialchars(date('M d, Y H:i', strtotime($assignment['due_date']))) ?></div>
                                    <span class="badge bg-<?= htmlspecialchars($state['class']) ?>">
                                        <i class="<?= htmlspecialchars($state['icon']) ?> me-1"></i><?= htmlspecialchars($state['label']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($state['class'] === 'danger'): ?>
                                        <span class="badge bg-danger rounded-pill px-2">Overdue</span>
                                    <?php else: ?>
                                        <span class="badge bg-success rounded-pill px-2">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="btn-group" role="group">
                                        <a href="<?= BASE_URL ?>/assignment/show/<?= $assignmentId ?>"
                                           class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <?php if (Auth::hasRole('student')): ?>
                                            <a href="<?= BASE_URL ?>/assignment/submit/<?= $assignmentId ?>"
                                               class="btn btn-sm btn-outline-success" title="Submit work">
                                                <i class="fa-solid fa-upload"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($canEditThis): ?>
                                            <a href="<?= BASE_URL ?>/assignment/edit/<?= $assignmentId ?>"
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fa-solid fa-edit"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($canEditThis): ?>
                                            <form action="<?= BASE_URL ?>/assignment/delete/<?= $assignmentId ?>" method="POST"
                                                  class="d-inline confirm-delete m-0 p-0"
                                                  data-confirm="Delete this assignment? Student submissions will be removed too.">
                                                <?= ViewHelper::csrfField() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <tr class="no-match" style="display:none">
                            <td colspan="8">
                                <?= ViewHelper::emptyState('fa-file-circle-xmark', 'No matching assignments', 'Try a different search term.') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <?php if ($canManage): ?>
                                    <?= ViewHelper::emptyState(
                                        'fa-file-circle-plus',
                                        'No assignments yet',
                                        'Create your first assignment and attach a briefing document.',
                                        BASE_URL . '/assignment/create',
                                        'Create Assignment'
                                    ) ?>
                                <?php else: ?>
                                    <?= ViewHelper::emptyState('fa-mug-hot', 'Nothing assigned yet', 'Your teacher has not published any assignments for your class.') ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        $paginationBaseUrl = BASE_URL . '/assignment';
        $paginationQuery   = !empty($keyword) ? ['q' => $keyword] : [];
        $paginationNoun    = 'assignments';
        require BASE_PATH . '/Views/partials/pagination.php';
        ?>
    </div>
</div>