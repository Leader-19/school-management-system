<?php
/** @var array $student */
/** @var array $history */
/** @var array $summary */
/** @var array $statuses */
$pageTitle = 'Attendance History';
$total = array_sum($summary);
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-0 text-gray-800">
            Attendance &middot; <?= htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name'])) ?>
        </h2>
        <p class="text-muted small mb-0 mt-1">Code: <?= htmlspecialchars((string) $student['student_code']) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/student/show/<?= (int) $student['id'] ?>" class="btn btn-sm btn-light border">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Student
    </a>
</div>

<div class="row g-3 mb-4">
    <?php foreach (['present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'excused' => 'info'] as $key => $tone): ?>
        <div class="col-6 col-md-3">
            <div class="border rounded p-3 text-center h-100">
                <div class="h4 mb-0 text-<?= $tone ?>"><?= (int) ($summary[$key] ?? 0) ?></div>
                <div class="text-muted small text-capitalize"><?= $key ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Sessions</h6>
        <?php if ($total > 0): ?>
            <span class="text-muted small">
                <?= (int) $summary['present'] ?>/<?= $total ?> present
                (<?= round(100 * $summary['present'] / max(1, $total), 1) ?>%)
            </span>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Session</th>
                        <th>Class</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($history)): ?>
                        <?php foreach ($history as $row): ?>
                            <tr>
                                <td class="ps-4"><?= htmlspecialchars(date('M d, Y', strtotime($row['session_date']))) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary-emphasis border"><?= htmlspecialchars((string) $row['session_type']) ?></span></td>
                                <td class="text-muted"><?= htmlspecialchars((string) $row['class_name']) ?></td>
                                <td>
                                    <?php
                                    $tones = ['present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'excused' => 'info'];
                                    ?>
                                    <span class="badge bg-<?= $tones[$row['status']] ?? 'secondary' ?> rounded-pill px-2 text-capitalize">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">
                                <?= ViewHelper::emptyState('fa-clipboard-check', 'No attendance recorded yet', 'Sessions this student is marked in will appear here.') ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
