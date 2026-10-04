<?php
$canManageGrades = Auth::hasPermission('manage_grades');
$pageTitle = 'Grades';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-0 text-gray-800">Grades Record</h2>
        <p class="text-muted small mb-0 mt-1">
            <?= isset($pagination) ? (int) $pagination['total'] : count($grades) ?> result<?= (isset($pagination) ? (int) $pagination['total'] : count($grades)) === 1 ? '' : 's' ?> on record
        </p>
    </div>
    <?php if ($canManageGrades): ?>
        <a href="<?= BASE_URL ?>/grade/create" class="btn btn-sm btn-primary shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Add Manual Grade
        </a>
    <?php endif; ?>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-award me-2"></i>Grades Database</h6>
        <?php if (Auth::hasPermission('manage_grades')): ?>
            <form action="<?= BASE_URL ?>/grade" method="GET" class="d-flex gap-2">
                <div class="input-group input-group-sm" style="max-width: 240px;">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-search"></i></span>
                    <input type="text" class="form-control" id="tableSearch" name="q"
                           value="<?= htmlspecialchars($keyword ?? '') ?>" data-target="#dataTable"
                           placeholder="Search student or subject..." aria-label="Search grades">
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Search</button>
                <?php if (!empty($keyword)): ?>
                    <a href="<?= BASE_URL ?>/grade" class="btn btn-sm btn-light border">Clear</a>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <div class="input-group input-group-sm" style="max-width: 260px;">
                <span class="input-group-text bg-light"><i class="fa-solid fa-search"></i></span>
                <input type="text" class="form-control" id="tableSearch" data-target="#dataTable"
                       placeholder="Search grades..." aria-label="Search grades">
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dataTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <?php if (!Auth::hasRole('student')): ?>
                            <th>Student Name</th>
                        <?php endif; ?>
                        <th>Subject</th>
                        <th>Score</th>
                        <th>Semester</th>
                        <th>Year</th>
                        <?php if (Auth::hasPermission('manage_grades')): ?>
                            <th class="pe-4 text-center">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($grades)): ?>
                        <?php 
                            $totalScore = 0;
                            $count = count($grades);
                        ?>
                        <?php foreach($grades as $index => $grade): ?>
                            <?php $totalScore += $grade['score']; ?>
                            <tr class="searchable-row">
                                <td class="ps-4 text-muted"><?= (int) ($pagination['offset'] ?? 0) + $index + 1 ?></td>
                                <?php if (!Auth::hasRole('student')): ?>
                                    <td class="fw-medium"><?= htmlspecialchars($grade['student_name'] ?? 'Unknown') ?></td>
                                <?php endif; ?>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($grade['subject_name'] ?? 'Unknown') ?></span></td>
<td>
                                    <?= ViewHelper::scoreBadge($grade['score']) ?>
                                </td>
                                <td><?= htmlspecialchars($grade['semester']) ?></td>
                                <td><?= htmlspecialchars($grade['year']) ?></td>
                                
                                <?php if (Auth::hasPermission('manage_grades')): ?>
                                    <td class="pe-4 text-center">
                                        <form action="<?= BASE_URL ?>/grade/delete/<?= (int) $grade['id'] ?>" method="POST"
                                              class="d-inline confirm-delete m-0 p-0"
                                              data-confirm="Remove this grade record?">
                                            <?= ViewHelper::csrfField() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>

                        <tr class="no-match" style="display:none">
                            <td colspan="<?= Auth::hasRole('student') ? '5' : (Auth::hasPermission('manage_grades') ? '7' : '6') ?>">
                                <?= ViewHelper::emptyState('fa-graduation-cap', 'No matching grades', 'Try a different search term.') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= Auth::hasRole('student') ? '5' : (Auth::hasPermission('manage_grades') ? '7' : '6') ?>">
                                <?= Auth::hasPermission('manage_grades')
                                    ? ViewHelper::emptyState('fa-award', 'No grades recorded yet', 'Use "Add Manual Grade" to enter the first result.', null, '')
                                    : ViewHelper::emptyState('fa-award', 'No grades yet', 'Your grades will appear here once a teacher records them.') ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pagination)): ?>
            <?php
            $paginationBaseUrl = BASE_URL . '/grade';
            $paginationQuery   = !empty($keyword) ? ['q' => $keyword] : [];
            $paginationNoun    = 'results';
            require BASE_PATH . '/Views/partials/pagination.php';
            ?>
        <?php endif; ?>
    </div>
    
    <?php if (Auth::hasRole('student') && !empty($grades)): ?>
        <div class="card-footer bg-light py-3 d-flex justify-content-end align-items-center">
            <span class="fw-bold me-3 text-uppercase text-muted">Overall Average:</span>
            <span class="badge bg-primary fs-5 px-3 py-2 rounded-pill shadow-sm"><?= number_format($totalScore / $count, 2) ?>%</span>
        </div>
    <?php endif; ?>
</div>
