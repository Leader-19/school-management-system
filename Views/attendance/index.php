<?php
/** @var array $classes */
/** @var int $classId */
/** @var array $sessions */
/** @var array|null $pagination */
$pageTitle = 'Attendance';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-0 text-gray-800">Attendance</h2>
        <p class="text-muted small mb-0 mt-1">Record who attended each class session.</p>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="<?= BASE_URL ?>/attendance" method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label for="class_id" class="form-label small fw-semibold mb-1">Class</label>
                <select class="form-select" id="class_id" name="class_id" required>
                    <option value="">Select a class...</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= (int) $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) $class['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Open Class
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($classId > 0): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-clipboard-check me-2"></i>Session History</h6>
        <a href="<?= BASE_URL ?>/attendance/take/<?= $classId ?>" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Take Attendance (Today)
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Session</th>
                        <th>Subject</th>
                        <th class="text-center">Marked</th>
                        <th>Recorded By</th>
                        <th class="pe-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sessions)): ?>
                        <?php foreach ($sessions as $index => $session): ?>
                            <tr>
                                <td class="ps-4 fw-medium"><?= htmlspecialchars(date('M d, Y', strtotime($session['session_date']))) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary-emphasis border"><?= htmlspecialchars((string) $session['session_type']) ?></span></td>
                                <td class="text-muted"><?= htmlspecialchars((string) ($session['subject_id'] ? '' : 'General')) ?></td>
                                <td class="text-center"><span class="badge bg-secondary rounded-pill"><?= (int) $session['marked_count'] ?></span></td>
                                <td class="text-muted small"><?= htmlspecialchars((string) ($session['created_by_name'] ?? '—')) ?></td>
                                <td class="pe-4 text-center">
                                    <a href="<?= BASE_URL ?>/attendance/take/<?= $classId ?>?date=<?= urlencode($session['session_date']) ?>&type=<?= urlencode($session['session_type']) ?>"
                                       class="btn btn-sm btn-outline-primary" title="View / edit session">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <?= ViewHelper::emptyState(
                                    'fa-clipboard-check',
                                    'No sessions recorded yet',
                                    'Take attendance for the first time to start the history.',
                                    BASE_URL . '/attendance/take/' . $classId,
                                    'Take Attendance'
                                ) ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination !== null): ?>
            <?php
            $paginationBaseUrl = BASE_URL . '/attendance';
            $paginationQuery   = ['class_id' => $classId];
            $paginationNoun    = 'sessions';
            require BASE_PATH . '/Views/partials/pagination.php';
            ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
