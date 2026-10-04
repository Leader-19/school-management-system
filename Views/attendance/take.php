<?php
/** @var array $class */
/** @var array $students */
/** @var string $sessionDate */
/** @var string $sessionType */
/** @var string $subjectId */
/** @var array $subjects */
/** @var array $existing */
/** @var array $marks */
/** @var array $statuses */
$pageTitle = 'Take Attendance';
$isCustom = !in_array($sessionType, AttendanceService::SESSION_TYPES, true);
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-0 text-gray-800">Attendance &middot; <?= htmlspecialchars((string) $class['name']) ?></h2>
        <p class="text-muted small mb-0 mt-1">
            <?= htmlspecialchars(date('l, M j, Y', strtotime($sessionDate))) ?>
            &middot; session: <?= htmlspecialchars($sessionType) ?>
            <?php if (!empty($marks)): ?>
                &middot; <span class="text-success fw-semibold"><?= count($marks) ?> student(s) already marked</span>
            <?php endif; ?>
        </p>
    </div>
    <a href="<?= BASE_URL ?>/attendance?class_id=<?= (int) $class['id'] ?>" class="btn btn-sm btn-light border">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3 bg-white">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-calendar-day me-2"></i>Session</h6>
    </div>
    <div class="card-body pb-0">
        <form action="<?= BASE_URL ?>/attendance/take/<?= (int) $class['id'] ?>" method="GET"
              class="row g-2 align-items-end mb-3" id="sessionPicker">
            <input type="hidden" name="submitted" value="0">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Date</label>
                <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($sessionDate) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Session type</label>
                <select class="form-select" name="type" onchange="this.form.submit()">
                    <?php foreach (AttendanceService::SESSION_TYPES as $type): ?>
                        <option value="<?= $type ?>" <?= $sessionType === $type ? 'selected' : '' ?>><?= ucfirst($type) ?></option>
                    <?php endforeach; ?>
                    <?php if ($isCustom): ?>
                        <option value="<?= htmlspecialchars($sessionType) ?>" selected><?= htmlspecialchars($sessionType) ?></option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Subject (optional)</label>
                <select class="form-select" name="subject_id" onchange="this.form.submit()">
                    <option value="">General</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= (int) $subject['id'] ?>" <?= $subjectId === (string) $subject['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) $subject['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Go</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3 bg-white">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-users me-2"></i>Mark Students</h6>
    </div>
    <div class="card-body p-0">
        <form action="<?= BASE_URL ?>/attendance/take/<?= (int) $class['id'] ?>" method="POST" id="attendanceForm">
            <?= ViewHelper::csrfField() ?>
            <input type="hidden" name="session_date" value="<?= htmlspecialchars($sessionDate) ?>">
            <input type="hidden" name="session_type" value="<?= htmlspecialchars($sessionType) ?>">
            <input type="hidden" name="subject_id" value="<?= htmlspecialchars($subjectId) ?>">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Student</th>
                            <th>Code</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $index => $student): ?>
                                <?php $sid = (int) $student['id']; ?>
                                <tr>
                                    <td class="ps-4"><?= $index + 1 ?></td>
                                    <td class="fw-medium">
                                        <a href="<?= BASE_URL ?>/attendance/student/<?= $sid ?>" class="text-decoration-none">
                                            <?= htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name'])) ?>
                                        </a>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars((string) $student['student_code']) ?></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Attendance status">
                                            <?php foreach ($statuses as $status): ?>
                                                <input type="radio" class="btn-check" name="attendance[<?= $sid ?>]"
                                                       id="att-<?= $sid ?>-<?= $status ?>" value="<?= $status ?>"
                                                       <?= ($marks[$sid] ?? 'present') === $status ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-<?= [
                                                    'present' => 'success', 'absent' => 'danger',
                                                    'late' => 'warning', 'excused' => 'info'
                                                ][$status] ?> for-att-<?= $status ?>"
                                                    for="att-<?= $sid ?>-<?= $status ?>">
                                                    <?= ucfirst($status) ?>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">
                                    <?= ViewHelper::emptyState('fa-user-slash', 'No students in this class', 'Assign students to the class first.') ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($students)): ?>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 border-top bg-light">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" id="markAllPresent">Mark all present</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="markNone">Clear all</button>
                    </div>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-save me-1"></i> Save Attendance
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('attendanceForm');
    if (!form) return;

    function setStatus(status) {
        form.querySelectorAll('input[type="radio"]').forEach(function (radio) {
            radio.checked = (radio.value === status);
        });
    }

    var allBtn = document.getElementById('markAllPresent');
    var noneBtn = document.getElementById('markNone');
    if (allBtn) allBtn.addEventListener('click', function () { setStatus('present'); });
    if (noneBtn) noneBtn.addEventListener('click', function () { setStatus('absent'); });
});
</script>
